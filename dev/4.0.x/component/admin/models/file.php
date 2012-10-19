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
 * Item Model for a file form.
 *
 */
class ProjectforkModelFile extends JModelAdmin
{
    /**
     * The prefix to use with controller messages.
     *
     * @var    string
     */
    protected $text_prefix = 'COM_PROJECTFORK_FILE';


    /**
     * Constructor.
     *
     * @param    array          $config    An optional associative array of configuration settings.
     *
     * @see      jcontroller
     */
    public function __construct($config = array())
    {
       // Register dependencies
       JLoader::register('ProjectforkHelperRepository', JPATH_ADMINISTRATOR . '/components/com_projectfork/helpers/repository.php');

       jimport('joomla.filesystem.path');
       jimport('joomla.filesystem.folder');
       jimport('joomla.filesystem.file');

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
    public function getTable($type = 'File', $prefix = 'PFTable', $config = array())
    {
        return JTable::getInstance($type, $prefix, $config);
    }


    /**
     * Method to perform batch operations on an item or a set of items.
     *
     * @param     array      $commands    An array of commands to perform.
     * @param     array      $pks         An array of item ids.
     *
     * @return    boolean                 Returns true on success, false on failure.
     */
    public function batch($commands, $pks)
    {
        // Sanitize user ids.
        $pks = array_unique($pks);
        JArrayHelper::toInteger($pks);

        // Remove any values of zero.
        if (array_search(0, $pks, true)) {
            unset($pks[array_search(0, $pks, true)]);
        }

        if (empty($pks)) {
            $this->setError(JText::_('JGLOBAL_NO_ITEM_SELECTED'));
            return false;
        }

        $done = false;

        if (!empty($commands['parent_id']))
        {
            $cmd = JArrayHelper::getValue($commands, 'move_copy', 'c');

            if ($cmd == 'c') {
                $result = $this->batchCopy($commands['parent_id'], $pks);

                if (is_array($result)) {
                    $pks = $result;
                }
                else {
                    return false;
                }
            }
            elseif ($cmd == 'm' && !$this->batchMove($commands['parent_id'], $pks)) {
                return false;
            }
            $done = true;
        }

        if (!$done) {
            $this->setError(JText::_('JLIB_APPLICATION_ERROR_INSUFFICIENT_BATCH_INFORMATION'));
            return false;
        }

        // Clear the cache
        $this->cleanCache();

        return true;
    }


    /**
     * Method to delete one or more records.
     *
     * @param     array  &    $pks    An array of record primary keys.
     *
     * @return    boolean             True if successful, false if an error occurs.
     */
    public function delete(&$pks)
    {
        // Initialise variables.
        $dispatcher = JDispatcher::getInstance();
        $pks        = (array) $pks;
        $table      = $this->getTable();

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

                    // Delete the physical file
                    $path = ProjectforkHelperRepository::getBasePath($table->project_id);
                    $file = $table->file_name;

                    if (JFile::exists($path . '/' . $file)) {
                        JFile::delete($path . '/' . $file);
                        // Dont care if the deletion failed and remove the db record anyway.
                    }

                    if (!$table->delete($pk)) {
                        $this->setError($table->getError());
                        return false;
                    }

                    $tables = array('labelref');
                    $field  = array('item_type' => 'file', 'item_id' => $pk);

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
     * Batch move items to a new directory
     *
     * @param     integer    $value    The new parent ID.
     * @param     array      $pks      An array of row IDs.
     *
     * @return    boolean              True if successful, false otherwise and internal error is set.
     */
    protected function batchMove($value, $pks)
    {
        $dest = (int) $value;

        $table = $this->getTable('Directory');

        // Check that the destination exists
        if ($dest) {
            if (!$table->load($dest)) {
                if ($error = $dest->getError()) {
                    // Fatal error
                    $this->setError($error);
                    return false;
                }
                else {
                    $this->setError(JText::_('COM_PROJECTFORK_ERROR_BATCH_MOVE_DIRECTORY_NOT_FOUND'));
                    return false;
                }
            }
        }

        if (empty($dest)) {
            $this->setError(JText::_('COM_PROJECTFORK_ERROR_BATCH_MOVE_DIRECTORY_NOT_FOUND'));
            return false;
        }

        // Check that user has create and edit permission
        $access = ProjectforkHelperAccess::getActions('directory', $dest);

        if (!$access->get('file.create')) {
            $this->setError(JText::_('COM_PROJECTFORK_ERROR_BATCH_CANNOT_CREATE_FILE'));
            return false;
        }

        $table = $this->getTable();

        // Parent exists so we let's proceed
        foreach ($pks as $pk)
        {
            // Check that the row actually exists
            if (!$table->load($pk)) {
                if ($error = $table->getError()) {
                    // Fatal error
                    $this->setError($error);
                    return false;
                }
                else {
                    // Not fatal error
                    $this->setError(JText::sprintf('JGLOBAL_BATCH_MOVE_ROW_NOT_FOUND', $pk));
                    continue;
                }
            }

            // Set the new location directory
            $table->dir_id = (int) $dest;

            // Generate new title
            list($title, $alias) = $this->generateNewTitle($table->dir_id, $table->title, $table->alias, $table->id);

            $table->title = $title;
            $table->alias = $alias;

            // Store the row.
            if (!$table->store()) {
                $this->setError($table->getError());
                return false;
            }
        }

        // Clean the cache
        $this->cleanCache();

        return true;
    }


    /**
     * Batch copy items to a new directory.
     *
     * @param     integer    $value    The destination dir.
     * @param     array      $pks      An array of row IDs.
     *
     * @return    mixed                An array of new IDs on success, boolean false on failure.
     */
    protected function batchCopy($value, $pks)
    {
        $dest = (int) $value;
        $rbid = null;

        $table = $this->getTable('Directory');
        $db    = $this->getDbo();
        $user  = JFactory::getUser();

        $i = 0;

        // Check that the parent exists
        if ($dest) {
            if (!$table->load($dest)) {
                if ($error = $table->getError()) {
                    $this->setError($error);
                    return false;
                }
                else {
                    $this->setError(JText::_('COM_PROJECTFORK_ERROR_BATCH_COPY_DIRECTORY_NOT_FOUND'));
                    return false;
                }
            }

            // Check that user has create permission for parent directory
            $access = ProjectforkHelperAccess::getActions('directory', $dest);

            if (!$access->get('file.create')) {
                // Error since user cannot create in parent dir
                $this->setError(JText::_('COM_PROJECTFORK_ERROR_BATCH_CANNOT_CREATE_FILE'));
                return false;
            }
        }

        $table  = $this->getTable();
        $newIds = array();

        // Parent exists so we let's proceed
        foreach ($pks as $pk)
        {
            // Check that the row actually exists
            if (!$table->load($pk)) {
                if ($error = $table->getError()) {
                    // Fatal error
                    $this->setError($error);
                    return false;
                }
                else {
                    // Not fatal error
                    $this->setError(JText::sprintf('JGLOBAL_BATCH_MOVE_ROW_NOT_FOUND', $pk));
                    continue;
                }
            }

            // Copy the physical file
            $basepath = ProjectforkHelperRepository::getBasePath($table->project_id);

            if (JFile::exists($basepath . '/' . $table->file_name)) {
                $filepath = $basepath . '/' . $table->file_name;
                $new_name = $this->generateNewFileName($basepath, $table->file_name);

                if (!JFile::copy($basepath . '/' . $table->file_name, $basepath . '/' . $new_name)) {
                    continue;
                }
                else {
                    $table->file_name = $new_name;
                }
            }

            // Reset the id because we are making a copy.
            $table->id = 0;

            // Set the new location directory
            $table->dir_id = (int) $dest;

            // Alter the title & alias
            list($title, $alias) = $this->generateNewTitle($table->dir_id, $table->title, $table->alias);
            $table->title = $title;
            $table->alias = $alias;

            // Store the row.
            if (!$table->store()) {
                $this->setError($table->getError());
                return false;
            }

            // Get the new item ID
            $newId = $table->get('id');

            // Add the new ID to the array
            $newIds[] = $newId;
        }

        return $newIds;
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

            // Get the labels
            $labels = $this->getInstance('Labels', 'ProjectforkModel');
            $item->labels = $labels->getConnections('file', $item->id);
        }

        return $item;
    }


    /**
     * Method to save an item
     *
     * @param     array      $data    The item data
     *
     * @return    boolean             True on success, False on error
     */
    public function save($data)
    {
        // Initialise variables;
        $table  = $this->getTable();
        $pk     = (!empty($data['id'])) ? $data['id'] : (int) $this->getState($this->getName() . '.id');
        $date   = JFactory::getDate();
        $is_new = true;

        // Load the row if saving an existing item.
        if ($pk > 0) {
            if ($table->load($pk)) {
                $is_new = false;
            }
            else {
                $pk = 0;
            }
        }

        // Delete the old file if a new one is uploaded
        if (!$is_new && isset($data['file']['name'])) {
            $this->deleteFile($table->file_name, $table->project_id);
        }

        // Use the file name as title if empty
        if ($data['title'] == '' && isset($data['file']['name'])) {
            $data['title'] = $data['file']['name'];
        }

        // Get the other file properties
        if (isset($data['file']['name'])) {
            $data['file_name'] = $data['file']['name'];
        }

        if (isset($data['file']['extension'])) {
            $data['file_extension'] = $data['file']['extension'];
        }

        if (isset($data['file']['size'])) {
            $data['file_size'] = ($data['file']['size'] > 0 ? round($data['file']['size'] / 1024) : 0);
        }

        // Make sure the title and alias are always unique
        $data['alias'] = '';
        list($title, $alias) = $this->generateNewTitle($data['dir_id'], $data['title'], $data['alias'], $pk);

        $data['title'] = $title;
        $data['alias'] = $alias;

        // Handle permissions and access level
        if (isset($data['rules'])) {
            $access = ProjectforkHelperAccess::getViewLevelFromRules($data['rules'], intval($data['access']));

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

        // Store the data.
        if (!$table->store()) {
            $this->setError($table->getError());
            return false;
        }

        $this->setState($this->getName() . '.id', $table->id);

        $updated = $this->getTable();
        if ($updated->load($table->id) === false) return false;

        // Store the labels
        if (isset($data['labels'])) {
            $labels = $this->getInstance('Labels', 'ProjectforkModel');

            if ((int) $labels->getState('item.project') == 0) {
                $labels->setState('item.project', $updated->project_id);
            }

            $labels->setState('item.type', 'file');
            $labels->setState('item.id', $table->id);

            if (!$labels->saveRefs($data['labels'])) {
                return false;
            }
        }

        // Clear the cache
        $this->cleanCache();

        return true;
    }


    /**
     * Method for uploading a file
     *
     * @param     array      $file       The file information
     * @param     integer    $project    The project id
     * @param     boolean    $stream     If set to true, use data stream
     *
     * @return    mixed                  Array with file info on success, otherwise False
     */
    public function upload($file = NULL, $project = 0, $stream = false)
    {
        $uploadpath = ProjectforkHelperRepository::getBasePath($project);

        if (!is_array($file)) {
            $this->setError(JText::_('COM_PROJECTFORK_WARNING_NO_FILE_SELECTED'));
            return false;
        }

        if (!isset($file['tmp_name'])) {
            $this->setError(JText::_('COM_PROJECTFORK_WARNING_NO_FILE_SELECTED'));
            return false;
        }

        // Try to create the upload path destination
        if (!JFolder::exists($uploadpath)) {
            if (!JFolder::create($uploadpath)) {
                return false;
            }
        }

        $errnum = (int) $file['error'];

        if ($errnum > 0) {
            $errmsg = ProjectforkHelperRepository::getFileErrorMsg($errnum, $file['name'], $file['size']);
            $this->setError($errmsg);

            return false;
        }

        $name = $this->generateNewFileName($uploadpath, $file['name']);
        $ext  = JFile::getExt($name);

        if (JFile::upload($file['tmp_name'], $uploadpath . '/' . $name, $stream) === true) {
            return array('name' => $name, 'size' => $file['size'], 'extension' => $ext);
        }

        return false;
    }


    /**
     * Method to delete a file
     *
     * @param     string     $name       The file name
     * @param     integer    $project    The project id to which the file belongs to
     *
     * @return    boolean                True on success, otherwise False
     */
    public function deleteFile($name, $project = 0)
    {
        $uploadpath = ProjectforkHelperRepository::getBasePath($project);

        if (JFile::exists($uploadpath . '/' . $name)) {
            if (JFile::delete($uploadpath . '/' . $name) !== true) {
                return false;
            }
            else {
                return true;
            }
        }
        else {
            return false;
        }
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
        $form = $this->loadForm('com_projectfork.file', 'file', array('control' => 'jform', 'load_data' => $loadData));
        if (empty($form)) return false;

        // Check if a project and directory is already selected. If not, get them from the current state
        $project_id = (int) $form->getValue('project_id');
        $dir_id     = (int) $form->getValue('dir_id');

        if (!$project_id) {
            $form->setValue('project_id', null, $this->getState($this->getName() . '.project'));
        }
        if (!$dir_id) {
            $form->setValue('dir_id', null, $this->getState($this->getName() . '.dir_id'));
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
     * Custom clean the cache of com_projectfork and projectfork modules
     *
     */
    protected function cleanCache()
    {
        parent::cleanCache('com_projectfork');
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
            $access = ProjectforkHelperAccess::getActions('file', $record->id);
            return $access->get('file.delete');
        }
        else {
            $access = ProjectforkHelperAccess::getActions();
            return $access->get('file.delete');
        }
    }


    /**
     * Method to change the title.
     *
     * @param     integer    $dir_id    The parent directory
     * @param     string     $title     The directory title
     * @param     string     $alias     The current alias
     * @param     integer    $id        The note id
     *
     * @return    string                Contains the new title
     */
    protected function generateNewTitle($dir_id, $title, $alias = '', $id = 0)
    {
        // Alter the title & alias
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
              ->where('alias = ' . $db->quote($alias))
              ->where('dir_id = ' . $db->quote($dir_id));

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
            while ($table->load(array('alias' => $alias, 'dir_id' => $dir_id)))
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
     * Method to change the file name.
     *
     * @param     string    $dest    The target destination folder
     * @param     string    $name    The file name
     *
     * @return    string             Contains the new name
     */
    protected function generateNewFileName($dest, $name)
    {
        $name = JFile::makeSafe($name);
        $ext  = JFile::getExt($name);
        $name = substr($name, 0 , (strlen($name) - (strlen($ext) + 1)));

        if ($name == '') {
            $name = JFile::makeSafe(JFactory::getDate()->format('Y-m-d-H-i-s'));
        }

        $exists = true;
        $files  = JFolder::files($dest);

        if (!is_array($files)) {
            return $name . '.' . $ext;
        }

        if (!count($files)) {
            return $name . '.' . $ext;
        }

        if (!in_array($name . '.' . $ext, $files)) {
            return $name . '.' . $ext;
        }

        while ($exists == true)
        {
            $m = null;

            if (preg_match('#-(\d+)$#', $name, $m)) {
                $name   = preg_replace('#-(\d+)$#', '-'.($m[1] + 1).'', $name);
                $exists = JFile::exists($dest . '/' . $name . '.' . $ext);
            }
            else {
                $name  .= '-2';
                $exists = JFile::exists($dest . '/' . $name . '.' . $ext);
            }
        }

        return $name . '.' . $ext;
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
            $access = ProjectforkHelperAccess::getActions('file', $record->id);
            return $access->get('file.edit.state');
        }
        else {
            $access = ProjectforkHelperAccess::getActions();
            return $access->get('note.edit');
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
            $access = ProjectforkHelperAccess::getActions('file', $record->id);
            $user   = JFactory::getUser();

            return ($access->get('file.edit') || ($access->get('file.edit.own') && $record->created_by == $user->id));
        }
        else {
            $access = ProjectforkHelperAccess::getActions();
            return $access->get('file.edit');
        }
    }


    /**
     * Method to auto-populate the model state.
     * Note: Calling getState in this method will result in recursion.
     *
     * @return    void
     */
    protected function populateState()
    {
        // Initialise variables.
        $app   = JFactory::getApplication();
        $table = $this->getTable();
        $key   = $table->getKeyName();

        // Get the pk of the record from the request.
        $pk = JRequest::getInt($key);
        $this->setState($this->getName() . '.id', $pk);

        if ($pk) {
            $table = $this->getTable();

            if ($table->load($pk)) {
                $project = (int) $table->project_id;
                $this->setState($this->getName() . '.project', $project);
                ProjectforkHelper::setActiveProject($project);

                $dir_id = (int) $table->dir_id;
                $this->setState($this->getName() . '.dir_id', $dir_id);
            }
        }
        else {
            $dir_id = JRequest::getUInt('filter_parent_id', 0);
            $this->setState($this->getName() . '.dir_id', $dir_id);

            $project = ProjectforkHelper::getActiveProjectId('filter_project');

            if ($project) {
                $this->setState($this->getName() . '.project', $project);
                ProjectforkHelper::setActiveProject($project);
            }
            elseif ($parent_id) {
                $table = $this->getTable('Directory');

                if ($table->load($parent_id)) {
                    $project = (int) $table->project_id;

                    $this->setState($this->getName() . '.project', $project);
                    ProjectforkHelper::setActiveProject($project);
                }
            }
        }

        // Load the parameters.
        $value = JComponentHelper::getParams($this->option);
        $this->setState('params', $value);
    }
}
