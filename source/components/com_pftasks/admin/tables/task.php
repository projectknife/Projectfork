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


/**
 * Task table
 *
 */
class PFtableTask extends PFTable
{
    /**
     * Constructor
     *
     * @param    database    $db    A database connector object
     */
    public function __construct(&$db)
    {
        parent::__construct('#__pf_tasks', 'id', $db);
    }


    /**
     * Method to compute the default name of the asset.
     * The default name is in the form table_name.id
     * where id is the value of the primary key of the table.
     *
     * @return    string
     */
    protected function _getAssetName()
    {
        $k = $this->_tbl_key;
        return 'com_pftasks.task.' . (int) $this->$k;
    }


    /**
     * Method to return the title to use for the asset table.
     *
     * @return    string
     */
    protected function _getAssetTitle()
    {
        return $this->title;
    }


    /**
     * Method to get the parent asset id for the record
     *
     * @param     jtable     $table    A JTable object for the asset parent
     * @param     integer    $id
     *
     * @return    integer
     */
    protected function _getAssetParentId($table = null, $id = null)
    {
        // Initialise variables.
        $asset_id = null;
        $db       = $this->getDbo();
        $query    = $db->getQuery(true);

        if ($this->list_id)  {
            // This is a task under a list.
            $query->select('asset_id')
                  ->from('#__pf_task_lists')
                  ->where('id = ' . (int) $this->list_id);

            $this->_db->setQuery($query);
            $result = $this->_db->loadResult();

            if ($result) $asset_id = (int) $result;
        }
        elseif ($this->milestone_id)  {
            // This is a task under a milestone.
            $query->select('asset_id')
                  ->from('#__pf_milestones')
                  ->where('id = ' . (int) $this->milestone_id);

            $this->_db->setQuery($query);
            $result = $this->_db->loadResult();

            if ($result) $asset_id = (int) $result;
        }
        elseif ($this->project_id) {
            // This is a task under a project.
            $query->select('asset_id')
                  ->from('#__pf_projects')
                  ->where('id = ' . (int) $this->project_id);

            $this->_db->setQuery($query);
            $result = $this->_db->loadResult();

            if ($result) $asset_id = (int) $result;
        }

        if (!$asset_id) {
            // No asset found, fall back to the component
            $query->clear();
            $query->select($this->_db->quoteName('id'))
                  ->from($this->_db->quoteName('#__assets'))
                  ->where($this->_db->quoteName('name') . ' = ' . $this->_db->quote("com_pftasks"));

            // Get the asset id from the database.
            $this->_db->setQuery($query);
            $result = $this->_db->loadResult();

            if ($result) $asset_id = (int) $result;
        }

        // Return the asset id.
        if ($asset_id) return $asset_id;

        return parent::_getAssetParentId($table, $id);
    }


    /**
     * Method to get the access level of the parent asset
     *
     * @return    integer
     */
    protected function _getParentAccess()
    {
        $db    = $this->getDbo();
        $query = $db->getQuery(true);

        $list      = (int) $this->list_id;
        $milestone = (int) $this->milestone_id;
        $project   = (int) $this->project_id;

        if ($list > 0) {
            $query->select('access')
                  ->from('#__pf_task_lists')
                  ->where('id = ' . $db->quote($list));

            $db->setQuery($query);
            $access = (int) $db->loadResult();
        }
        elseif ($milestone > 0) {
            $query->select('access')
                  ->from('#__pf_milestones')
                  ->where('id = ' . $db->quote($milestone));

            $db->setQuery($query);
            $access = (int) $db->loadResult();
        }
        elseif ($project > 0) {
            $query->select('access')
                  ->from('#__pf_projects')
                  ->where('id = ' . $db->quote($project));

            $db->setQuery($query);
            $access = (int) $db->loadResult();
        }

        if (!$access) $access = 1;

        return $access;
    }


    /**
     * Overloaded bind function
     *
     * @param     array    $array     Named array
     * @param     mixed    $ignore    An optional array or space separated list of properties to ignore while binding.
     *
     * @return    mixed               Null if operation was satisfactory, otherwise returns an error string
     */
    public function bind($array, $ignore = '')
    {
        if (isset($array['attribs']) && is_array($array['attribs'])) {
            $registry = new JRegistry;
            $registry->loadArray($array['attribs']);
            $array['attribs'] = (string) $registry;
        }

        // Bind the rules.
        if (isset($array['rules']) && is_array($array['rules'])) {
            $rules = new JRules($array['rules']);
            $this->setRules($rules);
        }

        // Bind the assigned users
        if (isset($array['users']) && is_array($array['users'])) {
            $this->users = $array['users'];
        }

        return parent::bind($array, $ignore);
    }


