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
 * Item Model for a Directory form.
 *
 */
class ProjectforkModelDirectory extends JModelAdmin
{
    /**
     * The prefix to use with controller messages.
     *
     * @var    string
     */
    protected $text_prefix = 'COM_PROJECTFORK_DIRECTORY';


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
        JLoader::register('ProjectforkHelperAccess', JPATH_ADMINISTRATOR . '/components/com_projectfork/helpers/access.php');
        JLoader::register('ProjectforkHelper', JPATH_ADMINISTRATOR . '/components/com_projectfork/helpers/projectfork.php');

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
    public function getTable($type = 'Directory', $prefix = 'PFTable', $config = array())
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


    public function getItemFromProjectPath($project, $path)
    {
        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);

        if (substr($path, -1) == '/') {
            $path = substr($path, 0, -1);
        }

        $params   = ProjectforkHelper::getProjectParams((int) $project);
        $repo_dir = (int) $params->get('repo_dir');

        if (!$repo_dir) {
            return false;
        }

        $query->select('alias')
              ->from('#__pf_repo_dirs')
              ->where('id = ' . $db->quote($repo_dir));

        $db->setQuery($query);
        $alias = $db->loadResult();

        $path = $db->escape($alias . '/' . $path);

        $query->clear();
        $query->select('id')
              ->from('#__pf_repo_dirs')
              ->where('project_id = ' . $db->quote((int) $project))
              ->where('path = ' . $db->quote($path));

        $db->setQuery($query);
        $id = (int) $db->loadResult();

        if ($id) {
            return $this->getItem($id);
        }

        return false;
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

        $table = $this->getTable();

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
        $access = ProjectforkHelperAccess::getActions('project', $table->project_id);
        if (!$access->get('directory.create')) {
            $this->setError(JText::_('COM_PROJECTFORK_ERROR_BATCH_CANNOT_CREATE_DIRECTORY'));
            return false;
        }

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

            // Set the new location in the tree for the node.
            $table->setLocation($dest, 'last-child');

            // Store the row.
            if (!$table->store()) {
                $this->setError($table->getError());
                return false;
            }

            // Rebuild the tree path.
            if (!$table->rebuildPath($table->id)) {
                $this->setError($table->getError());
                return false;
            }

