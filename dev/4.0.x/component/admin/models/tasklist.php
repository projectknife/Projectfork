<?php
/**
 * @package      Projectfork
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


jimport('joomla.application.component.modeladmin');


/**
 * Item Model for a milestone form.
 *
 */
class ProjectforkModelTasklist extends JModelAdmin
{
    /**
     * The prefix to use with controller messages.
     *
     * @var    string
     */
    protected $text_prefix = 'COM_PROJECTFORK_TASKLIST';


    /**
     * Returns a Table object, always creating it.
     *
     * @param     string    The table type to instantiate
     * @param     string    A prefix for the table class name. Optional.
     * @param     array     Configuration array for model. Optional.
     *
     * @return    jtable    A database object
     */
    public function getTable($type = 'Tasklist', $prefix = 'PFTable', $config = array())
    {
        return JTable::getInstance($type, $prefix, $config);
    }


    /**
     * Method to get a single record.
     *
     * @param     integer    The id of the primary key.
     *
     * @return    mixed      Object on success, false on failure.
     */
    public function getItem($pk = null)
    {
        if ($item = parent::getItem($pk)) {
            // Convert the params field to an array.
            $registry = new JRegistry;
            $registry->loadString($item->attribs);

            $item->attribs = $registry->toArray();
        }

        return $item;
    }


    /**
     * Method to get the record form.
     *
     * @param     array      Data for the form.
     * @param     boolean    True if the form is to load its own data (default case), false if not.
     *
     * @return    mixed      A JForm object on success, false on failure
     */
    public function getForm($data = array(), $loadData = true)
    {
        // Get the form.
        $form = $this->loadForm('com_projectfork.tasklist', 'tasklist', array('control' => 'jform', 'load_data' => $loadData));
        if (empty($form)) return false;

        $is_new    = ((int) $this->getState($this->getName() . '.id') > 0) ? false : true;
        $project   = (int) $form->getValue('project_id');
        $milestone = (int) $form->getValue('milestone_id');

        if ($is_new) {
            // Override project if not set
            if ($project == 0) {
                $app       = JFactory::getApplication();
                $active_id = (int) $app->getUserState('com_projectfork.project.active.id', 0);

                $form->setValue('project_id', null, $active_id);
            }

            // Override milestone selection if set
            if ($milestone == 0) {
                $form->setValue('milestone_id', null, JRequest::getUInt('milestone_id'));
            }
        }

        return $form;
    }


    /**
     * Method to get the data that should be injected in the form.
     *
     * @return    mixed    The data for the form.
     */
    protected function loadFormData()
    {
        // Check the session for previously entered form data.
        $data = JFactory::getApplication()->getUserState('com_projectfork.edit.' . $this->getName() . '.data', array());

        if (empty($data)) $data = $this->getItem();

        return $data;
    }


    /**
     * Method to delete one or more records.
     *
     * @param     array      An array of record primary keys.
     *
     * @return    boolean    True if successful, false if an error occurs.
     */
    public function delete(&$pks)
    {
        // Delete the records
        $success = parent::delete($pks);

        // Cancel if something went wrong
        if (!$success) return false;

        $tasks = JTable::getInstance('Task', 'PFTable');

        // Delete all other items referenced to each project
        if (!$tasks->deleteByReference($pks, 'list_id')) $success = false;

        return $success;
    }


    /**
     * Method to save the form data.
     *
     * @param     array      The form data
     *
     * @return    boolean    True on success
     */
    public function save($data)
    {
        // Alter the title for save as copy
        if (JRequest::getVar('task') == 'save2copy') {
            list($title, $alias) = $this->generateNewTitle($data['alias'], $data['title'], $data['project_id'], $data['milestone_id']);
            $data['title'] = $title;
            $data['alias'] = $alias;
        }
        else {
            // Always re-generate the alias unless save2copy
            $data['alias'] = '';
        }

        $id      = (int) $this->getState($this->getName() . '.id');
        $is_new  = ($id > 0) ? false : true;
        $item    = null;

        if (!$is_new) {
            // Load the existing record before updating it
            $item = $this->getTable();
            $item->load($id);
        }

        // Store the record
        if (parent::save($data)) {
            // To keep data integrity, update all child assets
            if (!$is_new && is_object($item)) {
                $updated = $this->getTable();
                $tasks   = JTable::getInstance('Task', 'PFTable');

                $parent_data = array();

                // Load the just updated row
                if ($updated->load($this->getState($this->getName() . '.id')) === false) return false;

                // Check if any relevant values have changed that need to be updated to children
                if ($item->access != $updated->access) {
                    $parent_data['access'] = $updated->access;
                }

                if ($item->state != $updated->state) {
                    $parent_data['state'] = $updated->state;
                }

                if (count($parent_data)) {
                    $tasks->updateByReference($id, 'list_id', $parent_data);
                }
            }

            return true;
        }

        return false;
    }


