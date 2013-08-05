<?php
/**
 * @package      Projectfork
 * @subpackage   Projects
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


jimport('joomla.application.component.modeladmin');
jimport('projectfork.application.helper');
jimport('projectfork.access.helper');

if (PFApplicationHelper::exists('com_pfrepo')) {
    JLoader::register('PFrepoHelper', JPATH_ADMINISTRATOR . '/components/com_pfrepo/helpers/pfrepo.php');
}

/**
 * Item Model for a Project form.
 *
 */
class PFprojectsModelProject extends JModelAdmin
{
    /**
     * The prefix to use with controller messages.
     *
     * @var    string    
     */
    protected $text_prefix = 'COM_PROJECTFORK_PROJECT';


    /**
     * Returns a Table object, always creating it.
     *
     * @param     string    The table type to instantiate
     * @param     string    A prefix for the table class name. Optional.
     * @param     array     Configuration array for model. Optional.
     *
     * @return    jtable    A database object
     */
    public function getTable($type = 'Project', $prefix = 'PFtable', $config = array())
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
            if (PFApplicationHelper::exists('com_pfrepo')) {
                $attachments = $this->getInstance('Attachments', 'PFrepoModel');
                $item->attachment = $attachments->getItems('com_pfprojects.project', $item->id);
            }
            else {
                $item->attachment = array();
            }

            // Get the labels
            $labels = $this->getInstance('Labels', 'PFmodel');
            $item->labels = $labels->getItems($item->id);
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

