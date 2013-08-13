<?php
/**
 * @package      pkg_projectfork
 * @subpackage   com_pftasks
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2013 Tobias Kuhn. All rights reserved.
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
     * @param     integer    $pk      The id of the item.
     * @return    mixed      $item    Item data object on success, false on failure.
     */
    public function getItem($pk = null)
    {
        // Get the record from the parent class method
        $item = parent::getItem($pk);

        if ($item === false) return false;

        // Compute selected asset permissions.
        $user   = JFactory::getUser();
        $uid    = $user->get('id');
        $access = PFtasksHelper::getActions($item->id);

        $view_access = true;

        if ($item->access && !$user->authorise('core.admin')) {
            $view_access = in_array($item->access, $user->getAuthorisedViewLevels());
        }

        $item->params->set('access-view', $view_access);

        if (!$view_access) {
            $item->params->set('access-edit', false);
            $item->params->set('access-change', false);
        }
        else {
            // Check general edit permission first.
            if ($access->get('core.edit')) {
                $item->params->set('access-edit', true);
            }
            elseif (!empty($uid) &&  $access->get('core.edit.own')) {
                // Check for a valid user and that they are the owner.
                if ($uid == $item->created_by) {
                    $item->params->set('access-edit', true);
                }
            }

            // Check edit state permission.
            $item->params->set('access-change', $access->get('core.edit.state'));
        }

        return $item;
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
     * @param     array    $pks    An array of primary key ids.
     *
     * @return    mixed            True on success, otherwise false
     */
    public function complete($pks = null, $state = null)
    {
        // Initialise variables.
        $table = $this->getTable();
        $uid   = JFactory::getUser()->get('id');
        $date  = new JDate();
        $now   = $date->toSql();
        $ndate = JFactory::getDbo()->getNullDate();

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
                    $table->completed = $ndate;
                    $table->completed_by = '0';
                }
                else {
                    $table->complete = '1';
                    $table->completed = $now;
                    $table->completed_by = $uid;
                }
            }
            else {
                $table->complete = (int) $state;

                if ($table->complete == 1) {
                    $table->complete = '0';
                    $table->completed = $ndate;
                    $table->completed_by = '0';
                }
                else {
                    $table->complete = 0;
                    $table->completed = $now;
                    $table->completed_by = $uid;
                }
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