    /**
     * Method to change the published state of one or more records.
     *
     * @param     array      A list of the primary keys to change.
     * @param     integer    The value of the published state.
     *
     * @return    boolean    True on success.
     */
    public function publish(&$pks, $value = 1)
    {
        $result = parent::publish($pks, $value);

        if ($result) {
            // State change succeeded. Now update all children
            $tasks = JTable::getInstance('Task', 'PFTable');

            $parent_data = array();
            $parent_data['state'] = $value;

            $tasks->updateByReference($pks, 'milestone_id', $parent_data);
        }

        return $result;
    }


    /**
     * Custom clean the cache of com_projectfork and projectfork modules
     *
     */
    protected function cleanCache()
    {
        parent::cleanCache('com_projectfork');
    }


    /**
     * Method to change the title & alias.
     * Overloaded from JModelAdmin class
     *
     * @param     string     The alias
     * @param     string     The title
     * @param     integer    The project id
     * @param     integer    The milestone id
     *
     * @return    array      Contains the modified title and alias
     */
    protected function generateNewTitle($alias, $title, $project_id, $milestone_id)
    {
        // Alter the title & alias
        $table = $this->getTable();
        $data  = array('alias' => $alias, 'project_id' => $project_id, 'milestone_id' => $milestone_id);

        while ($table->load($data))
        {
            $m = null;

            if (preg_match('#-(\d+)$#', $alias, $m)) {
                $alias = preg_replace('#-(\d+)$#', '-' . ($m[1] + 1) . '', $alias);
            }
            else {
                $alias .= '-2';
            }

            if (preg_match('#\((\d+)\)$#', $title, $m)) {
                $title = preg_replace('#\(\d+\)$#', '(' . ($m[1] + 1) . ')', $title);
            }
            else {
                $title .= ' (2)';
            }
        }

        return array($title, $alias);
    }


    /**
     * Method to test whether a record can be deleted.
     * Defaults to the permission set in the component.
     *
     * @param     object     A record object.
     *
     * @return    boolean    True if allowed to delete the record.
     */
    protected function canDelete($record)
    {
        if (!empty($record->id)) {
            if ($record->state != -2) return false;

            $access = ProjectforkHelperAccess::getActions('tasklist', $record->id);
            return $access->get('tasklist.delete');
        }
        else {
            $access = ProjectforkHelperAccess::getActions();
            return $access->get('tasklist.delete');
        }
    }


    /**
     * Method to test whether a record can have its state edited.
     * Defaults to the permission set in the component.
     *
     * @param     object     A record object.
     *
     * @return    boolean    True if allowed to delete the record.
     */
    protected function canEditState($record)
    {
        // Check for existing item.
        if (!empty($record->id)) {
            $access = ProjectforkHelperAccess::getActions('tasklist', $record->id);
            return $access->get('tasklist.edit.state');
        }
        else {
            return parent::canEditState('com_projectfork');
        }
    }


    /**
     * Method to test whether a record can be edited.
     * Defaults to the permission for the component.
     *
     * @param     object     $record    A record object.
     *
     * @return    boolean               True if allowed to edit the record.
     */
    protected function canEdit($record)
    {
        // Check for existing item.
        if (!empty($record->id)) {
            $access = ProjectforkHelperAccess::getActions('tasklist', $record->id);
            return $access->get('tasklist.edit');
        }
        else {
            $access = ProjectforkHelperAccess::getActions();
            return $access->get('tasklist.edit');
        }
    }
}