            return PFAccessHelper::getGroupsByAccessLevel($table->access);
        }

        return false;
    }


    /**
     * Method to delete a project logo
     *
     * @param     integer    The project id
     *
     * @return    boolean    True on success, False on error
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
                        if (PFApplicationHelper::exists('com_pfrepo')) {
                            $error = PFrepoHelper::getFileErrorMsg($file['error'], $file['name']);
                            $this->setError($error);
                        }

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

        if (!PFImage::isValid($file['name'], $file['tmp_name'])) {
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
        $form = $this->loadForm('com_pfprojects.project', 'project', array('control' => 'jform', 'load_data' => $loadData));
        if (empty($form)) return false;

        $jinput = JFactory::getApplication()->input;
        $user   = JFactory::getUser();
        $id     = (int) $jinput->get('id', 0);

        // Check for existing item.
        // Modify the form based on Edit State access controls.
        if ($id != 0 && (!$user->authorise('core.edit.state', 'com_pfprojects.project.' . $id)) || ($id == 0 && !$user->authorise('core.edit.state', 'com_pfprojects')))
        {
            // Disable fields for display.
            $form->setFieldAttribute('state', 'disabled', 'true');
            $form->setFieldAttribute('start_date', 'disabled', 'true');
            $form->setFieldAttribute('end_date', 'disabled', 'true');

            // Disable fields while saving.
            $form->setFieldAttribute('state', 'filter', 'unset');
            $form->setFieldAttribute('start_date', 'filter', 'unset');
            $form->setFieldAttribute('end_date', 'filter', 'unset');
        }

        // Always disable these fields while saving
        $form->setFieldAttribute('alias', 'filter', 'unset');

        // Disable these fields if not an admin
        if (!$user->authorise('core.admin', 'com_pfprojects')) {
            $form->setFieldAttribute('access', 'disabled', 'true');
            $form->setFieldAttribute('access', 'filter', 'unset');

            $form->setFieldAttribute('rules', 'disabled', 'true');
            $form->setFieldAttribute('rules', 'filter', 'unset');
        }

        if ($id) {
            // Set the project as active when editing
            PFApplicationHelper::setActiveProject($id);
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
        $table  = $this->getTable();
        $key    = $table->getKeyName();
        $pk     = (!empty($data[$key])) ? $data[$key] : (int) $this->getState($this->getName() . '.id');
        $is_new = true;
        $old    = null;

        // Include the content plugins for the on save events.
        JPluginHelper::importPlugin('content');
        $dispatcher = JDispatcher::getInstance();

        $cfg = JComponentHelper::getParams('com_pfprojects');
        $create_group   = (int) $cfg->get('create_group');
        $group_location = (int) $cfg->get('group_location');
        $group_id       = 0;

        if (!$group_location) $group_location = 1;

        // Allow an exception to be thrown.
        try {
            // Load the row if saving an existing record.
            if ($pk > 0) {
                if ($table->load($pk)) {
                    $is_new = false;
                    $old    = clone $table;
                }
            }

            // Make sure the title and alias are always unique
            $data['alias'] = '';
            list($title, $alias) = $this->generateNewTitle($data['title'], $data['alias'], $pk);

            $data['title'] = $title;
            $data['alias'] = $alias;

            // Create new user group?
            if ($is_new && $create_group) {
                $group_users = array(JFactory::getUser()->get('id'));
                $group_id = $this->createUserGroup($data['title'], $group_location, $group_users);

                if ($group_id) {
                    if (!isset($data['attribs'])) $data['attribs'] = array();
                    $data['attribs']['usergroup'] = $group_id;
                }
            }

            // Inject group into component rules
            if (isset($data['component_rules']) && $is_new && $create_group) {
                foreach ($data['component_rules'] AS $component => $rules)
                {
                    foreach ($rules AS $action => $groups)
                    {
                        if (!is_numeric($action) && is_array($groups)) {
                            foreach ($groups AS $gid => $v)
                            {
                                if ($gid == 0) {
                                    if ($group_id) {
                                        unset($data['component_rules'][$component][$action][$gid]);
                                        $data['component_rules'][$component][$action][$group_id] = $v;
                                    }
                                    else {
                                        unset($data['component_rules'][$component][$action][$gid]);
                                    }
                                }
                            }
                        }
                    }
                }
            }

            // Rename project group
            if (!$is_new && $create_group) {
                $reg = new JRegistry();
                $reg->loadString($table->attribs);

                $group_id = (int) $reg->get('usergroup');

                if ($group_id) {
                    // Re-inject the group and other attribs
                    if (!isset($data['attribs'])) {
                        $data['attribs'] = $reg->toArray();
                    }
                    else {
                        $data['attribs']['usergroup'] = $group_id;
                    }

                    // Rename
                    $this->renameUserGroup($group_id, $data['title']);
                }
            }

            // Handle permissions and access level
            if (isset($data['rules'])) {
                // Inject newly created group
                if ($is_new && $create_group) {
                    foreach ($data['rules'] AS $action => $groups)
                    {
                        if (is_numeric($action) && is_numeric($groups) && $groups == 0) {
                            unset($data['rules'][$action]);

                            if ($group_id) {
                                $data['rules'][$action] = $group_id;
                            }
                        }

                        if (!is_numeric($action) && is_array($groups)) {
                            foreach ($groups AS $gid => $v)
                            {
                                if ($gid == 0) {
                                    if ($group_id) {
                                        unset($data['rules'][$action][$gid]);
                                        $data['rules'][$action][$group_id] = $v;
                                    }
                                    else {
                                        unset($data['rules'][$action][$gid]);
                                    }
                                }
                            }
                        }
                    }
                }

                $prev_access = ($is_new ? 0 : $table->access);
                $access = PFAccessHelper::getViewLevelFromRules($data['rules'], $prev_access);

                if ($access) {
                    $data['access'] = $access;
                }
            }
            else {
                if ($is_new) {
                    $data['access'] = (int) JFactory::getConfig()->get('access');
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

            // Make item published by default if new
            if (!isset($data['state']) && $is_new) {
                $data['state'] = 1;
            }

            // Bind the data.
            if (!$table->bind($data)) {
                $this->setError($table->getError());
                return false;
            }

            // Prepare the row for saving
            $this->prepareTable($table);

            // Check the data.
            if (!$table->check()) {
                $this->setError($table->getError());
                return false;
            }

            // Trigger the onContentBeforeSave event.
            $result = $dispatcher->trigger($this->event_before_save, array($this->option . '.' . $this->name, &$table, $is_new));

            if (in_array(false, $result, true)) {
                $this->setError($table->getError());
                return false;
            }

            // Store the data.
            if (!$table->store()) {
                $this->setError($table->getError());
                return false;
            }

            $pk_name = $table->getKeyName();

            if (isset($table->$pk_name)) {
                $this->setState($this->getName() . '.id', $table->$pk_name);
            }

            $this->setState($this->getName() . '.new', $is_new);

            $id = $this->getState($this->getName() . '.id');

            $this->setActive(array('id' => $id));

            // Add to watch list
            if ($is_new) {
                $cid = array($id);

                if (!$this->watch($cid, 1)) {
                    return false;
                }
            }

            // Create repo base and attachments folder
            if (PFApplicationHelper::exists('com_pfrepo')) {
                if (!$this->createRepository($table)) {
                    return false;
                }

                // Store the attachments
                if (isset($data['attachment']) && !$is_new) {
                    $attachments = $this->getInstance('Attachments', 'PFrepoModel', array('ignore_request' => true));

                    $attachments->setState('item.type', 'com_pfprojects.project');
                    $attachments->setState('item.id', $id);
                    $attachments->setState('item.project', $id);

                    if (!$attachments->save($data['attachment'])) {
                        $this->setError($attachments->getError());
                        return false;
                    }
                }
            }

            // Store the labels
            if (isset($data['labels'])) {
                $labels = $this->getInstance('Labels', 'PFModel');
                $lbl_project = (int) $labels->getState('item.project');

                if ($lbl_project != $id) {
                    $labels->setState('item.project', $id);
                }

                if (!$labels->save($data['labels'])) {
                    $this->setError($labels->getError());
                    return false;
                }
            }

            // Handle project logo
            if (!$this->saveLogo()) {
                return false;
            }

            // Clean the cache.
            $this->cleanCache();

            // Trigger the onContentAfterSave event.
            $dispatcher->trigger($this->event_after_save, array($this->option . '.' . $this->name, &$table, $is_new));
        }
        catch (Exception $e) {
            $this->setError($e->getMessage());
            return false;
        }

        return true;
    }


    /**
     * Method to watch an item
     *
     * @param    array      $pks      The items to watch
     * @param    integer    $value    1 to watch, 0 to unwatch
     * @param    integer    $uid      The user id to watch the item
     */
    public function watch(&$pks, $value = 1, $uid = null)
    {
        $user  = JFactory::getUser($uid);
        $table = $this->getTable();
        $pks   = (array) $pks;

        $is_admin = $user->authorise('core.admin', $this->option);
        $levels   = $user->getAuthorisedViewLevels();
        $projects = array();

        $item_type = 'com_pfprojects.project';

        // Access checks.
        foreach ($pks as $i => $pk)
        {
            $table->reset();

            if ($table->load($pk)) {
                if (!$is_admin && !in_array($table->access, $levels)) {
                    unset($pks[$i]);
                    JError::raiseWarning(403, JText::_('JERROR_ALERTNOAUTHOR'));
                    $this->setError(JText::_('JERROR_ALERTNOAUTHOR'));
                    return false;
                }

                $projects[$pk] = (int) $table->id;
            }
            else {
                unset($pks[$i]);
            }
        }

        // Attempt to watch/unwatch the selected items
        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);

        foreach ($pks AS $i => $pk)
        {
            $query->clear();

            if ($value == 0) {
                $query->delete('#__pf_ref_observer')
                      ->where('item_type = ' . $db->quote($item_type) )
                      ->where('item_id = ' . $db->quote((int) $pk))
                      ->where('user_id = ' . $db->quote((int) $user->get('id')));

                $db->setQuery($query);
                $db->execute();

                if ($db->getError()) {
                    $this->setError($db->getError());
                    return false;
                }
            }
            else {
                $query->select('COUNT(*)')
                      ->from('#__pf_ref_observer')
                      ->where('item_type = ' . $db->quote($item_type) )
                      ->where('item_id = ' . $db->quote((int) $pk))
                      ->where('user_id = ' . $db->quote((int) $user->get('id')));

                $db->setQuery($query);
                $count = (int) $db->loadResult();

                if (!$count) {
                    $data = new stdClass;

                    $data->user_id   = (int) $user->get('id');
                    $data->item_type = $item_type;
                    $data->item_id   = (int) $pk;
                    $data->project_id= (int) $projects[$pk];

                    $db->insertObject('#__pf_ref_observer', $data);

                    if ($db->getError()) {
                        $this->setError($db->getError());
                        return false;
                    }
                }
            }
        }

        // Clear the component's cache
        $this->cleanCache();

        return true;
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
        if (!isset($data['id'])) {
            return false;
        }

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

            if (!$user->authorise('core.admin', 'com_pfprojects')) {
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
        $pks   = (array) $pks;
        $table = $this->getTable();
        $query = $this->_db->getQuery(true);

        $active_id   = PFApplicationHelper::getActiveProjectId();
        $repo_exists = PFApplicationHelper::exists('com_pfrepo');

        if ($repo_exists) {
            $base_path = PFrepoHelper::getBasePath();
        }

        // Include the content plugins for the on delete events.
        $dispatcher = JDispatcher::getInstance();
        JPluginHelper::importPlugin('content');

        // Iterate the items to delete each one.
        foreach ($pks as $i => $pk)
        {
            // Try to load from the db
            if ($table->load($pk) === false) {
                $this->setError($table->getError());
                return false;
            }

            // Check delete permission
            if (!$this->canDelete($table)) {
                unset($pks[$i]);

                $error = $this->getError();

                if ($error) {
                    JError::raiseWarning(500, $error);
                }
                else {
                    JError::raiseWarning(403, JText::_('JLIB_APPLICATION_ERROR_DELETE_NOT_PERMITTED'));
                }

                return false;
            }

            // Trigger the onContentBeforeDelete event.
            $context = $this->option . '.' . $this->name;
            $result  = $dispatcher->trigger($this->event_before_delete, array($context, $table));

            if (in_array(false, $result, true)) {
                $this->setError($table->getError());
                return false;
            }

            if ($repo_exists) {
                $params = new JRegistry;
                $params->loadString($table->attribs);

                $repo_dir = (int) $params->get('repo_dir');

                $query->clear()
                      ->select('path')
                      ->from('#__pf_repo_dirs')
                      ->where('id = ' . $repo_dir);

                $this->_db->setQuery($query);
                $repo_path = $this->_db->loadResult();
            }

            // Delete the item
            if (!$table->delete($pk)) {
                $this->setError($table->getError());
                return false;
            }

            // Delete the repo directory
            if ($repo_exists) {
                if ($repo_path && $repo_dir) {
                    // Delete repo 4.1
                    $repo = $base_path . '/' . $repo_path;
                    if (JFolder::exists($repo) && $repo != $base_path) JFolder::delete($repo);

                    // Delete repo 4.0
                    $repo = $base_path . '/' . $pk;
                    if (JFolder::exists($repo)) JFolder::delete($repo);

                    // Delete repo 3.0
                    $repo = $base_path . '/project_' . $pk;
                    if (JFolder::exists($repo)) JFolder::delete($repo);
                }
            }

            // Delete the logo
            $this->deleteLogo($pk);

            // Check if the currently active project is being deleted.
            // If so, clear it from the session
            if ($active_id == $pk) $this->setActive(array('id' => 0));

            // Trigger the onContentAfterDelete event.
            $dispatcher->trigger($this->event_after_delete, array($context, $table));
        }

        // Clear the component's cache
        $this->cleanCache();

        return true;
    }


    /**
     * Custom clean the cache
     *
     */
    protected function cleanCache($group = null, $client_id = 0)
    {
        parent::cleanCache('com_pfprojects');
    }


    /**
     * Method to create a project repository
     *
     * @param     object     $item    The project JTable object
     *
     * @return    boolean             True on success, otherwise False
     */
    protected function createRepository($item)
    {
        if (empty($item)) return false;

        // Get Repo dir model
        $suffix    = (JFactory::getApplication()->isSite() ? 'Form' : '');
        $config    = array('ignore_request' => true);
        $dir_class = $this->getInstance('Directory' . $suffix, 'PFrepoModel', $config);

        // Indicate to the model that we possibly want to create a new repo
        $dir_class->setState('create_repo', true);

        // Load attributes into JRegistry
        $registry = new JRegistry;
        $registry->loadString($item->attribs);

        // Get the repo dir pk
        $repo_dir = (int) $registry->get('repo_dir');

        // Check if the dir exists
        if ($repo_dir) {
            $record = $dir_class->getItem($repo_dir);

            if ($record === false || empty($record->id)) {
                // Record not found
                $repo_dir = 0;
            }
            else {
                // Record found, update it
                $data = array();
                $data['id']         = $record->id;
                $data['title']      = $item->title;
                $data['project_id'] = $item->id;
                $data['parent_id']  = $record->id;

                if (!$dir_class->save($data)) {
                    $this->setError($dir_class->getError());
                    return false;
                }

                return true;
            }
        }

        // Create repo dir
        $query = $this->_db->getQuery(true);
        $data  = array();

        $data['id']         = 0;
        $data['protected']  = 1;
        $data['title']      = $item->title;
        $data['project_id'] = $item->id;
        $data['created']    = $item->created;
        $data['created_by'] = $item->created_by;
        $data['access']     = $item->access;
        $data['parent_id']  = 1;

        if (!$dir_class->save($data)) {
            $this->setError($dir_class->getError());
            return false;
        }

        $repo_dir = $dir_class->getState($dir_class->getName() . '.id');
        $registry->set('repo_dir', $repo_dir);

        if (!$repo_dir) return false;

        // Update the project attribs
        $query->clear();
        $query->update('#__pf_projects')
              ->set('attribs = ' . $this->_db->quote((string) $registry))
              ->where('id = ' . (int) $item->id);

        $this->_db->setQuery($query);
        $this->_db->execute();

        if ($this->_db->getError()) {
            $this->setError($this->_db->getError());
            return false;
        }

        return true;
    }


    /**
     * Method to change the title & alias.
     * Overloaded from JModelAdmin class
     *
     * @param     string     The title
     * @param     string     The alias
     * @param     integer    The item id
     *
     * @return    array      Contains the modified title and alias
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
     * Method to generate a unique group title.
     *
     * @param     string     $title        The title
     * @param     integer    $parent_id    The parent group id
     * @param     integer    $id           The group id
     *
     * @return    array                    Contains the modified title
     */
    protected function generateNewGroupTitle($title, $parent_id, $id = 0)
    {
        $table = $this->getTable('UserGroup', 'JTable');
        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->select('COUNT(id)')
              ->from($table->getTableName())
              ->where('parent_id = ' . (int) $parent_id);

        if ($id) {
            $query->where('id != ' . intval($id));
        }

        $db->setQuery($query);
        $count = (int) $db->loadResult();

        if ($id > 0 && $count == 0) {
            return $title;
        }
        elseif ($id == 0 && $count == 0) {
            return $title;
        }
        else {
            while ($table->load(array('title' => $title, 'parent_id' => $parent_id)))
            {
                $m = null;

                if (preg_match('#\((\d+)\)$#', $title, $m)) {
                    $title = preg_replace('#\(\d+\)$#', '('.($m[1] + 1).')', $title);
                }
                else {
                    $title .= ' (2)';
                }
            }
        }

        return $title;
    }


    /**
     * Method to create a new user group
     *
     * @param     string     $title        the name of the group
     * @param     integer    $parent_id    The parenting group
     * @param     array      $users        The users to add to the group
     *
     * @return    integer    $id           The id of the new group
     */
    protected function createUserGroup($title, $parent_id, $users = array())
    {
        $dispatcher = JDispatcher::getInstance();

        $table = $this->getTable('Usergroup', 'JTable');

        // Include the content plugins for the on save events.
        JPluginHelper::importPlugin('content');

        // Generate unique title
        $title = $this->generateNewGroupTitle($title, $parent_id);

        $data = array();
        $data['title']     = $title;
        $data['parent_id'] = (int) $parent_id;

        // Allow an exception to be thrown.
        try
        {
            // Bind the data.
            if (!$table->bind($data)) {
                $this->setError($table->getError());
                return false;
            }

            // Check the data.
            if (!$table->check()) {
                $this->setError($table->getError());
                return false;
            }

            // Trigger the onContentBeforeSave event.
            $result = $dispatcher->trigger($this->event_before_save, array('com_users.group', $table, true));

            if (in_array(false, $result, true)) {
                $this->setError($table->getError());
                return false;
            }

            // Store the data.
            if (!$table->store()) {
                $this->setError($table->getError());
                return false;
            }

            // Clean the cache.
            $this->cleanCache();

            // Trigger the onContentAfterSave event.
            $dispatcher->trigger($this->event_after_save, array('com_users.group', $table, true));
        }
        catch (Exception $e)
        {
            $this->setError($e->getMessage());

            return false;
        }

        $pkName = $table->getKeyName();

        if (!isset($table->$pkName)) {
            return false;
        }

        $gid = (int) $table->$pkName;

        // Add users to the group
        $looped = array();

        foreach ($users AS $uid)
        {
            if (in_array($uid, $looped)) continue;

            $looped[] = $uid;

            $obj = new stdClass();
            $obj->user_id  = (int) $uid;
            $obj->group_id = (int) $gid;

            $this->_db->insertObject('#__user_usergroup_map', $obj);
        }

        return false;
    }


    /**
     * Method to rename a user group
     *
     * @param     integer    $id       The group id to rename
     * @param     string     $title    The new group title
     *
     * @return    boolean              True on success.
     */
    protected function renameUserGroup($id, $title)
    {
        $query = $this->_db->getQuery(true);

        $query->select('id, title, parent_id')
              ->from('#__usergroups')
              ->where('id = ' . (int) $id);

        $this->_db->setQuery($query);
        $group = $this->_db->loadObject();

        if (empty($group)) return false;

        if ($title != $group->title) {
            $title = $this->generateNewGroupTitle($title, $group->parent_id, $group->id);

            $query->clear()
                  ->update('#__usergroups')
                  ->set('title = ' . $db->quote($title))
                  ->where('id = ' . (int) $id);

            $this->_db->setQuery($query);
            $this->_db->execute();
        }

        return true;
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

            $user  = JFactory::getUser();
            $asset = 'com_pfprojects.project.' . (int) $record->id;

            return $user->authorise('core.delete', $asset);
        }

        return parent::canDelete($record);
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
        if (!empty($record->id)) {
            $user  = JFactory::getUser();
            $asset = 'com_pfprojects.project.' . (int) $record->id;

            return $user->authorise('core.edit.state', $asset);
        }

        return parent::canEditState($record);
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
        $user = JFactory::getUser();

        // Check for existing item.
        if (!empty($record->id)) {
            $asset  = 'com_pfprojects.project.' . (int) $record->id;

            return ($user->authorise('core.edit', $asset) || ($access->get('core.edit.own', $asset) && $record->created_by == $user->id));
        }

        return $user->authorise('core.edit', 'com_pfprojects');
    }


    /**
     * Method to get the data that should be injected in the form.
     *
     * @return    mixed    The data for the form.
     */
    protected function loadFormData()
    {
        // Check the session for previously entered form data.
        $data = JFactory::getApplication()->getUserState('com_pfprojects.edit.' . $this->getName() . '.data', array());

        if (empty($data)) $data = $this->getItem();

        return $data;
    }
}
