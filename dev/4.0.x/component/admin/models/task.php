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
class ProjectforkModelTask extends JModelAdmin
{
    /**
     * The prefix to use with controller messages.
     *
     * @var    string
     */
    protected $text_prefix = 'COM_PROJECTFORK_TASK';


    /**
     * Returns a Table object, always creating it.
     *
     * @param     string    The table type to instantiate
     * @param     string    A prefix for the table class name. Optional.
     * @param     array     Configuration array for model. Optional.
     *
     * @return    jtable    A database object
     */
    public function getTable($type = 'Task', $prefix = 'PFTable', $config = array())
    {
        return JTable::getInstance($type, $prefix, $config);
    }


    /**
     * Method to get a single record.
     *
     * @param     integer    The id of the primary key.
     * @return    mixed      Object on success, false on failure.
     */
    public function getItem($pk = null)
    {
        if ($item = parent::getItem($pk)) {
            // Convert the params field to an array.
            $registry = new JRegistry;
            $registry->loadString($item->attribs);
            $item->attribs = $registry->toArray();

            $item->users = $this->getUsers($pk);

            // Convert seconds back to minutes
            if ($item->estimate > 0) {
                $item->estimate = round($item->estimate / 60);
            }

            // Get the attachments
            $attachments = $this->getInstance('Attachments', 'ProjectforkModel');
            $item->attachment = $attachments->getItems('task', $item->id);

        }

        return $item;
    }


    /**
     * Method to get assigned users of a task
     *
     * @param     integer    The id of the primary key.
     * @return    array      The assigned users
     */
    public function getUsers($pk = NULL)
    {
        if (!$pk) $pk = $this->getState($this->getName() . '.id');
        if (!$pk) return array();

        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->select('user_id')
              ->from('#__pf_ref_users')
              ->where('item_type = ' . $db->quote('task'))
              ->where('item_id = ' . $db->quote($pk));

        $db->setQuery((string) $query);
        $data = (array) $db->loadResultArray();
        $list = array();

        foreach($data AS $i => $uid)
        {
            $list['user' . $i] = $uid;
        }

        return $list;
    }


    /**
     * Method to get the record form.
     *
     * @param     array      Data for the form.
     * @param     boolean    True if the form is to load its own data (default case), false if not.
     * @return    mixed      A JForm object on success, false on failure
     */
    public function getForm($data = array(), $loadData = true)
    {
        // Get the form.
        $form = $this->loadForm('com_projectfork.task', 'task', array('control' => 'jform', 'load_data' => $loadData));
        if (empty($form)) return false;

        $is_new    = ((int) $this->getState($this->getName() . '.id') > 0) ? false : true;
        $project   = (int) $form->getValue('project_id');
        $milestone = (int) $form->getValue('milestone_id');
        $list      = (int) $form->getValue('list_id');

        // Override data if not set
        if ($is_new) {
            if ($project == 0) {
                $app       = JFactory::getApplication();
                $active_id = (int) $app->getUserState('com_projectfork.project.active.id', 0);

                $form->setValue('project_id', null, $active_id);
            }

            // Override milestone selection if set
            if ($milestone == 0) {
                $form->setValue('milestone_id', null, JRequest::getUInt('milestone_id'));
            }

            // Override task list selection if set
            if ($list == 0) {
                $form->setValue('list_id', null, JRequest::getUInt('list_id'));
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
     * A protected method to get a set of ordering conditions.
     *
     * @param     object    A record object.
     *
     * @return    array     An array of conditions to add to add to ordering queries.
     */
    protected function getReorderConditions($table)
    {
        $catid = intval($table->project_id) . '-' . intval($table->milestone_id) . '-' . intval($table->list_id);

        $condition = array();
        $condition[] = 'catid = '.(int) $catid;

        return $condition;
    }


    /**
     * Prepare and sanitise the table data prior to saving.
     *
     * @param     jtable    A JTable object.
     *
     * @return    void
     */
    protected function prepareTable(&$table)
    {
        // Generate catid
        $catid = intval($table->project_id) . '-' . intval($table->milestone_id) . '-' . intval($table->list_id);

        // Reorder the items within the category so the new item is first
        if (empty($table->id)) {
            $table->reorder('catid = '.(int) $catid.' AND state >= 0');
        }
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
            list($title, $alias) = $this->generateNewTitle($data['alias'], $data['title'], $data['project_id']);
            $data['title']    = $title;
            $data['alias']    = $alias;
        }
        else {
            // Always re-generate the alias unless save2copy
            $data['alias'] = '';
        }

        // Try to convert estimate string to time
        if (!is_numeric($data['estimate'])) {
            $estimate_time = strtotime($data['estimate']);

            if ($estimate_time === false || $estimate_time < 0) {
                $data['estimate'] = 1;
            }
            else {
                $data['estimate'] = $estimate_time - time();
            }
        }
        else {
            // not a literal time, so convert minutes to secs
            $data['estimate'] = $data['estimate'] * 60;
        }

        if (!isset($data['attachment'])) {
            $data['attachment'] = array();
        }

        // Store the base record
        if(parent::save($data)) {
            $id   = $this->getState($this->getName() . '.id');
            $item = $this->getItem($id);

            // Store the attachments
            if (isset($data['attachment'])) {
                $attachments = $this->getInstance('Attachments', 'ProjectforkModel');
                $attachments->setState('item.id', $id);

                if (!$attachments->save($data['attachment'])) {
                    JError::raiseWarning(500, $attachments->getError());
                    $this->setError($attachments->getError());
                }
            }


            return true;
        }

        return false;
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
     * @param     string    The alias
     * @param     string    The title
     *
     * @return    array     Contains the modified title and alias
     */
    protected function generateNewTitle($alias, $title, $project_id)
    {
        // Alter the title & alias
        $table = $this->getTable();

        while ($table->load(array('alias' => $alias, 'project_id' => $project_id)))
        {
            $m = null;
            if (preg_match('#-(\d+)$#', $alias, $m)) {
                $alias = preg_replace('#-(\d+)$#', '-'.($m[1] + 1).'', $alias);
            }
            else {
                $alias .= '-2';
            }

            if (preg_match('#\((\d+)\)$#', $title, $m)) {
                $title = preg_replace('#\(\d+\)$#', '('.($m[1] + 1).')', $title);
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

            $access = ProjectforkHelperAccess::getActions('task', $record->id);
            return $access->get('task.delete');
        }
        else {
            $access = ProjectforkHelperAccess::getActions();
            return $access->get('task.delete');
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
            $access = ProjectforkHelperAccess::getActions('task', $record->id);
            return $access->get('task.edit.state');
        }
        else {
            return parent::canEditState('com_projectfork');
        }
    }


    /**
     * Method to test whether a record can be edited.
     * Defaults to the permission for the component.
     *
     * @param     object     A record object.
     *
     * @return    boolean    True if allowed to edit the record.
     */
    protected function canEdit($record)
    {
        // Check for existing item.
        if (!empty($record->id)) {
            $access = ProjectforkHelperAccess::getActions('task', $record->id);
            return $access->get('task.edit');
        }
        else {
            $access = ProjectforkHelperAccess::getActions();
            return $access->get('task.edit');
        }
    }
}
