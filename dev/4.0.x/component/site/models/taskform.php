<?php
/**
 * @package      Projectfork
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


// Base this model on the backend version.
require_once JPATH_ADMINISTRATOR . '/components/com_projectfork/models/task.php';


/**
 * Projectfork Component Task Form Model
 *
 */
class ProjectforkModelTaskForm extends ProjectforkModelTask
{
    /**
     * Method to get item data.
     *
     * @param     integer    $id    The id of the item.
     * @return    mixed             Item data object on success, false on failure.
     */
    public function getItem($id = null)
    {
        // Initialise variables.
        $id = (int) (!empty($id)) ? $id : $this->getState('task.id');

        // Get a row instance.
        $table = $this->getTable();

        // Attempt to load the row.
        $return = $table->load($id);

        // Check for a table object error.
        if ($return === false && $table->getError()) {
            $this->setError($table->getError());
            return false;
        }

        $properties = $table->getProperties(1);
        $value = JArrayHelper::toObject($properties, 'JObject');

        // Convert attrib field to Registry.
        $value->params = new JRegistry;
        $value->params->loadString($value->attribs);

        // Get assigned users
        $value->users = $this->getUsers($id);

        // Compute selected asset permissions.
        $uid    = JFactory::getUser()->get('id');
        $access = ProjectforkHelperAccess::getActions('task', $value->id);

        // Convert seconds back to minutes
        if ($value->estimate > 0) {
            $value->estimate = round($value->estimate / 60);
        }

        // Check general edit permission first.
        if ($access->get('task.edit')) {
            $value->params->set('access-edit', true);
        }
        // Now check if edit.own is available.
        elseif (!empty($uid) && $access->authorise('task.edit.own')) {
            // Check for a valid user and that they are the owner.
            if ($uid == $value->created_by) {
                $value->params->set('access-edit', true);
            }
        }

        // Check edit state permission.
        if ($id) {
            // Existing item
            $value->params->set('access-change', $access->get('task.edit.state'));
        }
        else {
            // New item
            $access = ProjectforkHelperAccess::getActions();
            $value->params->set('access-change', $access->get('task.edit.state'));
        }

        return $value;
    }


    /**
     * Method to save the priority of one or more tasks
     *
     * @param     array    $ids     An array of primary key ids.
     * @param     array    $pids    An array of priority values.
     * @return    mixed             True on success, otherwise false
     */
    public function savePriority($pks = null, $priority = null)
    {
        // Initialise variables.
        $table = $this->getTable();
        $conditions = array();

        if (empty($pks)) {
            return JError::raiseWarning(500, JText::_($this->text_prefix . '_ERROR_NO_ITEMS_SELECTED'));
        }

        // update priority values
        foreach ($pks as $i => $pk)
        {
            $table->load((int) $pk);

            // Access checks.
            if (!$this->canEditState($table))
            {
                // Prune items that you can't change.
                unset($pks[$i]);
                JError::raiseWarning(403, JText::_('JLIB_APPLICATION_ERROR_EDITSTATE_NOT_PERMITTED'));
            }
            elseif ($table->priority != $priority[$pk])
            {
                $table->priority = $priority[$pk];

                if (!$table->store()) {
                    $this->setError($table->getError());
                    return false;
                }
            }
        }

        // Clear the component's cache
        $this->cleanCache();

        return true;
    }


    /**
     * Method to assign a user to one or more tasks
     *
     * @param     array    $ids     An array of primary key ids.
     * @param     array    $uids    An array of user id values.
     * @return    mixed             True on success, otherwise false
     */
    public function addUsers($pks = null, $uids = null)
    {
        // Initialise variables.
        $table = $this->getTable();
        $conditions = array();

        if (empty($pks)) {
            return JError::raiseWarning(500, JText::_($this->text_prefix . '_ERROR_NO_ITEMS_SELECTED'));
        }

        // update priority values
        foreach ($pks as $i => $pk)
        {
            $table->load((int) $pk);

            // Access checks.
            if (!$this->canEditState($table))
            {
                // Prune items that you can't change.
                unset($pks[$i]);
                JError::raiseWarning(403, JText::_('JLIB_APPLICATION_ERROR_EDIT_NOT_PERMITTED'));
            }

            $refs = JModel::getInstance('UserRefs', 'ProjectforkModel', array('ignore_request' => true));

            if (!$refs->store($uids, 'task', $pk)) {
                return false;
            }
        }

        // Clear the component's cache
        $this->cleanCache();

        return true;
    }


    /**
     * Method to set the completion state of tasks
     *
     * @param     array    $ids         An array of primary key ids.
     * @param     array    $complete    An array of state values.
     * @return    mixed                 True on success, otherwise false
     */
    public function setComplete($pks = null, $complete = null)
    {
        // Initialise variables.
        $table = $this->getTable();
        $conditions = array();

        if (empty($pks)) {
            return JError::raiseWarning(500, JText::_($this->text_prefix . '_ERROR_NO_ITEMS_SELECTED'));
        }

        // update values
        foreach ($pks as $i => $pk)
        {
            $table->load((int) $pk);

            // Access checks.
            if (!$this->canEditState($table))
            {
                // Prune items that you can't change.
                unset($pks[$i]);
                JError::raiseWarning(403, JText::_('JLIB_APPLICATION_ERROR_EDIT_NOT_PERMITTED'));
            }
            elseif ($table->complete != $complete[$pk]) {
                $table->complete = (int) $complete[$pk];

                if (!$table->store()) {
                    $this->setError($table->getError());
                    return false;
                }
            }
        }

        // Clear the component's cache
        $this->cleanCache();

        return true;
    }


    /**
     * Get the return URL.
     *
     * @return    string    The return URL.
     */
    public function getReturnPage()
    {
        return base64_encode($this->getState('return_page'));
    }


    /**
     * Method to get the data that should be injected in the form.
     *
     * @return    mixed    The data for the form.
     */
    protected function loadFormData()
    {
        // Check the session for previously entered form data.
        $data = JFactory::getApplication()->getUserState('com_projectfork.edit.taskform.data', array());

        if (empty($data)) $data = $this->getItem();

        return $data;
    }


    /**
     * Method to auto-populate the model state.
     * Note. Calling getState in this method will result in recursion.
     *
     * @return    void
     */
    protected function populateState()
    {
        // Load state from the request.
        $pk = JRequest::getInt('id');
        $this->setState('task.id', $pk);

        $return = JRequest::getVar('return', null, 'default', 'base64');
        $this->setState('return_page', base64_decode($return));

        // Load the parameters.
        $params = JFactory::getApplication()->getParams();
        $this->setState('params', $params);

        $this->setState('layout', JRequest::getCmd('layout'));
    }
}
