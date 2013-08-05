<?php
/**
 * @package      pkg_projectfork
 * @subpackage   com_pfrepo
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2013 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


jimport('joomla.application.component.modeladmin');
jimport('joomla.filesystem.path');
jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');


/**
 * Item Model for a file form.
 *
 */
class PFrepoModelFile extends JModelAdmin
{
    /**
     * The prefix to use with controller messages.
     *
     * @var    string
     */
    protected $text_prefix = 'COM_PROJECTFORK_FILE';


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
     * @param     array      $contexts    An array of item contexts.
     *
     * @return    boolean                 Returns true on success, false on failure.
     */
    public function batch($commands, $pks, $contexts = array())
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
     * @param     array  &    $pks              An array of record primary keys.
     * @param     bool        $ignore_access    If true, ignore permission and just delete
     *
     * @return    boolean                       True if successful, false if an error occurs.
     */
    public function delete(&$pks, $ignore_access = false)
    {
        // Initialise variables.
        $dispatcher = JDispatcher::getInstance();
        $pks        = (array) $pks;
        $table      = $this->getTable();
        $query      = $this->_db->getQuery(true);

        // Include the content plugins for the on delete events.
        JPluginHelper::importPlugin('content');

        // Iterate the items to delete each one.
        foreach ($pks as $i => $pk)
        {
            if ($table->load($pk)) {
                if ($ignore_access || $this->canDelete($table)) {
                    $context = $this->option . '.' . $this->name;

                    // Trigger the onContentBeforeDelete event.
                    $result = $dispatcher->trigger($this->event_before_delete, array($context, $table));

                    if (in_array(false, $result, true)) {
                        $this->setError($table->getError());
                        return false;
                    }

                    // Delete from database 1st
                    if (!$table->delete($pk)) {
                        $this->setError($table->getError());
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
     * @param     integer    $value       The new parent ID.
     * @param     array      $pks         An array of row IDs.
     * @param     array      $contexts    An array of item contexts
     *
     * @return    boolean                 True if successful, false otherwise and internal error is set.
     */
    protected function batchMove($value, $pks, $contexts = array())
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
        $access = PFrepoHelper::getActions('directory', $dest);

        if (!$access->get('core.create')) {
            $this->setError(JText::_('COM_PROJECTFORK_ERROR_BATCH_CANNOT_CREATE_FILE'));
            return false;
        }

        $dir_path = $table->path;
        $table    = $this->getTable();

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

            // Skip if the the destination is the same as the current dir
            if ($dest == $table->dir_id) continue;

            // Move the physical file
            $path = PFrepoHelper::getFilePath($table->file_name, $table->dir_id);

            if (empty($path)) {
                $this->setError(JText::sprintf('JGLOBAL_BATCH_MOVE_ROW_NOT_FOUND', $pk));
                continue;
            }

            $base = PFrepoHelper::getBasePath();
            $from = $path . '/' . $table->file_name;
            $to   = $base . '/' . $dir_path;
            $name = $this->generateNewFileName($to, $table->file_name);

            if (!JFolder::exists($to)) {
                if (JFolder::create($to) !== true) continue;
            }

            if (!JFile::move($from, $to . '/' . $name)) {
                continue;
            }
            else {
                $table->file_name = $name;
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
     * @param     integer    $value       The destination dir.
     * @param     array      $pks         An array of row IDs.
     * @param     array      $contexts    An array of item contexts.
     *
     * @return    mixed                   An array of new IDs on success, boolean false on failure.
     */
    protected function batchCopy($value, $pks, $contexts = array())
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
            $access = PFrepoHelper::getActions('directory', $dest);

            if (!$access->get('core.create')) {
                // Error since user cannot create in parent dir
                $this->setError(JText::_('COM_PROJECTFORK_ERROR_BATCH_CANNOT_CREATE_FILE'));
                return false;
            }
        }

        $dir_path = $table->path;
        $table    = $this->getTable();
        $newIds   = array();

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
            $path = PFrepoHelper::getFilePath($table->file_name, $table->dir_id);

            if (empty($path)) {
                $this->setError(JText::sprintf('JGLOBAL_BATCH_MOVE_ROW_NOT_FOUND', $pk));
                continue;
            }

            $base = PFrepoHelper::getBasePath();
            $from = $path . '/' . $table->file_name;
            $to   = $base . '/' . $dir_path;
            $name = $this->generateNewFileName($to, $table->file_name);

            if (!JFolder::exists($to)) {
                if (JFolder::create($to) !== true) continue;
            }

            if (!JFile::copy($from, $to . '/' . $name)) {
                continue;
            }
            else {
                $table->file_name = $name;
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
        $item = parent::getItem($pk);

        if ($item == false) return false;

        if (property_exists($item, 'attribs')) {
            // Convert the params field to an array.
            $registry = new JRegistry();

            $registry->loadString($item->attribs);

            $item->params  = $registry;
            $item->attribs = $registry->toArray();
        }

        if ($item->id > 0) {
            // Existing record
            $labels = $this->getInstance('Labels', 'PFModel');

            $item->labels = $labels->getConnections('com_pfrepo.file', $item->id);
            $item->revision_count = 0;
        }
        else {
            // New record
            $item->labels   = array();
            $item->revision_count = $this->getRevisionCount($pk);
        }

        return $item;
    }


    /**
     * Counts the revisions of the given file
     *
     * @param    array      $pk       The file primary key
     *
     * @retun    integer    $count    The revision count
     */
    public function getRevisionCount($pk = null)
    {
        $pk    = (!empty($pk)) ? $pk : (int) $this->getState($this->getName() . '.id');
        $query = $this->_db->getQuery(true);
        $count = 0;

        if (empty($pk)) return $count;

        // Count revs
        $query->select('COUNT(*)')
              ->from('#__pf_repo_file_revs')
              ->where('parent_id = ' . (int) $pk);

        $query->group('parent_id');
        $this->_db->setQuery($query);

        try {
            $count += (int) $this->_db->loadResult();
        }
        catch (RuntimeException $e) {
            $this->setError($e->getMessage());
            return false;
        }

        return $count;
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
        $dispatcher = JDispatcher::getInstance();

        $table  = $this->getTable();
        $pk     = (!empty($data['id'])) ? $data['id'] : (int) $this->getState($this->getName() . '.id');
        $date   = JFactory::getDate();
        $is_new = true;

        // Include the content plugins for the on save events.
        JPluginHelper::importPlugin('content');

        // Load the row if saving an existing item.
        if ($pk > 0) {
            if ($table->load($pk)) {
                $is_new = false;
            }
            else {
                $pk = 0;
            }
        }

        // Save revision if not new
        if (!$is_new && isset($data['file']['name'])) {
            // $this->deleteFile($table->file_name, $table->dir_id);
            $head_data = $table->getProperties(true);
            $config    = array('ignore_request' => true);
            $rev_model = $this->getInstance('FileRevision', 'PFrepoModel', $config);

            $head_data['parent_id'] = $head_data['id'];
            $head_data['id']        = null;

            if (!$rev_model->save($head_data)) {
                $this->setError($rev_model->getError());
                return false;
            }

            // Change the title to the file name
            if (strrpos($data['title'], $data['file']['extension']) !== false) {
                $data['title'] = '';
            }
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
            $access = PFAccessHelper::getViewLevelFromRules($data['rules'], intval($data['access']));

            if ($access) {
                $data['access'] = $access;
            }
        }
        else {
            if ($is_new) {
                // Let the table class find the correct access level
                $data['access'] = 0;
            }
            else {
                // Keep the existing access in the table
                if (isset($data['access'])) {
                    unset($data['access']);
                }
            }
        }

        $new_dir = (isset($data['dir_id']) ? (int) $data['dir_id'] : 0);

        // Move file to new location?
        if ($new_dir > 0 && !$is_new && $new_dir != $table->dir_id) {
            $pks      = array($table->id);
            $contexts = array();

            if(!$this->batchMove($new_dir, $pks, $contexts)) {
                return false;
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

        // Trigger the onContentAfterSave event.
        $dispatcher->trigger($this->event_after_save, array($this->option . '.' . $this->name, &$table, $is_new));

        $this->setState($this->getName() . '.id', $table->id);

        $updated = $this->getTable();
        if ($updated->load($table->id) === false) return false;

        // Store the labels
        if (isset($data['labels'])) {
            $labels = $this->getInstance('Labels', 'PFModel');

            if ((int) $labels->getState('item.project') == 0) {
                $labels->setState('item.project', $updated->project_id);
            }

            $labels->setState('item.type', 'com_pfrepo.file');
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
     * @param     array      $file         The file information
     * @param     integer    $dir          The directory id
     * @param     boolean    $stream       If set to true, use data stream
     * @param     integer    $parent_id    If set, will try to move the original file to the revs folder
     *
     * @return    mixed                    Array with file info on success, otherwise False
     */
    public function upload($file = NULL, $dir = 0, $stream = false, $parent_id = 0)
    {
        // Dont allow upload to root dir
        if ((int) $dir <= 1) {
            $this->setError(JText::_('COM_PROJECTFORK_WARNING_SELECT_DIRECTORY'));
            return false;
        }

        // Check allowed file extension
        $allowed = PFrepoHelper::getAllowedFileExtensions();
        $config  = JComponentHelper::getParams('com_pfrepo');
        $user    = JFactory::getUser();

        $filter_admin = $config->get('filter_ext_admin');
        $is_admin     = $user->authorise('core.admin');

        if ($is_admin && !$filter_admin) $allowed = array();

        if (count($allowed)) {
            $ext = strtolower(JFile::getExt($file['name']));

            if (!in_array($ext, $allowed)) {
                $this->setError(JText::_('COM_PROJECTFORK_WARNING_INVALID_FILE_EXT'));
                return false;
            }
        }

        $query = $this->_db->getQuery(true);

        $query->select('project_id, path')
              ->from('#__pf_repo_dirs')
              ->where('id = ' . (int) $dir);

        $this->_db->setQuery($query);
        $dir = $this->_db->loadObject();

        if (empty($dir)) {
            $this->setError(JText::_('COM_PROJECTFORK_WARNING_SELECT_DIRECTORY'));
            return false;
        }

        $project    = $dir->project_id;
        $uploadpath = PFrepoHelper::getBasePath() . '/' . $dir->path;

        if (!is_array($file) || !isset($file['tmp_name'])) {
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
            $errmsg = PFrepoHelper::getFileErrorMsg($errnum, $file['name'], $file['size']);
            $this->setError($errmsg);

            return false;
        }

        // If we have a parent id, move it to the revisions folder first
        if ($parent_id) {
            $query->clear()
                  ->select('project_id, dir_id, file_name')
                  ->from('#__pf_repo_files')
                  ->where('id = ' . (int) $parent_id);

            $this->_db->setQuery($query);
            $head = $this->_db->loadObject();

            if (empty($head)) {
                $this->setError(JText::_('COM_PROJECTFORK_WARNING_FILE_HEAD_NOT_FOUND'));
                return false;
            }

            // Prepare file paths
            $head_dest = PFrepoHelper::getBasePath($head->project_id) . '/_revs/file_' . (int) $parent_id;
            $head_path = PFrepoHelper::getFilePath($head->file_name, $head->dir_id);

            if (empty($head_path)) {
                $this->setError(JText::_('COM_PROJECTFORK_WARNING_FILE_HEAD_FILE_NOT_FOUND'));
                return false;
            }

            if (!JFolder::exists($head_dest)) {
                if (JFolder::create($head_dest) !== true) {
                    return false;
                }
            }

            $head_path .= '/' . $head->file_name;

            $head_name = $this->generateNewFileName($head_dest, $head->file_name);
            $head_dest .= '/' . $head_name;

            // Move the file
            $move = JFile::move($head_path, $head_dest);

            if ($move !== true) {
                if (!is_bool($move)) {
                    $this->setError($move);
                }
                return false;
            }
        }

        $name = $this->generateNewFileName($uploadpath, $file['name']);
        $ext  = JFile::getExt($name);

        if ($stream) {
            // Check file size
            $flimit = PFrepoHelper::getMaxUploadSize();
            $plimit = PFrepoHelper::getMaxPostSize();
            $size  = (isset($_SERVER["CONTENT_LENGTH"]) ? (int) $_SERVER["CONTENT_LENGTH"] : 0);

            if ($flimit < $size) {
                $msg = JText::sprintf('COM_PROJECTFORK_WARNING_FILE_UPLOAD_ERROR_1', $name, $flimit);
                $this->setError($msg);

                if ($parent_id) JFile::move($head_dest, $head_path);

                return false;
            }
            elseif ($plimit < $size) {
                $msg = JText::sprintf('COM_PROJECTFORK_WARNING_FILE_UPLOAD_ERROR_9', $name, $plimit);
                $this->setError($msg);

                if ($parent_id) JFile::move($head_dest, $head_path);

                return false;
            }

            $fp   = fopen("php://input", "r");
            $temp = tmpfile();

            if ($fp === false) {
                $this->setError(JText::_('COM_PROJECTFORK_WARNING_FILE_STREAM_ERROR_1'));
                if ($parent_id) JFile::move($head_dest, $head_path);
                return false;
            }

            if ($temp === false) {
                $this->setError(JText::_('COM_PROJECTFORK_WARNING_FILE_STREAM_ERROR_2'));
                if ($parent_id) JFile::move($head_dest, $head_path);
                return false;
            }

            $check = stream_copy_to_stream($fp, $temp);
            fclose($fp);

            if ($check != $size || empty($size)) {
                $this->setError(JText::_('COM_PROJECTFORK_WARNING_FILE_STREAM_ERROR_3'));
                if ($parent_id) JFile::move($head_dest, $head_path);
                return false;
            }

            $dest = fopen($uploadpath . '/' . $name, "w");

            if ($dest === false) {
                $this->setError(JText::_('COM_PROJECTFORK_WARNING_FILE_STREAM_ERROR_4'));
                if ($parent_id) JFile::move($head_dest, $head_path);
                return false;
            }

            fseek($temp, 0, SEEK_SET);
            $check = stream_copy_to_stream($temp, $dest);
            fclose($dest);

            if ($check != $size) {
                $this->setError(JText::_('COM_PROJECTFORK_WARNING_FILE_STREAM_ERROR_5'));
                if ($parent_id) JFile::move($head_dest, $head_path);
                return false;
            }

            $file['size'] = $size;

            if ($parent_id) {
                // Rename the file name in the db
                if ($head_name != $head->file_name) {
                    $query->clear()
                          ->update('#__pf_repo_files')
                          ->set('file_name = ' . $this->_db->quote($head_name))
                          ->where('id = ' . $parent_id);

                    $this->_db->setQuery($query);
                    $this->_db->execute();
                }
            }

            return array('name' => $name, 'size' => $file['size'], 'extension' => $ext);
        }
        else {
            if (JFile::upload($file['tmp_name'], $uploadpath . '/' . $name) === true) {
                if ($parent_id) {
                    // Rename the file name in the db
                    if ($head_name != $head->file_name) {
                        $query->clear()
                              ->update('#__pf_repo_files')
                              ->set('file_name = ' . $this->_db->quote($head_name))
                              ->where('id = ' . $parent_id);

                        $this->_db->setQuery($query);
                        $this->_db->execute();
                    }
                }

                return array('name' => $name, 'size' => $file['size'], 'extension' => $ext);
            }
        }

        if ($parent_id) JFile::move($head_dest, $head_path);

        return false;
    }


    /**
     * Method to delete a file
     *
     * @param     string     $name    The file name
     * @param     integer    $dir     The dir id to which the file belongs to
     *
     * @return    boolean             True on success, otherwise False
     */
    public function deleteFile($name, $dir = 0)
    {
        $path = PFrepoHelper::getFilePath($name, $dir);

        if (empty($path)) return false;

        if (JFile::delete($path . '/' . $name) !== true) {
            return false;
        }

        return true;
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
        $form = $this->loadForm('com_pfrepo.file', 'file', array('control' => 'jform', 'load_data' => $loadData));
        if (empty($form)) return false;

        $jinput = JFactory::getApplication()->input;
        $user   = JFactory::getUser();
        $id     = (int) $jinput->get('id', 0);

        // Disable these fields if not an admin
        if (!$user->authorise('core.admin', 'com_pfrepo')) {
            $form->setFieldAttribute('access', 'disabled', 'true');
            $form->setFieldAttribute('access', 'filter', 'unset');

            $form->setFieldAttribute('rules', 'disabled', 'true');
            $form->setFieldAttribute('rules', 'filter', 'unset');
        }

        // Disable these fields when updating
        if ($id) {
            $form->setFieldAttribute('project_id', 'disabled', 'true');
            $form->setFieldAttribute('project_id', 'filter', 'unset');
            $form->setFieldAttribute('project_id', 'required', 'false');

            // We still need to inject the project id when reloading the form
            if (!isset($data['project_id'])) {
                $db    = JFactory::getDbo();
                $query = $db->getQuery(true);

                $query->select('project_id')
                      ->from('#__pf_repo_files')
                      ->where('id = ' . $db->quote($id));

                $db->setQuery($query);
                $form->setValue('project_id', null, (int) $db->loadResult());
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
        $data = JFactory::getApplication()->getUserState('com_pfrepo.edit.' . $this->getName() . '.data', array());

        if (empty($data)) {
            $data = $this->getItem();

            // Set default values
            if ($this->getState($this->getName() . '.id') == 0) {
                $active_id = PFApplicationHelper::getActiveProjectId();

                $data->set('project_id', $active_id);
                $data->set('dir_id', $this->getState($this->getName() . '.dir_id'));
            }
        }

        return $data;
    }


    /**
     * Custom clean the cache of com_projectfork and projectfork modules
     *
     */
    protected function cleanCache($group = 'com_pfrepo', $client_id = 0)
    {
        parent::cleanCache($group, $client_id);
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
            $user  = JFactory::getUser();
            $asset = 'com_pfrepo.file.' . (int) $record->id;

            return $user->authorise('core.delete', $asset);
        }

        return parent::canDelete($record);
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
        $user = JFactory::getUser();

        // Check for existing item.
        if (!empty($record->id)) {
            return $user->authorise('core.edit.state', 'com_pfrepo.file.' . (int) $record->id);
        }
        elseif (!empty($record->dir_id)) {
            // New item, so check against the directory.
            return $user->authorise('core.edit.state', 'com_pfrepo.directory.' . (int) $record->dir_id);
        }
        else {
            // Default to component settings.
            return parent::canEditState('com_pfrepo');
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
        $user = JFactory::getUser();

        // Check for existing item.
        if (!empty($record->id)) {
            $asset = 'com_pfrepo.file.' . (int) $record->id;

            return ($user->authorise('core.edit', $asset) || ($access->get('core.edit.own', $asset) && $record->created_by == $user->id));
        }

        return $user->authorise('core.edit', 'com_pfrepo');
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
                PFApplicationHelper::setActiveProject($project);

                $dir_id = (int) $table->dir_id;
                $this->setState($this->getName() . '.dir_id', $dir_id);
            }
        }
        else {
            $dir_id = JRequest::getUInt('filter_parent_id', 0);
            $this->setState($this->getName() . '.dir_id', $dir_id);

            $project = PFApplicationHelper::getActiveProjectId('filter_project');

            if ($project) {
                $this->setState($this->getName() . '.project', $project);
                PFApplicationHelper::setActiveProject($project);
            }
            elseif ($parent_id) {
                $table = $this->getTable('Directory');

                if ($table->load($parent_id)) {
                    $project = (int) $table->project_id;

                    $this->setState($this->getName() . '.project', $project);
                    PFApplicationHelper::setActiveProject($project);
                }
            }
        }

        // Load the parameters.
        $value = JComponentHelper::getParams($this->option);
        $this->setState('params', $value);
    }
}
