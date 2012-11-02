<?php
/**
 * @package      Projectfork
 * @subpackage   Tasks
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


// Base this model on the backend version.
JLoader::register('PFtasksModelTask', JPATH_ADMINISTRATOR . '/components/com_pftasks/models/task.php');


/**
 * Projectfork Component Task Form Model
 *
 */
class PFtasksModelTaskForm extends PFtasksModelTask
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
        $id = (int) (!empty($id)) ? $id : $this->getState($this->getName() . '.id');

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
        $access = PFtasksHelper::getActions($value->id);

        // Convert seconds back to minutes
        if ($value->estimate > 0) {
            $value->estimate = round($value->estimate / 60);
        }

        if (PFApplicationHelper::exists('com_pfrepo')) {
            // Get the attachments
            $attachments = $this->getInstance('Attachments', 'PFrepoModel');
            $value->attachment = $attachments->getItems('com_pftasks.task', $value->id);
        }

        // Get the labels
        $labels = $this->getInstance('Labels', 'PFModel');
        $value->labels = $labels->getConnections('com_pftasks.task', $value->id);

        // Get the Dependencies
        $taskrefs = $this->getInstance('TaskRefs', 'PFtasksModel');
        $value->dependency = $taskrefs->getItems($value->id, true);

        // Check general edit permission first.
        if ($access->get('core.edit')) {
            $value->params->set('access-edit', true);
        }
        // Now check if edit.own is available.
        elseif (!empty($uid) && $access->authorise('core.edit.own')) {
            // Check for a valid user and that they are the owner.
            if ($uid == $value->created_by) {
                $value->params->set('access-edit', true);
            }
        }

        // Check edit state permission.
        if ($id) {
            // Existing item
            $value->params->set('access-change', $access->get('core.edit.state'));
        }
        else {
            // New item
            $access = PFtasksHelper::getActions();
            $value->params->set('access-change', $access->get('core.edit.state'));
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

        // Include the content plugins for the on save events.
        JPluginHelper::importPlugin('content');
        $dispatcher = JDispatcher::getInstance();

        // update priority values
        foreach ($pks as $i => $pk)
        {
            $table->load((int) $pk);

            // Access checks.
            if (!$this->canEditState($table)) {
                // Prune items that you can't change.
                unset($pks[$i]);
                JError::raiseWarning(403, JText::_('JLIB_APPLICATION_ERROR_EDITSTATE_NOT_PERMITTED'));
            }
            elseif ($table->priority != $priority[$pk]) {
                $table->priority = $priority[$pk];

                // Trigger the onContentBeforeSave event.
                $result = $dispatcher->trigger($this->event_before_save, array($this->option . '.' . $this->name, &$table, false));

                if (!$table->store()) {
                    $this->setError($table->getError());
                    return false;
                }

                // Trigger the onContentBeforeSave event.
                $result = $dispatcher->trigger($this->event_after_save, array($this->option . '.' . $this->name, &$table, false));
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
            if (!$this->canEditState($table)) {
                // Prune items that you can't change.
                unset($pks[$i]);
                JError::raiseWarning(403, JText::_('JLIB_APPLICATION_ERROR_EDIT_NOT_PERMITTED'));
            }

            $refs = $this->getInstance('UserRefs', 'PFusersModel', array('ignore_request' => true));

            if (!$refs->store($uids, 'com_pftasks.task', $pk)) {
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
     * @param     array    $pks         An array of primary key ids.
     *
     * @return    mixed                 True on success, otherwise false
     */
    public function complete($pks = null, $state = null)
    {
        // Initialise variables.
        $table = $this->getTable();

        if (empty($pks)) {
            JError::raiseWarning(500, JText::_($this->text_prefix . '_ERROR_NO_ITEMS_SELECTED'));
            return false;
        }

        // Include the content plugins for the on save events.
        JPluginHelper::importPlugin('content');
        $dispatcher = JDispatcher::getInstance();

        // Update values
        foreach ($pks as $i => $pk)
        {
            $table->load((int) $pk);

            // Access checks.
            if (!$this->canEditState($table)) {
                // Prune items that you can't change.
                unset($pks[$i]);
                JError::raiseWarning(403, JText::_('JLIB_APPLICATION_ERROR_EDIT_NOT_PERMITTED'));
            }

            if (is_null($state)) {
                if ($table->complete == '1') {
                    $table->complete = '0';
                }
                else {
                    $table->complete = '1';
                }
            }
            else {
                $table->complete = (int) $state;
            }

            // Trigger the onContentBeforeSave event.
            $result = $dispatcher->trigger($this->event_before_save, array($this->option . '.' . $this->name, &$table, false));

            if (!$table->store()) {
                $this->setError($table->getError());
                return false;
            }

            // Trigger the onContentBeforeSave event.
            $result = $dispatcher->trigger($this->event_after_save, array($this->option . '.' . $this->name, &$table, false));
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
     * Method to auto-populate the model state.
     * Note. Calling getState in this method will result in recursion.
     *
     * @return    void
     */
    protected function populateState()
    {
        // Load state from the request.
        $pk = JRequest::getInt('id');
        $this->setState($this->getName() . '.id', $pk);

        $return = JRequest::getVar('return', null, 'default', 'base64');
        $this->setState('return_page', base64_decode($return));

        // Load the parameters.
        $params = JFactory::getApplication()->getParams();
        $this->setState('params', $params);

        $this->setState('layout', JRequest::getCmd('layout'));
    }
}
