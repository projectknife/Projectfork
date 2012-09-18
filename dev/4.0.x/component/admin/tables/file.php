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
 * File Table Class
 *
 */
class PFTableFile extends JTable
{
    /**
     * Constructor
     *
     * @param    database    $db    A database connector object
     */
    public function __construct(&$db)
    {
        parent::__construct('#__pf_repo_files', 'id', $db);
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
        return 'com_projectfork.file.' . (int) $this->$k;
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

        $query = $this->_db->getQuery(true);

        if ($this->dir_id) {
            // Build the query to get the asset id for the parent topic.
            $query->select('asset_id')
                  ->from('#__pf_repo_dirs')
                  ->where('id = ' . (int) $this->dir_id);

            // Get the asset id from the database.
            $this->_db->setQuery((string) $query);
            $result = $this->_db->loadResult();

            if ($result) $asset_id = (int) $result;
        }
        elseif ($this->project_id) {
            // Build the query to get the asset id for the parent project.
            $query->select('asset_id')
                  ->from('#__pf_projects')
                  ->where('id = ' . (int) $this->project_id);

            // Get the asset id from the database.
            $this->_db->setQuery((string) $query);
            $result = $this->_db->loadResult();

            if ($result) $asset_id = (int) $result;
        }

        // Return the asset id.
        if ($asset_id) return $asset_id;

        return parent::_getAssetParentId($table, $id);
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
        return true;
        if (trim(str_replace('&nbsp;', '', $this->title)) == '') {
            $this->setError(JText::_('COM_PROJECTFORK_WARNING_PROVIDE_VALID_TITLE'));
            return false;
        }

        return true;
    }


    /**
     * Overrides JTable::store to set modified data and user id.
     *
     * @param     boolean    True to update fields even if they are null.
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
            // New item. A created_by field can be set by the user, so we don't touch it if set.
            $this->created = $date->toSql();
            if (empty($this->created_by)) $this->created_by = $user->get('id');
        }

        // Store the main record
        $success = parent::store($updateNulls);

        return $success;
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
        $uid    = (int) $uid;
        $state  = (int) $state;

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
        $query = $this->_db->getQuery(true);

        $query->update($this->_db->quoteName($this->_tbl))
              ->set($this->_db->quoteName('state') . ' = ' . $state)
              ->where('(' . $where . ')');

        $this->_db->setQuery((string) $query);
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

        $tbl_fields = implode(', ', array_keys($data));

        // Return if no fields are left to update
        if (count($fields) == 0) {
            return true;
        }

        // Find access children if access field is in the data
        $access_children = array();
        if (in_array('access', $fields)) {
            $access_children = array_keys(ProjectforkHelper::getChildrenOfAccess($data['access']));
        }

        // Get the items we have to update
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
