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
 * Item Model for a Project form.
 *
 */
class ProjectforkModelProject extends JModelAdmin
{
    /**
     * The prefix to use with controller messages.
     *
     * @var    string
     */
    protected $text_prefix = 'COM_PROJECTFORK_PROJECT';


    /**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @see     JController
	 */
    public function __construct($config = array())
    {
        // Register dependencies
        JLoader::register('ProjectforkHelper',       JPATH_ADMINISTRATOR . '/components/com_projectfork/helpers/projectfork.php');
        JLoader::register('ProjectforkHelperAccess', JPATH_ADMINISTRATOR . '/components/com_projectfork/helpers/access.php');
        JLoader::register('ProjectforkHelperQuery',  JPATH_ADMINISTRATOR . '/components/com_projectfork/helpers/query.php');

        parent::__construct($config);
    }

    /**
     * Returns a Table object, always creating it.
     *
     * @param     string    The table type to instantiate
     * @param     string    A prefix for the table class name. Optional.
     * @param     array     Configuration array for model. Optional.
     *
     * @return    jtable    A database object
     */
    public function getTable($type = 'Project', $prefix = 'PFTable', $config = array())
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

            // Get the attachments
            $attachments = $this->getInstance('Attachments', 'ProjectforkModel');
            $item->attachment = $attachments->getItems('project', $item->id);
        }

        return $item;
    }


    /**
     * Method to get the user groups of a project
     *
     * @param     integer    The project id
     *
     * @return    array      The user groups
     **/
    public function getUserGroups($pk = NULL, $children = true)
    {
        $pk    = (!empty($pk)) ? $pk : (int) $this->getState($this->getName() . '.id');
        $table = $this->getTable();

        if ($pk > 0) {
            // Attempt to load the row.
            $return = $table->load($pk);

            // Check for a table object error.
            if ($return === false && $table->getError()) {
                $this->setError($table->getError());
                return false;
            }

            return ProjectforkHelper::getGroupsByAccess($table->access, $children);
        }

        return false;
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
        $form = $this->loadForm('com_projectfork.project', 'project', array('control' => 'jform', 'load_data' => $loadData));
        if (empty($form)) return false;

        $jinput = JFactory::getApplication()->input;
        $user   = JFactory::getUser();
        $id     =  $jinput->get('id', 0);

        $item_access = ProjectforkHelperAccess::getActions('project', $id);
        $access      = ProjectforkHelperAccess::getActions();


        // Check for existing item.
        // Modify the form based on Edit State access controls.
        if (($id != 0 && !$item_access->get('project.edit.state')) || ($id == 0 && !$access->get('project.edit.state'))) {
            // Disable fields for display.
            $form->setFieldAttribute('state', 'disabled', 'true');
            $form->setFieldAttribute('state', 'filter', 'unset');
        }

        return $form;
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
        $record = $this->getTable();
		$key    = $record->getKeyName();
        $pk     = (!empty($data[$key])) ? $data[$key] : (int) $this->getState($this->getName() . '.id');
		$is_new = true;

        if ($pk > 0) {
			if ($record->load($pk)) {
			    $is_new = false;
			}
		}

        // Alter the title for save as copy
        if (JRequest::getVar('task') == 'save2copy') {
            list($title, $alias) = $this->generateNewTitle($data['alias'], $data['title']);

            $data['title'] = $title;
            $data['alias'] = $alias;
        }
        else {
            // Always re-generate the alias unless save2copy
            $data['alias'] = '';
        }

        // Create access level
        if (isset($data['rules'])) {
            $prev_access = ($is_new ? 0 : $record->access);
            $access = ProjectforkHelperAccess::getViewLevelFromRules($data['rules'], $prev_access);

            if ($access) {
                $data['access'] = $access;
            }
        }
        else {
            if ($is_new) {
                $data['access'] = 1;
            }
            else {
                if (isset($data['access'])) {
                    unset($data['access']);
                }
            }
        }

        // Store the record
        if (parent::save($data)) {
            $id = $this->getState($this->getName() . '.id');

            $this->setActive(array('id' => $this->getState($this->getName() . '.id')));

            // Load the just updated row
            $updated = $this->getTable();
            if ($updated->load($id) === false) return false;

            // To keep data integrity, update all child assets
            if (!$is_new) {
                $props   = array('access', 'state', array('start_date', 'NE-SQLDATE'), array('end_date', 'NE-SQLDATE'));
                $changes = ProjectforkHelper::getItemChanges($record, $updated, $props);

                if (count($changes)) {
                    $tables = array('milestone', 'tasklist', 'task', 'topic', 'reply');
                    $field  = 'project_id.' . $id;

                    if (!ProjectforkHelperQuery::updateTablesByField($tables, $field, $changes)) {
                        return false;
                    }
                }
            }

            // Create repo base and attachments folder
            if (!$this->createRepository($updated)) {
                return false;
            }

            // Store the attachments
            if (isset($data['attachment']) && !$is_new) {
                $attachments = $this->getInstance('Attachments', 'ProjectforkModel');

                if ($attachments->getState('item.id') == 0) {
                    $attachments->setState('item.id', $this->getState($this->getName() . '.id'));
                }

                if (!$attachments->save($data['attachment'])) {
                    $this->setError($attachments->getError());
                    return false;
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
            $milestones = JTable::getInstance('Milestone', 'PFTable');
            $tasklists  = JTable::getInstance('Tasklist', 'PFTable');
            $tasks      = JTable::getInstance('Task', 'PFTable');
            $topics     = JTable::getInstance('Topic', 'PFTable');
            $replies    = JTable::getInstance('Reply', 'PFTable');

            $parent_data = array();
            $parent_data['state'] = $value;

            $milestones->updateByReference($pks, 'project_id', $parent_data);
            $tasklists->updateByReference($pks, 'project_id', $parent_data);
            $tasks->updateByReference($pks, 'project_id', $parent_data);
            $topics->updateByReference($pks, 'project_id', $parent_data);
            $replies->updateByReference($pks, 'project_id', $parent_data);
        }

        return $result;
    }


    /**
     * Method to set a project to active on the current user
     *
     * @param     array      The form data
     *
     * @return    boolean    True on success
     */
    public function setActive($data)
    {
        $app = JFactory::getApplication();

        $id = (int) $data['id'];

        if ($id) {
            // Load the project and verify the access
            $user  = JFactory::getUser();
            $table = $this->getTable();

            if ($table->load($id) === false) {
                return false;
            }

            if (!$user->authorise('core.admin')) {
                if (!in_array($table->access, $user->getAuthorisedViewLevels())) {
                    $this->setError(JText::_('COM_PROJECTFORK_ERROR_PROJECT_ACCESS'));
                    return false;
                }
            }

            $app->setUserState('com_projectfork.project.active.id', $id);
            $app->setUserState('com_projectfork.project.active.title', $table->title);
        }
        else {
            $app->setUserState('com_projectfork.project.active.id', 0);
            $app->setUserState('com_projectfork.project.active.title', '');
        }

        return true;
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

        $app        = JFactory::getApplication();
        $milestones = JTable::getInstance('Milestone', 'PFTable');
        $tasklists  = JTable::getInstance('Tasklist', 'PFTable');
        $tasks      = JTable::getInstance('Task', 'PFTable');
        $topics     = JTable::getInstance('Topics', 'PFTable');
        $replies    = JTable::getInstance('Reply', 'PFTable');

        // Delete all other items referenced to each project
        if (!$milestones->deleteByReference($pks, 'project_id')) $success = false;
        if (!$tasklists->deleteByReference($pks, 'project_id'))  $success = false;
        if (!$tasks->deleteByReference($pks, 'project_id'))      $success = false;
        if (!$topics->deleteByReference($pks, 'project_id'))     $success = false;
        if (!$replies->deleteByReference($pks, 'project_id'))    $success = false;

        $active_id = (int) $app->getUserState('com_projectfork.project.active.id', 0);

        // The active project has been delete?
        if (in_array($active_id, $pks)) {
            $this->setActive(array('id' => 0));
        }

        return $success;
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
     * Method to create a project repository structure
     *
     * @param     object    $item    The project JTable object
     *
     * @return    boolean     True on success, otherwise False
     */
    protected function createRepository($item)
    {
        if (!is_object($item) || empty($item)) {
            return false;
        }

        $registry = new JRegistry;
        $registry->loadString($item->attribs);

        $repo_dir   = ($registry->get('repo_dir') ? (int) $registry->get('repo_dir') : 0 );
        $attach_dir = ($registry->get('attachments_dir') ? (int) $registry->get('attachments_dir') : 0 );
        $suffix     = (JFactory::getApplication()->isSite() ? 'Form' : '');

        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);

        if ($repo_dir) {
            // A repo dir reference is set. See if the dir actually exists
            $dir = $this->getInstance('Directory' . $suffix, 'ProjectforkModel', array('ignore_request'));

            if (!$dir->getState('create_repo')) {
                $dir->setState('create_repo', true);
            }

            $record = $dir->getItem($repo_dir);

            if ($record === false || $record->id == 0) {
                $repo_dir = 0;
            }
        }

        if ($attach_dir) {
            // A repo attachments dir reference is set. See if the dir actually exists
            $dir = $this->getInstance('Directory' . $suffix, 'ProjectforkModel', array('ignore_request'));

            if (!$dir->getState('create_repo')) {
                $dir->setState('create_repo', true);
            }

            $record = $dir->getItem($attach_dir);

            if ($record === false || $record->id == 0) {
                $attach_dir = 0;
            }
        }

        // Create repo dir if it does not exist
        if (!$repo_dir) {
            $dir = $this->getInstance('Directory' . $suffix, 'ProjectforkModel', array('ignore_request'));

            if (!$dir->getState('create_repo')) {
                $dir->setState('create_repo', true);
            }

            $data = array();
            $data['id']         = 0;
            $data['protected']  = 1;
            $data['title']      = $item->title;
            $data['project_id'] = $item->id;
            $data['created']    = $item->created;
            $data['created_by'] = $item->created_by;
            $data['access']     = $item->access;
            $data['parent_id']  = 1;

            if (!$dir->save($data)) {
                $this->setError($dir->getError());
                return false;
            }

            $repo_dir = $dir->getState($dir->getName() . '.id');
            $registry->set('repo_dir', $repo_dir);

            // Update the project attribs
            $query->clear();
            $query->update('#__pf_projects')
                  ->set('attribs = ' . $db->quote((string) $registry))
                  ->where('id = ' . $db->quote($item->id));

            $db->setQuery((string) $query);
            $db->execute();
        }

        // Create attachments dir if it does not exist
        if (!$attach_dir && $repo_dir > 0) {
            $dir = $this->getInstance('Directory' . $suffix, 'ProjectforkModel', array('ignore_request'));

            if (!$dir->getState('create_repo')) {
                $dir->setState('create_repo', true);
            }

            $data = array();
            $data['id']         = 0;
            $data['protected']  = 1;
            $data['title']      = JText::_('COM_PROJECTFORK_REPO_TITLE_ATTACHMENTS');
            $data['project_id'] = $item->id;
            $data['created']    = $item->created;
            $data['created_by'] = $item->created_by;
            $data['access']     = $item->access;
            $data['parent_id']  = $repo_dir;

            if (!$dir->save($data)) {
                $this->setError($dir->getError() . ' yepp');
                return false;
            }

            $registry->set('attachments_dir', $dir->getState($dir->getName() . '.id'));

            // Update the project attribs
            $query->clear();
            $query->update('#__pf_projects')
                  ->set('attribs = ' . $db->quote((string) $registry))
                  ->where('id = ' . $db->quote($item->id));

            $db->setQuery((string) $query);
            $db->execute();
        }

        return true;
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
    protected function generateNewTitle($alias, $title)
    {
        // Alter the title & alias
        $table = $this->getTable();

        while ($table->load(array('alias' => $alias)))
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

            $access = ProjectforkHelperAccess::getActions('project', $record->id);
            return $access->get('project.delete');
        }
        else {
            $access = ProjectforkHelperAccess::getActions();
            return $access->get('project.delete');
        }
    }


    /**
     * Method to test whether a record can have its state edited.
     * Defaults to the permission set in the component.
     *
     * @param     object     A record object.
     *
     * @return    boolean    True if allowed to edit the state of the record.
     */
    protected function canEditState($record)
    {
        // Check for existing item.
        if (!empty($record->id)) {
            $access = ProjectforkHelperAccess::getActions('project', $record->id);
            return $access->get('project.edit.state');
        }
        else {
            return parent::canEditState('com_projectfork');
        }
    }


    /**
     * Method to test whether a record can be edited.
     * Defaults to the permission set in the component.
     *
     * @param     object     A record object.
     *
     * @return    boolean    True if allowed to edit the record.
     */
    protected function canEdit($record)
    {
        // Check for existing item.
        if (!empty($record->id)) {
            $access = ProjectforkHelperAccess::getActions('project', $record->id);
            return $access->get('project.edit');
        }
        else {
            $access = ProjectforkHelperAccess::getActions();
            return $access->get('project.edit');
        }
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
}
