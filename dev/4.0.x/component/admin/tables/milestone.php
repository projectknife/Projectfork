<?php
/**
 * @package      Projectfork
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


jimport('joomla.database.tableasset');


/**
 * Milestone table
 *
 */
class PFTableMilestone extends JTable
{
    /**
     * Constructor
     *
     * @param    database    $db    A database connector object
     */
    public function __construct(&$db)
    {
        parent::__construct('#__pf_milestones', 'id', $db);
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
        return 'com_projectfork.milestone.'.(int) $this->$k;
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
        $asset_id = null;
        $query    = $this->_db->getQuery(true);

        // This is a milestone under a project.
        if ($this->project_id) {
            // Build the query to get the asset id for the parent project.
            $query->select('asset_id')
                  ->from('#__pf_projects')
                  ->where('id = '.(int) $this->project_id);

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
     * Method to get the project access level id
     *
     * @return    integer
     */
    protected function _getAccessProjectId()
    {
        if ((int) $this->project_id == 0) return 1;

        $db    = $this->getDbo();
        $query = $db->getQuery(true);

        $query->select('access')
              ->from('#__pf_projects')
              ->where('id = '.(int) $this->project_id);

        $db->setQuery($query);
        $access = (int) $db->loadResult();

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
        if (trim(str_replace('-','', $this->alias)) == '') $this->alias = JFactory::getDate()->format('Y-m-d-H-i-s');

        if (trim(str_replace('&nbsp;', '', $this->description)) == '') $this->description = '';

        // Check if a project is selected
        if ((int) $this->project_id == 0) {
            $this->setError(JText::_('COM_PROJECTFORK_WARNING_SELECT_PROJECT'));
            return false;
        }

        // Check for selected access level
        if ($this->access == 0) $this->access = $this->_getAccessProjectId();

        // Check the start date is not earlier than the end date.
        if ($this->end_date > $this->_db->getNullDate() && $this->end_date < $this->start_date) {
            // Swap the dates
            $temp = $this->start_date;
            $this->start_date = $this->end_date;
            $this->end_date   = $temp;
        }

        // Check if the start and end dates are in bounds of the parent dates
        $project = JTable::getInstance('project', 'PFTable');
        $project->load((int)$this->project_id);

        $a_start = strtotime($project->start_date);
        $a_end   = strtotime($project->end_date);
        $b_start = strtotime($this->start_date);
        $b_end   = strtotime($this->end_date);

        if ($a_start > $b_start) $this->start_date = $project->start_date;
        if ($a_end < $b_end)     $this->end_date   = $project->end_date;

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
            $this->modified    = $date->toMySQL();
            $this->modified_by = $user->get('id');
        }
        else {
            // New item. A created_by field can be set by the user,
            // so we don't touch it if set.
            $this->created = $date->toMySQL();
            if (empty($this->created_by)) $this->created_by = $user->get('id');
        }

        // Verify that the alias is unique
        $table = JTable::getInstance('Milestone', 'PFTable');

        if ($table->load(array('alias' => $this->alias, 'project_id' => $this->project_id)) && ($table->id != $this->id || $this->id==0)) {
            $this->setError(JText::_('JLIB_DATABASE_ERROR_MILESTONE_UNIQUE_ALIAS'));
            return false;
        }

        return parent::store($updateNulls);
    }

    /**
     * Method to set the state for a row or list of rows in the database
     * table. The method respects checked out rows by other users and will attempt
     * to checkin rows that it can after adjustments are made.
     *
     * @param     mixed      $pks      An optional array of primary key values to update.  If not set the instance property value is used.
     * @param     integer    $state    The state. eg. [0 = Inactive, 1 = Active, 2 = Archived, -2 = Trashed]
     * @param     integer    $uid      The user id of the user performing the operation.
     *
     * @return    boolean              True on success.
     */
    public function setState($pks = null, $state = 1, $uid = 0)
    {
        // Initialise variables.
        $k = $this->_tbl_key;

        // Sanitize input.
        JArrayHelper::toInteger($pks);
        $uid   = (int) $uid;
        $state = (int) $state;

        // If there are no primary keys set check to see if the instance key is set.
        if (empty($pks)) {
            if ($this->$k) {
                $pks = array($this->$k);
            }
            else {
                // Nothing to set state on, return false.
                $this->setError(JText::_('JLIB_DATABASE_ERROR_NO_ROWS_SELECTED'));
                return false;
            }
        }

        // Build the WHERE clause for the primary keys.
        $where = $k . '=' . implode(' OR ' . $k . '=', $pks);

        // Determine if there is checkin support for the table.
        if (!property_exists($this, 'checked_out') || !property_exists($this, 'checked_out_time')) {
            $checkin = '';
        }
        else {
            $checkin = ' AND (checked_out = 0 OR checked_out = ' . (int) $uid . ')';
        }

        // Update the state for rows with the given primary keys.
        $this->_db->setQuery(
            'UPDATE ' . $this->_db->quoteName($this->_tbl).
            ' SET ' . $this->_db->quoteName('state').' = '.(int) $state .
            ' WHERE (' . $where.')' .
            $checkin
        );
        $this->_db->query();

        // Check for a database error.
        if ($this->_db->getErrorNum()) {
            $this->setError($this->_db->getErrorMsg());
            return false;
        }

        // If checkin is supported and all rows were adjusted, check them in.
        if ($checkin && (count($pks) == $this->_db->getAffectedRows())) {
            // Checkin the rows.
            foreach($pks as $pk)
            {
                $this->checkin($pk);
            }
        }

        // If the JTable instance value is in the list of primary keys that were set, set the instance.
        if (in_array($this->$k, $pks)) $this->state = $state;
        $this->setError('');

        return true;
    }



    /**
     * @see    setstate
     *
     */
    public function publish($pks = null, $state = 1, $userId = 0)
    {
        return $this->setState($pks, $state, $userId);
    }


    /**
     * Deletes all items by a reference field
     *
     * @param     mixed      $id       The parent item id(s)
     * @param     string     $field    The parent field name
     *
     * @return    boolean              True on success, False on error
     */
    public function deleteByReference($id, $field)
    {
        $db    = $this->_db;
        $query = $db->getQuery(true);

        // Generate the WHERE clause
        $where = $db->quoteName($field) . (is_array($id) ? ' IN(' . implode(', ', $id) . ')' : ' = ' . (int) $id );

        if (is_array($id) && count($id) === 1) {
            $where = $db->quoteName($field) . ' = ' . (int) $id[0];
        }

        // Delete the records. Note that the assets have already been deleted
        $query->delete($this->_db->quoteName($this->_tbl))
              ->where($where);

        $db->setQuery((string) $query);
        $db->query();

        return true;
    }


    /**
     * Updates all items by reference data and parent item
     *
     * @param     integer    $id       The parent item id
     * @param     string     $field    The parent field name
     * @param     array      $data     The parent data
     * @return    boolean              True on success, False on error
     */
    public function updateByReference($id, $field, $data)
    {
        $db        = $this->_db;
        $fields    = array_keys($data);
        $null_date = $db->getNullDate();
        $pk        = $this->_tbl_key;

        // Check if the fields exist
        foreach($fields AS $i => $tbl_field)
        {
            if (!property_exists($this, $tbl_field)) {
                unset($fields[$i]);
                unset($data[$tbl_field]);
            }
        }

        // Return if no fields are left
        if (count($fields) == 0) {
            return true;
        }

        $tbl_fields = implode(', ', array_keys($data));

        // Find access children if access field is in the data
        $access_children = array();
        if (in_array('access', $fields)) {
            $access_children = array_keys(ProjectforkHelper::getChildrenOfAccess($data['access']));
        }

        // Get the items we have to update
        $where = $db->quoteName($field) . (is_array($id) ? ' IN(' . implode(', ', $id) . ')' : ' = ' . (int) $id );

        if (is_array($id) && count($id) === 1) {
            $where = $db->quoteName($field) . ' = ' . (int) $id[0];
        }

        $query = $db->getQuery(true);
        $query->select($this->_tbl_key . ', ' . $tbl_fields)
              ->from($db->quoteName($this->_tbl))
              ->where($where);

        $db->setQuery((string) $query);

        // Get the result
        $list = (array) $db->loadObjectList();

        // Update each item
        foreach($list AS $item)
        {
            $updates = array();

            foreach($data AS $key => $val)
            {
                switch($key)
                {
                    case 'start_date':
                        $tmp_val_1 = strtotime($val);
                        $tmp_val_2 = strtotime($item->$key);
                        if ($tmp_val_1 > 0) {
                            if (($tmp_val_1 > $tmp_val_2) && $tmp_val_2 > 0) {
                                $updates[$key] = $db->quoteName($key) . ' = ' . $db->quote($val);
                            }
                        }
                        break;

                    case 'end_date':
                        $tmp_val_1 = strtotime($val);
                        $tmp_val_2 = strtotime($item->$key);
                        if ($tmp_val_1 > 0) {
                            if (($tmp_val_1 < $tmp_val_2)) {
                                $updates[$key] = $db->quoteName($key) . ' = ' . $db->quote($val);
                            }
                        }
                        break;

                    case 'access':
                        if ($val != $item->$key && !in_array($item->$key, $access_children)) {
                            $updates[$key] = $db->quoteName($key) . ' = ' . $db->quote($val);
                        }
                        break;

                    case 'state':
                        if ($val != $item->$key) {
                            // Do not publish/unpublish items that are currently archived or trashed
                            // Also, do not publish items that are unpublished
                            if (($item->$key == '2' || $item->$key == '-2' || $item->$key == '0') && ($val == '0' || $val == '1')) {
                                continue;
                            }

                            $updates[$key] = $db->quoteName($key) . ' = ' . $db->quote($val);
                        }
                        break;

                    default:
                        if ($item->$key != $val) $updates[$key] = $db->quoteName($key) . ' = ' . $db->quote($val);
                        break;
                }
            }

            if (count($updates)) {
                $query->clear();

                $query->update($db->quoteName($this->_tbl))
                      ->set(implode(', ', $updates))
                      ->where($db->quoteName($this->_tbl_key) . ' = ' . (int) $item->$pk);

                $db->setQuery((string) $query);
                $db->query();
            }
        }
    }


    /**
     * Converts record to XML
     *
     * @param     boolean    $mapKeysToText    Map foreign keys to text values
     * @return    string                       Record in XML format
     */
    function toXML($mapKeysToText=false)
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
