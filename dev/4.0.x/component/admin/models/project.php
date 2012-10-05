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
        JLoader::register('ProjectforkHelper',           JPATH_ADMINISTRATOR . '/components/com_projectfork/helpers/projectfork.php');
        JLoader::register('ProjectforkHelperAccess',     JPATH_ADMINISTRATOR . '/components/com_projectfork/helpers/access.php');
        JLoader::register('ProjectforkHelperQuery',      JPATH_ADMINISTRATOR . '/components/com_projectfork/helpers/query.php');
        JLoader::register('ProjectforkHelperRepository', JPATH_ADMINISTRATOR . '/components/com_projectfork/helpers/repository.php');

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
     * Method to get the user groups assigned to a project
     *
     * @param     integer    The project id
     *
     * @return    array      The user groups
     **/
    public function getUserGroups($pk = NULL)
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

            return ProjectforkHelperAccess::getGroupsByAccessLevel($table->access);
        }

        return false;
    }


    /**
     * Method to delete a project logo
     *
     * @param     integer    The project id
     *
     * @return    boolean     True on success, False on error
     **/
    public function deleteLogo($pk = NULL)
    {
        $pk = (!empty($pk)) ? (int) $pk : (int) $this->getState($this->getName() . '.id');

        $base_path = JPATH_ROOT . '/media/com_projectfork/repo/0/logo';
        $img_path  = NULL;

        if (JFile::exists($base_path . '/' . $pk . '.jpg')) {
            $img_path = $base_path . '/' . $pk . '.jpg';
        }
        elseif (JFile::exists($base_path . '/' . $pk . '.jpeg')) {
            $img_path = $base_path . '/' . $pk . '.jpeg';
        }
        elseif (JFile::exists($base_path . '/' . $pk . '.png')) {
            $img_path = $base_path . '/' . $pk . '.png';
        }
        elseif (JFile::exists($base_path . '/' . $pk . '.gif')) {
            $img_path = $base_path . '/' . $pk . '.gif';
        }

        // No image found
        if (!$img_path) {
            return true;
        }

        if (!JFile::delete($img_path)) {
            return false;
        }

        return true;
    }


    public function saveLogo($file = NULL, $pk = NULL)
    {
        $pk = (!empty($pk)) ? (int) $pk : (int) $this->getState($this->getName() . '.id');

        if (empty($file)) {
            $file_form = JRequest::getVar('jform', '', 'files', 'array');

            if (is_array($file_form)) {
                if (isset($file_form['name']['attribs']['logo'])) {
                    if ($file_form['name']['attribs']['logo'] == '') {
                        return true;
                    }

                    $file = array();

                    $file['name']     = $file_form['name']['attribs']['logo'];
                    $file['type']     = $file_form['type']['attribs']['logo'];
                    $file['tmp_name'] = $file_form['tmp_name']['attribs']['logo'];
                    $file['error']    = $file_form['error']['attribs']['logo'];
                    $file['size']     = $file_form['size']['attribs']['logo'];

                    if ($file['error']) {
                        $error = ProjectforkHelperRepository::getFileErrorMsg($file['error'], $file['name']);
                        $this->setError($error);
                        return false;
                    }
                }
            }

            if (empty($file)) {
                return true;
            }
        }

        if (!$pk) {
            return false;
        }

        if (empty($file)) {
            $this->setError(JText::_('COM_PROJECTFORK_WARNING_NO_FILE_SELECTED'));
            return false;
        }

        if (!ProjectforkProcImage::isImage($file['name'], $file['tmp_name'])) {
            $this->setError(JText::_('COM_PROJECTFORK_WARNING_NOT_AN_IMAGE'));
            return false;
        }

        // Delete any previous logo
        if (!$this->deleteLogo($pk)) {
            return false;
        }

        $uploadpath = JPATH_ROOT . '/media/com_projectfork/repo/0/logo';
        $name = $pk . '.' . strtolower(JFile::getExt($file['name']));

        if (JFile::upload($file['tmp_name'], $uploadpath . '/' . $name) === true) {
            return true;
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
        $id     = (int) $jinput->get('id', 0);

        $item_access = ProjectforkHelperAccess::getActions('project', $id);
        $access      = ProjectforkHelperAccess::getActions();

        // Check for existing item.
        // Modify the form based on Edit State access controls.
        if (($id != 0 && !$item_access->get('project.edit.state')) || ($id == 0 && !$access->get('project.edit.state'))) {
            // Disable fields for display.
            $form->setFieldAttribute('state', 'disabled', 'true');
            $form->setFieldAttribute('state', 'filter', 'unset');
        }

        if ($id) {
            ProjectforkHelper::setActiveProject($id);
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

        // Make sure the title and alias are always unique
        $data['alias'] = '';
        list($title, $alias) = $this->generateNewTitle($data['title'], $data['alias'], $pk);

        $data['title'] = $title;
        $data['alias'] = $alias;

        // Handle permissions and access level
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

        // Delete logo?
        if (isset($data['attribs']['logo']['delete']) && $pk && !$is_new) {
            $this->deleteLogo($pk);
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
                    $tables = array('milestone', 'tasklist', 'task', 'topic', 'reply', 'comment', 'directory', 'note', 'file', 'time');
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

                if ((int) $attachments->getState('item.id') == 0) {
                    $attachments->setState('item.id', $id);
                }

                if ((int) $attachments->getState('item.project') == 0) {
                    $attachments->setState('item.project', $id);
                }

                if (!$attachments->save($data['attachment'])) {
                    $this->setError($attachments->getError());
                    return false;
                }
            }

            // Handle project logo
            if (!$this->saveLogo()) {
                return false;
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
        $result  = parent::publish($pks, $value);
        $changes = array('state', $value);

        if ($result) {
            // State change succeeded. Now update all children
            foreach ($pks AS $id)
            {
                $tables = array('milestone', 'tasklist', 'task', 'topic', 'reply', 'comment', 'directory', 'note', 'file', 'time');
                $field  = 'project_id.' . $id;

                if (!ProjectforkHelperQuery::updateTablesByField($tables, $field, $changes)) {
                    $result = false;
                }
            }
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
        $id  = (int) $data['id'];

        if ($id) {
            // Load the project and verify the access
            $user  = JFactory::getUser();
            $table = $this->getTable();

            if ($table->load($id) === false) {
                if ($table->getError()) {
                    $this->setError($table->getError());
                }

                return false;
            }

            if (!$user->authorise('core.admin')) {
                if (!in_array($table->access, $user->getAuthorisedViewLevels())) {
                    $this->setError(JText::_('COM_PROJECTFORK_ERROR_PROJECT_ACCESS_DENIED'));
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
        // Initialise variables.
		$dispatcher = JDispatcher::getInstance();
		$pks   = (array) $pks;
		$table = $this->getTable();

        $active_id = ProjectforkHelper::getActiveProjectId();

		// Include the content plugins for the on delete events.
		JPluginHelper::importPlugin('content');

		// Iterate the items to delete each one.
		foreach ($pks as $i => $pk)
		{
			if ($table->load($pk)) {
				if ($this->canDelete($table)) {
					$context = $this->option . '.' . $this->name;
					// Trigger the onContentBeforeDelete event.
					$result = $dispatcher->trigger($this->event_before_delete, array($context, $table));

					if (in_array(false, $result, true)) {
						$this->setError($table->getError());
						return false;
					}

					if (!$table->delete($pk)) {
						$this->setError($table->getError());
						return false;
					}

                    // Try to delete the repo
                    $repo = ProjectforkHelperRepository::getBasePath($pk);

                    if (JFolder::exists($repo)) {
                        JFolder::delete($repo);
                    }

                    // Try to delete the logo
                    $this->deleteLogo($pk);

                    // Check if the currently active project is being deleted.
                    // If so, clear it from the session
                    if ($active_id == $pk) {
                        $this->setActive(array('id' => 0));
                    }

                    // Delete every item related to this project
                    $tables = array('milestone', 'tasklist', 'task', 'topic', 'reply', 'comment', 'directory', 'note', 'file', 'time', 'attachment');
                    $field  = 'project_id.' . $pk;

                    if (!ProjectforkHelperQuery::deleteFromTablesByField($tables, $field)) {
                        return false;
                    }

					// Trigger the onContentAfterDelete event.
					$dispatcher->trigger($this->event_after_delete, array($context, $table));
				}
				else {
					// Prune items that you can't change.
					unset($pks[$i]);

					$error = $this->getError();

					if ($error) {
						JError::raiseWarning(500, $error);
						return false;
					}
					else {
						JError::raiseWarning(403, JText::_('JLIB_APPLICATION_ERROR_DELETE_NOT_PERMITTED'));
						return false;
					}
				}
			}
			else {
				$this->setError($table->getError());
				return false;
			}
		}

		// Clear the component's cache
		$this->cleanCache();

		return true;
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
     * @param     string    The title
     * @param     string    The alias
     * @param     integer    The item id
     *
     * @return    array     Contains the modified title and alias
     */
    protected function generateNewTitle($title, $alias = '', $id = 0)
    {
        $table = $this->getTable();
        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);

        if (empty($alias)) {
            $alias = JApplication::stringURLSafe($title);

            if (trim(str_replace('-', '', $alias)) == '') {
                $alias = JApplication::stringURLSafe(JFactory::getDate()->format('Y-m-d-H-i-s'));
            }
        }

        $query->select('COUNT(id)')
              ->from($table->getTableName())
              ->where('alias = ' . $db->quote($alias));

        if ($id) {
            $query->where('id != ' . intval($id));
        }

        $db->setQuery((string) $query);
        $count = (int) $db->loadResult();

        if ($id > 0 && $count == 0) {
            return array($title, $alias);
        }
        elseif ($id == 0 && $count == 0) {
            return array($title, $alias);
        }
        else {
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
            $access = ProjectforkHelperAccess::getActions();
            return $access->get('project.edit.state');
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
            $user   = JFactory::getUser();
            $access = ProjectforkHelperAccess::getActions('project', $record->id);
            return ($access->get('project.edit') || ($access->get('project.edit.own') && $record->created_by == $user->id));
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