    /**
     * Overloaded check function
     *
     * @return    boolean    True on success, false on failure
     */
    public function check()
    {
        if (trim($this->title) == '') {
            $this->setError(JText::_('COM_PROJECTFORK_WARNING_PROVIDE_VALID_TITLE'));
            return false;
        }

        if (trim($this->alias) == '') $this->alias = $this->title;

        $this->alias = JApplication::stringURLSafe($this->alias);

        if (trim(str_replace('-','', $this->alias)) == '') {
            $this->alias = JFactory::getDate()->format('Y-m-d-H-i-s');
        }

        if (trim(str_replace('&nbsp;', '', $this->description)) == '') {
            $this->description = '';
        }

        $users = array();
        foreach($this->users AS $user)
        {
            if ((int) $user) $users[] = $user;
        }

        $this->users = $users;

        // Check if a project is selected
        if ((int) $this->project_id <= 0) {
            $this->setError(JText::_('COM_PROJECTFORK_WARNING_SELECT_PROJECT'));
            return false;
        }

        // Check for selected access level
        if ($this->access <= 0) {
            $this->access = $this->_getParentAccess();
        }

        return true;
    }


    /**
     * Overrides JTable::store to set modified data and user id.
     *
     * @param     boolean    True to update fields even if they are null.
     *
     * @return    boolean    True on success.
     */
    public function store($updateNulls = false)
    {
        $date = JFactory::getDate();
        $user = JFactory::getUser();

        if ($this->id) {
            // Existing item
            $this->modified    = $date->toSql();
            $this->modified_by = $user->get('id');
        }
        else {
            // New item. A project created_by field can be set by the user,
            // so we don't touch it if set.
            $this->created = $date->toSql();
            if (empty($this->created_by)) $this->created_by = $user->get('id');
        }

        // Generate catid
        $this->catid = intval($this->project_id).''.intval($this->milestone_id).''.intval($this->list_id);

        // Verify that the alias is unique
        $table = JTable::getInstance('Task','PFtable');
        $data  = array('alias' => $this->alias, 'project_id' => $this->project_id, 'milestone_id' => $this->milestone_id, 'list_id' => $this->list_id);

        if ($table->load($data) && ($table->id != $this->id || $this->id == 0)) {
            $this->setError(JText::_('JLIB_DATABASE_ERROR_TASK_UNIQUE_ALIAS'));
            return false;
        }

        // Store the main record
        $success = parent::store($updateNulls);

        if ($success && isset($this->users)) {
            $success = $this->storeUsers($this->id, $this->users);
        }

        return $success;
    }


    /**
     * Method to save the assigned users.
     *
     * @param     int        The task id
     * @param     array      The users
     *
     * @return    boolean    True on success
     */
    public function storeUsers($task_id, $data)
    {
        $item  = 'task';
        $table = JTable::getInstance('UserRef','PFtable');
        $query = $this->_db->getQuery(true);

        if (!$task_id) return true;

        $query->select('a.user_id')
              ->from('#__pf_ref_users AS a')
              ->where('a.item_type = ' . $this->_db->quote($item))
              ->where('a.item_id = ' . $this->_db->quote($task_id));

        $this->_db->setQuery((string) $query);
        $list = (array) $this->_db->loadResultArray();

        // Add new references
        foreach($data AS $uid)
        {
            if (!in_array($uid, $list) && $uid != 0) {
                $sdata = array('item_type' => $item,
                               'item_id'   => $task_id,
                               'user_id'   => $uid);

                if (!$table->save($sdata)) return false;

                $list[] = $uid;
            }
        }

        // Delete old references
        foreach($list AS $uid)
        {
            if (!in_array($uid, $data) && $uid != 0) {
                if (!$table->load(array('item_type' => $item, 'item_id' => $task_id, 'user_id' => $uid))) {
                    return false;
                }

                if (!$table->delete()) return false;
            }
        }

        return true;
    }


    /**
     * Method to delete referenced data of an item.
     *
     * @param     mixed      $pk    An primary key value to delete.
     *
     * @return    boolean
     */
    public function deleteReferences($pk)
    {
        // Delete related attachments
        $query = $this->_db->getQuery(true);
        $query->delete('#__pf_ref_attachments')
              ->where('item_id = ' . $this->_db->quote((int) $pk))
              ->where('item_type = ' . $this->_db->quote('com_pftasks.task'));

        $this->_db->setQuery($query);
        $this->_db->execute();

        // Delete related label references
        $query->clear();
        $query->delete('#__pf_ref_labels')
              ->where('item_id = ' . $this->_db->quote((int) $pk))
              ->where('item_type = ' . $this->_db->quote('com_pftasks.task'));

        $this->_db->setQuery($query);
        $this->_db->execute();

        // Delete related watching users
        $query->clear();
        $query->delete('#__pf_ref_observer')
              ->where('item_id = ' . $this->_db->quote((int) $pk))
              ->where('item_type = ' . $this->_db->quote('com_pftasks.task'));

        $this->_db->setQuery($query);
        $this->_db->execute();

        return true;
    }


    /**
     * Converts record to XML
     *
     * @param     boolean    $mapKeysToText    Map foreign keys to text values
     * @return    string                       Record in XML format
     */
    public function toXML($mapKeysToText=false)
    {
        $db = JFactory::getDbo();

        if ($mapKeysToText) {
            $query = 'SELECT name'
            . ' FROM #__users'
            . ' WHERE id = ' . (int) $this->created_by;
            $db->setQuery($query);
            $this->created_by = $db->loadResult();
        }

        return parent::toXML($mapKeysToText);
    }
}