            // Rebuild the paths of the directory children
            if (!$table->rebuild($table->id, $table->lft, $table->level, $table->path)) {
                $this->setError($table->getError());
                return false;
            }
        }

        // Clean the cache
        $this->cleanCache();

        return true;
    }


    /**
     * Batch copy directories to a new directory.
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

        $table = $this->getTable();
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

            if (!$access->get('directory.create')) {
                // Error since user cannot create in parent dir
                $this->setError(JText::_('COM_PROJECTFORK_ERROR_BATCH_CANNOT_CREATE_DIRECTORY'));
                return false;
            }
        }

        // We need to log the parent ID
        $rbid    = $table->parent_id;
        $parents = array();

        // Calculate the emergency stop count as a precaution against a runaway loop bug
        $query = $db->getQuery(true);
        $query->select('COUNT(id)')
              ->from($db->quoteName('#__pf_repo_dirs'))
              ->where('project_id = ' . $db->quote($table->project_id));

        $db->setQuery($query);
        $count = (int) $db->loadResult();

        if ($error = $db->getErrorMsg()) {
            $this->setError($error);
            return false;
        }

        // Parent exists so we let's proceed
        while (!empty($pks) && $count > 0)
        {
            // Pop the first id off the stack
            $pk = array_shift($pks);

            $table->reset();

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

            // Copy is a bit tricky, because we also need to copy the children
            $query->clear();
            $query->select('id')
                  ->from($db->quoteName('#__pf_repo_dirs'))
                  ->where('lft > ' . (int) $table->lft)
                  ->where('rgt < ' . (int) $table->rgt);

            $db->setQuery($query);
            $childIds = $db->loadColumn();

            // Add child ID's to the array only if they aren't already there.
            foreach ($childIds as $childId)
            {
                if (!in_array($childId, $pks)) {
                    array_push($pks, $childId);
                }
            }

            // Make a copy of the old ID and Parent ID
            $oldId       = $table->id;
            $oldParentId = $table->parent_id;

            // Reset the id because we are making a copy.
            $table->id = 0;

            // If we a copying children, the Old ID will turn up in the parents list
            // otherwise it's a new top level item
            $table->parent_id = isset($parents[$oldParentId]) ? $parents[$oldParentId] : $dest;

            // Set the new location in the tree for the node.
            $table->setLocation($table->parent_id, 'last-child');

            $table->level = null;
            $table->asset_id = null;
            $table->lft = null;
            $table->rgt = null;
            $table->protected = 0;

            // Alter the title & alias
            list($title, $alias) = $this->generateNewTitle($table->parent_id, $table->title, $table->alias);
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
            $newIds[$i] = $newId;
            $i++;

            // Now we log the old 'parent' to the new 'parent'
            $parents[$oldId] = $table->id;
            $count--;
        }

        // Rebuild the hierarchy.
        if (!$table->rebuild($rbid)) {
            $this->setError($table->getError());
            return false;
        }

        // Rebuild the tree path.
        if (!$table->rebuildPath($table->id)) {
            $this->setError($table->getError());
            return false;
        }

        // Copy the notes and files in the directories
        if (count($parents)) {
            $suffix     = ((JFactory::getApplication()->isSite()) ? 'Form' : '');
            $note_model = $this->getInstance('Note' . $suffix, 'ProjectforkModel', array('ignore_request' => true));
            $file_model = $this->getInstance('File' . $suffix, 'ProjectforkModel', array('ignore_request' => true));

            foreach($parents AS $old => $new)
            {
                $query->clear();
                $query->select('id')
                      ->from($db->quoteName('#__pf_repo_notes'))
                      ->where('dir_id = ' . (int) $old);

                $db->setQuery($query);
                $notes = (array) $db->loadColumn();

                $query->clear();
                $query->select('id')
                      ->from($db->quoteName('#__pf_repo_files'))
                      ->where('dir_id = ' . (int) $old);

                $db->setQuery($query);
                $files = (array) $db->loadColumn();

                if (count($notes)) {
                    if (!$note_model->batchCopy($new, $notes)) {
                        $this->setError($note_model->getError());
                    }
                }

                if (count($files)) {
                    if (!$file_model->batchCopy($new, $files)) {
                        $this->setError($file_model->getError());
                    }
                }
            }
        }

        return $newIds;
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
        $form = $this->loadForm('com_projectfork.directory', 'directory', array('control' => 'jform', 'load_data' => $loadData));
        if (empty($form)) return false;

        $jinput = JFactory::getApplication()->input;
        $id     = $jinput->get('id', 0);

        $item_access = ProjectforkHelperAccess::getActions('directory', $id);
        $access      = ProjectforkHelperAccess::getActions();

        // Check if the project, and parent id are given
        $project_id = (int) $form->getValue('project_id');
        $parent_id  = $form->getValue('parent_id');

        if (!$project_id) {
            $form->setValue('project_id', null, $this->getState($this->getName() . '.project'));
        }
        if (!$parent_id) {
            $form->setValue('parent_id', null, $this->getState($this->getName() . '.parent_id'));
        }

        return $form;
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
        $table = $this->getTable();
        $pk    = (!empty($data['id'])) ? $data['id'] : (int) $this->getState($this->getName() . '.id');
        $date  = JFactory::getDate();
        $isNew = true;

        $old_path = null;

        // Load the row if saving an existing item.
        if ($pk > 0) {
            if ($table->load($pk)) {
                $isNew = false;

                if (!empty($table->path)) {
                    $old_path = $table->path;
                }
            }
            else {
                $pk = 0;
            }
        }

        // Make sure the title and alias are always unique
        $data['alias'] = '';
        list($title, $alias) = $this->generateNewTitle($data['parent_id'], $data['title'], $data['alias'], $pk);

        $data['title'] = $title;
        $data['alias'] = $alias;

        if (!$this->getState('create_repo')) {
            if (intval($data['parent_id']) <= 1 && ($isNew == false && $table->parent_id > 1)) {
                $this->setError(JText::_('COM_PROJECTFORK_ERROR_REPO_SAVE_ROOT_DIR'));
                return false;
            }

            if (isset($data['protected'])) {
                $data['protected'] = 0;
            }
        }

        // Set the new parent id if parent id not matched OR while New/Save as Copy.
        if ($table->parent_id != $data['parent_id'] || $isNew) {
            $table->setLocation($data['parent_id'], 'last-child');
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

        // Rebuild the path for the directory
        if (!$table->rebuildPath($table->id)) {
            $this->setError($table->getError());
            return false;
        }

        // Rebuild the paths of the directory children
        if (!$table->rebuild($table->id, $table->lft, $table->level, $table->path)) {
            $this->setError($table->getError());
            return false;
        }

        $this->setState($this->getName() . '.id', $table->id);

        // Clear the cache
        $this->cleanCache();

        return true;
    }


    /**
     * Method to rebuild the data structure on the server
     *
     * @param     string     $new        The new path
     * @param     string     $old        The previous path to rename
     * @param     integer    $project    The project id of the directory
     *
     * @return    boolean                True on success, otherwise False
     */
    public function exportStructure($project = 0)
    {
        $basepath = ProjectforkHelperRepository::getBasePath($project);

        if (!empty($old) && $old != $new) {
            // Rename existing path
            $old_path = $basepath . '/' . $old;
            $new_path = $basepath . '/' . $new;

            if (JFolder::exists($new_path)) {
                $this->setError(JText::_('COM_PROJECTFORK_ERROR_REPO_DIR_EXISTS') . ' ' . $new_path);
                return false;
            }

            if (!JFolder::exists($old_path)) {
                return $this->rebuildPath($new_path, NULL, $project);
            }

            $result = JFolder::move($old_path, $new_path);

            if ($result !== true) {
                $this->setError($result . ': ' . $old . ' TO ' . $new);
                return false;
            }

            return true;
        }
        else {
            // Create new one
            $new_path = $basepath . '/' . $new;

            if (JFolder::exists($new_path)) {
                return true;
            }

            if (!JFolder::create($new_path)) {
                return false;
            }

            return true;
        }

        return false;
    }


    /**
     * Method to delete one or more records.
     *
     * @param     array      $pks    An array of record primary keys.
     *
     * @return    boolean            True if successful, false if an error occurs.
     */
    public function delete(&$pks)
    {
        $dispatcher = JDispatcher::getInstance();

        $pks   = (array) $pks;
        $table = $this->getTable();
        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);
        $moved = array();
        $dest  = 0;

        // Include the content plugins for the on delete events.
        JPluginHelper::importPlugin('content');

        // Move the sub folders up which we are not allowed to delete
        foreach ($pks as $i => $pk)
        {
            // Get the parent id of the current directory
            $query->clear();
            $query->select('parent_id')
                  ->from($table->getTableName())
                  ->where($table->getKeyName() . ' = ' . (int) $pk);

            $db->setQuery((string) $query);
            $dest = (int) $db->loadResult();

            if ($dest > 1) {
                // Get the children
                $tree = (array) $table->getTree($pk);

                foreach($tree AS $x => $item)
                {
                    $is_included = false;
                    foreach ($moved AS $p)
                    {
                        if ($item->lft > $p[0] && $item->rgt < $p[1]) {
                            $is_included = true;
                        }
                    }

                    if ($is_included) continue;

                    if (intval($item->id) != $pk) {
                        if (!$this->canDelete($item)) {
                            $table->load($item->id);
                            $table->setLocation($dest, 'last-child');

                            if (!$table->store()) {
                                $this->setError($table->getError());
                                return false;
                            }

                            if (!$table->rebuildPath($table->id)) {
                                $this->setError($table->getError());
                                return false;
                            }

                            if (!$table->rebuild($table->id, $table->lft, $table->level, $table->path)) {
                                $this->setError($table->getError());
                                return false;
                            }

                            $moved[] = array($item->lft, $item->rgt);
                        }
                    }
                }
            }
        }

        // Iterate the items to delete each one.
        $suffix     = ((JFactory::getApplication()->isSite()) ? 'Form' : '');
        $note_model = $this->getInstance('Note' . $suffix, 'ProjectforkModel', array('ignore_request' => true));
        $file_model = $this->getInstance('File' . $suffix, 'ProjectforkModel', array('ignore_request' => true));
        $note_table = $this->getTable('Note');
        $file_table = $this->getTable('File');

        foreach ($pks as $i => $pk)
        {
            if ($table->load($pk)) {
                if ($this->canDelete($table)) {
                    $context  = $this->option . '.' . $this->name;
                    $tree     = $table->getTree($pk, true);
                    $dir_list = array();

                    $dir_project = (int) $table->project_id;
                    $dir_path    = $table->path;

                    if (is_array($tree) && count($tree) > 0) {
                        foreach($tree AS $dir)
                        {
                            $dir_list[] = (int) $dir->id;
                        }
                    }

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



                    if (is_array($dir_list) && count($dir_list) > 0) {
                        $move_notes = array();
                        $del_notes  = array();
                        $move_files = array();
                        $del_files  = array();

                        // Get the notes for deletion
                        $query->clear();
                        $query->select('id, asset_id, access')
                              ->from('#__pf_repo_notes')
                              ->where('dir_id IN(' . implode(', ', $dir_list) . ')');

                        $db->setQuery($query);
                        $notes = (array) $db->loadObjectList();

                        foreach($notes AS $note)
                        {
                            if (!$note_model->canDelete($note)) {
                                $move_notes[] = $note->id;
                            }
                            else {
                                $del_notes[] = $note->id;
                            }
                        }

                        // Get the files for deletion
                        $query->clear();
                        $query->select('id, asset_id, access')
                              ->from('#__pf_repo_files')
                              ->where('dir_id IN(' . implode(', ', $dir_list) . ')');

                        $db->setQuery($query);
                        $files = (array) $db->loadObjectList();

                        foreach($files AS $file)
                        {
                            if (!$file_model->canDelete($file)) {
                                $move_files[] = $file->id;
                            }
                            else {
                                $del_files[] = $file->id;
                            }
                        }

                        // Delete notes
                        if (count($del_notes)) {
                            $query->clear();
                            $query->delete('#__pf_repo_notes')
                                  ->where('id IN(' . implode(', ', $del_notes) . ')');

                            $db->setQuery((string) $query);
                            $db->execute();

                            if ($db->getErrorMsg()) {
                                $this->setError($db->getErrorMsg());
                            }
                        }

                        // Move notes you cant delete
                        foreach($move_notes AS $note)
                        {
                            if ($note_table->load($note)) {
                                $note_table->dir_id = $table->parent_id;

                                if (!$note_table->store()) {
                                    $this->setError($note_table->getError());
                                }
                            }
                        }

                        // Delete files
                        if (count($del_files)) {
                            $query->clear();
                            $query->delete('#__pf_repo_files')
                                  ->where('id IN(' . implode(', ', $del_files) . ')');

                            $db->setQuery((string) $query);
                            $db->execute();

                            if ($db->getErrorMsg()) {
                                $this->setError($db->getErrorMsg());
                            }
                        }

                        // Move files you cant delete
                        foreach($move_files AS $file)
                        {
                            if ($file_table->load($file)) {
                                $file_table->dir_id = $table->parent_id;

                                if (!$file_table->store()) {
                                    $this->setError($file_table->getError());
                                }
                            }
                        }
                    }

                    // Delete the physical directory if it exists
                    if ($dir_project) {
                        $basepath = ProjectforkHelperRepository::getBasePath($dir_project);
                        $fullpath = JPath::clean($basepath . '/' . $dir_path);

                        if (JFolder::exists($fullpath)) {
                            JFolder::delete($fullpath);
                        }
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
     * Method to change the title.
     *
     * @param     integer    $parent_id    The parent directory
     * @param     string     $title        The directory title
     * @param     string     $alias        The current alias
     * @param     integer    $id           The directory id
     *
     * @return    string                   Contains the new title
     */
    protected function generateNewTitle($parent_id, $title, $alias = '', $id = 0)
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
              ->where('parent_id = ' . $db->quote($parent_id));

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
            while ($table->load(array('alias' => $alias, 'parent_id' => $parent_id)))
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
            if ($record->protected == 1) {
                if (!$this->getState('delete_protected')) {
                    return false;
                }
            }

            $access = ProjectforkHelperAccess::getActions('directory', $record->id);
            return $access->get('directory.delete');
        }
        else {
            $access = ProjectforkHelperAccess::getActions();
            return $access->get('directory.delete');
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
            $access = ProjectforkHelperAccess::getActions('directory', $record->id);
            return $access->get('directory.edit.state');
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
            $access = ProjectforkHelperAccess::getActions('directory', $record->id);
            return $access->get('directory.edit');
        }
        else {
            $access = ProjectforkHelperAccess::getActions();
            return $access->get('directory.edit');
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

                $parent_id = (int) $table->parent_id;
                $this->setState($this->getName() . '.parent_id', $parent_id);
            }
        }
        else {
            $parent_id = JRequest::getUInt('filter_parent_id', 0);
            $this->setState($this->getName() . '.parent_id', $parent_id);

            $project = (int) $app->getUserStateFromRequest('com_projectfork.project.active.id', 'filter_project', '');

            if ($project) {
                $this->setState($this->getName() . '.project', $project);
                ProjectforkHelper::setActiveProject($project);
            }
        }

        // Load the parameters.
        $value = JComponentHelper::getParams($this->option);
        $this->setState('params', $value);
    }
}
