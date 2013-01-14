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
        else {
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
        }
        elseif ($milestone > 0) {
            $query->select('access')
                  ->from('#__pf_milestones')
                  ->where('id = ' . $db->quote($milestone));
        }
        elseif ($project > 0) {
            $query->select('access')
                  ->from('#__pf_projects')
                  ->where('id = ' . $db->quote($project));
        }

        $db->setQuery($query);
        $access = (int) $db->loadResult();

        if (!$access) $access = (int) JFactory::getConfig()->get('access');

        return $access;
    }


    /**
     * Method to get the children on an asset (which are not directly connected in the assets table)
     *
     * @param    string    $name    The name of the parent asset
     *
     * @return    array    The names of the child assets
     */
    public function getAssetChildren($name)
    {
        $assets = array();

        list($component, $item, $id) = explode('.', $name, 3);

        // Get the project assets
        if ($component == 'com_pfprojects' && $item == 'project') {
            $db    = $this->getDbo();
            $query = $db->getQuery(true);

            $query->select('c.*')
                  ->from('#__assets AS c')
                  ->join('INNER', $this->_tbl . ' AS a ON (a.asset_id = c.id)')
                  ->where('a.project_id = ' . $db->quote((int) $id))
                  ->group('c.id');

            $db->setQuery($query);
            $assets = (array) $db->loadObjectList();
        }

        // Get the milestone assets
        if ($component == 'com_pfmilestones' && $item == 'milestone') {
            $db    = $this->getDbo();
            $query = $db->getQuery(true);

            $query->select('c.*')
                  ->from('#__assets AS c')
                  ->join('INNER', $this->_tbl . ' AS a ON (a.asset_id = c.id)')
                  ->where('a.milestone_id = ' . $db->quote((int) $id))
                  ->group('c.id');

            $db->setQuery($query);
            $assets = (array) $db->loadObjectList();
        }

        return $assets;
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

        // Check if a project is selected
        if ((int) $this->project_id <= 0) {
            $this->setError(JText::_('COM_PROJECTFORK_WARNING_SELECT_PROJECT'));
            return false;
        }

        // Check for selected access level
        if ($this->access <= 0) {
            $this->access = $this->_getParentAccess();
        }

        // Get the milestone or project start and end date for comparison
        $query      = $this->_db->getQuery(true);
        $nulldate   = $this->_db->getNullDate();
        $date_table = ($this->milestone_id > 0) ? '#__pf_milestones' : '#__pf_projects';
        $date_fld   = ($this->milestone_id > 0) ? 'milestone_id' : 'project_id';

        $query->select('start_date, end_date')
              ->from($date_table)
              ->where('id = ' . $this->_db->quote((int) $this->$date_fld));

        $this->_db->setQuery($query);
        $dates = $this->_db->loadObject();

        if ($dates) {
            $p_start = $dates->start_date;
            $p_end   = $dates->end_date;
        }
        else {
            $p_start = $nulldate;
            $p_end   = $nulldate;
        }

        // Turn dates to timestamps
        $p_start_time = ($p_start == $nulldate) ? 0 : strtotime($p_start);
        $p_end_time   = ($p_end == $nulldate)   ? 0 : strtotime($p_end);

        $a_start_time = ($this->start_date == $nulldate)  ? 0 : strtotime($this->start_date);
        $a_end_time   = ($this->end_date == $nulldate)    ? 0 : strtotime($this->end_date);


        if ($a_start_time && $a_end_time) {
            // Make sure the start is before the end
            if ($a_start_time > $a_end_time) {
                $a_start_time     = $a_end_time;
                $this->start_date = $this->end_date;
            }
        }
        else {
            // Use the parent start date if not set
            if ($a_start_time == 0) {
                $a_start_time     = $p_start_time;
                $this->start_date = $p_start;
            }

            // Use the parent end date if not set
            if ($p_end_time == 0) {
                $a_end_time     = $p_end_time;
                $this->end_date = $p_end;
            }

            // Make sure the start is before the end if a deadline is set
            if ($a_start_time > $a_end_time && $a_end_time > 0) {
                $a_start_time     = $a_end_time;
                $this->start_date = $this->end_date;
            }
        }

        // Use the task start date if parent is not set
        if ($p_start_time == 0) {
            $p_start_time = $a_start_time;
            $p_start      = $this->start_date;
        }

        // Use the task end date if parent is not set
        if ($p_end_time == 0) {
            $p_end_time = $a_end_time;
            $p_end      = $this->end_date;
        }

        // Check that the start date is within range of the parent start
        if ($p_start_time > $a_start_time) {
            $a_start_time     = $p_start_time;
            $this->start_date = $p_start;
        }

        // Check that the start date is within range of the parent deadline
        if ($a_start_time > $p_end_time) {
            $a_start_time     = $p_end_time;
            $this->start_date = $p_end;
        }

        // Make sure we have a deadline
        if ($a_end_time == 0) {
            $a_end_time     = $p_end_time;
            $this->end_date = $p_end;
        }

        if ($a_end_time > 0) {
            // Check that the end date is after the start date
            if ($a_start_time > $a_end_time) {
                $a_end_time     = $a_start_time;
                $this->end_date = $this->start_date;
            }

            // Check that the end date is within range of the parent deadline
            if ($a_end_time > $p_end_time && $p_end_time > 0) {
                $a_end_time     = $p_end_time;
                $this->end_date = $p_end;
            }
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

        // Verify that the alias is unique
        $table = JTable::getInstance('Task','PFtable');
        $data  = array('alias' => $this->alias, 'project_id' => $this->project_id, 'milestone_id' => $this->milestone_id, 'list_id' => $this->list_id);

        if ($table->load($data) && ($table->id != $this->id || $this->id == 0)) {
            $this->setError(JText::_('JLIB_DATABASE_ERROR_TASK_UNIQUE_ALIAS'));
            return false;
        }

        // Store the main record
        $success = parent::store($updateNulls);

        return $success;
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
