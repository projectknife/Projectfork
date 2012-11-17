<?php
/**
 * @package      Projectfork.Library
 * @subpackage   Table
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


class PFTable extends JTable
{
    /**
     * Should the table delete all child items too?
     *
     * @var    boolean
     */
    protected $_delete_children = false;

    /**
     * Should the table update all child items too?
     *
     * @var    boolean
     */
    protected $_update_children = false;

    /**
     * The fields of the child items to update
     *
     * @var    array
     */
    protected $_update_fields = array();


    /**
     * Method to get the children on an asset (which are not directly connected in the assets table)
     *
     * @param     string    $name    The name of the parent asset
     *
     * @return    array              The names of the child assets
     */
    public function getAssetChildren($name)
    {
        return array();
    }


    /**
     * Method to find all foreign component table classes and their child assets
     *
     * @param     string    $name    The name of the parent asset
     *
     * @return    array              The names of the child assets
     */
    protected function _findAssetChildren($name)
    {
        $components = PFApplicationHelper::getComponents();
        $assets     = array();

        foreach ($components AS $component)
        {
            $tables_path = JPath::clean(JPATH_ADMINISTRATOR . '/components/' . $component->element . '/tables');

            if (JFolder::exists($tables_path)) {
                JLoader::discover('PFtable', $tables_path, false);

                $tables = (array) JFolder::files($tables_path, '.php$');

                foreach ($tables AS $table_file)
                {
                    $table_name = JFile::stripExt($table_file);
                    $class      = 'PFtable' . ucfirst($table_name);

                    if (class_exists($class)) {
                        $instance     = self::getInstance(ucfirst($table_name), 'PFtable');
                        $methods      = get_class_methods($instance);

                        if (in_array('getAssetChildren', $methods)) {
                            $table_assets = (array) $instance->getAssetChildren($name);

                            if (count($table_assets)) {
                                $assets = array_merge($assets, $table_assets);
                            }
                        }
                    }
                }
            }
        }

        return $assets;
    }


    /**
     * Method to delete a row from the database table by primary key value.
     * This method is derived from JTable, with the difference that it will also
     * try to properly delete any record in foreign tables, based on asset children
     *
     * @param     mixed      $pk    An optional primary key value to delete. If not set the instance property value is used.
     *
     * @return    boolean           True on success.
     */
    public function delete($pk = null)
    {
        // Initialise variables.
        $k  = $this->_tbl_key;
        $pk = (is_null($pk)) ? $this->$k : $pk;

        $tables = array();

        // If no primary key is given, return false.
        if ($pk === null) {
            $e = new JException(JText::_('JLIB_DATABASE_ERROR_NULL_PRIMARY_KEY'));
            $this->setError($e);
            return false;
        }

        // If tracking assets, remove the asset first.
        if ($this->_trackAssets) {
            // Get and the asset name.
            $this->$k = $pk;

            $name  = $this->_getAssetName();
            $asset = JTable::getInstance('Asset');

            if ($asset->loadByName($name)) {
                if ($this->_delete_children) {
                    // Here is where things are different from the parent method
                    // Get child assets
                    $query = $this->_db->getQuery(true);
                    $query->select('*')
                          ->from('#__assets')
                          ->where('parent_id = ' . $this->_db->quote($asset->id));

                    $this->_db->setQuery($query);
                    $direct_children   = (array) $this->_db->loadObjectList();
                    $indirect_children = (array) $this->_findAssetChildren($name);

                    $children = array_merge($direct_children, $indirect_children);

                    foreach ($children AS $child)
                    {
                        if ($child->id == $asset->id) {
                            // Skip self
                            continue;
                        }

                        list($component, $asset_name, $asset_id) = explode('.', $child->name, 3);

                        $table_prefix = 'PFtable';
                        $table_name   = ucfirst($asset_name);
                        $cache_key    = $table_prefix . '.' . $table_name;
                        $asset_id     = (int) $asset_id;

                        // Try to get an instance of the asset
                        if (!array_key_exists($cache_key, $tables)) {
                            $tables[$cache_key] = JTable::getInstance($table_name, $table_prefix);

                            if ($tables[$cache_key]) {
                                // We have an instance. now check if the "deleteByParentAsset" method is available
                                $methods = get_class_methods($tables[$cache_key]);

                                if (!in_array('deleteChild', $methods)) {
                                    $tables[$cache_key] = false;
                                }
                            }
                        }

                        $child_table = $tables[$cache_key];

                        if (!$child_table) {
                            // Not a valid table instance, go to the next record
                            continue;
                        }

                        // Delete child
                        $child_table->reset();
                        $child_table->deleteChild($asset_id);
                    }
                }

                // Delete this asset
                if (!$asset->delete()) {
                    $this->setError($asset->getError());
                    return false;
                }
            }
            else {
                $this->setError($asset->getError());
                return false;
            }
        }

        // Delete references first
        $this->deleteReferences($pk);

        // Delete the row by primary key.
        $query = $this->_db->getQuery(true);

        $query->delete()
              ->from($this->_tbl)
              ->where($this->_tbl_key . ' = ' . $this->_db->quote($pk));

        $this->_db->setQuery($query);

        // Check for a database error.
        if (!$this->_db->execute()) {
            $e = new JException(JText::sprintf('JLIB_DATABASE_ERROR_DELETE_FAILED', get_class($this), $this->_db->getErrorMsg()));
            $this->setError($e);
            return false;
        }

        return true;
    }


    /**
     * Method to delete a child record of a parent asset.
     * This method does the same thing as the "delete" method, only that it returns
     * the asset ids that have been deleted and does not report any errors
     *
     * @param     mixed    $pk         An primary key value to delete.
     *
     * @return    array    $deleted    The deleted asset ids
     */
    public function deleteChild($pk)
    {
        static $tables = array();

        $pk = (int) $pk;
        $k  = $this->_tbl_key;

        // If no primary key is given, return False.
        if ($pk == 0) {
            return false;
        }

        // If tracking assets, remove the asset first.
        if ($this->_trackAssets) {
            // Get the asset
            $this->$k = $pk;

            $name  = $this->_getAssetName();
            $asset = JTable::getInstance('Asset');

            if ($asset->loadByName($name)) {
                // Get child assets
                $query = $this->_db->getQuery(true);
                $query->select('*')
                      ->from('#__assets')
                      ->where('parent_id = ' . $this->_db->quote($asset->id));

                $this->_db->setQuery($query);
                $direct_children   = (array) $this->_db->loadObjectList();
                $indirect_children = (array) $this->_findAssetChildren($name);

                $children = array_merge($direct_children, $indirect_children);

                foreach ($children AS $child)
                {
                    if ($child->id == $asset->id) {
                        // Skip self
                        continue;
                    }

                    list($component, $asset_name, $asset_id) = explode('.', $child->name, 3);

                    $table_prefix = 'PFtable';
                    $table_name   = ucfirst($asset_name);
                    $cache_key    = $table_prefix . '.' . $table_name;
                    $asset_id     = (int) $asset_id;

                    // Try to get an instance of the asset
                    if (!array_key_exists($cache_key, $tables)) {
                        $tables[$cache_key] = JTable::getInstance($table_name, $table_prefix);

                        if ($tables[$cache_key]) {
                            // We have an instance. now check if the "deleteByParentAsset" method is available
                            $methods = get_class_methods($tables[$cache_key]);

                            if (!in_array('deleteChild', $methods)) {
                                $tables[$cache_key] = false;
                            }
                        }
                    }

                    $child_table = $tables[$cache_key];

                    if (!$child_table) {
                        // Not a valid table instance, go to the next record
                        continue;
                    }

                    // Delete child
                    $child_table->reset();
                    $child_table->deleteChild($asset_id);
                }

                // Delete this asset
                $asset->delete();
            }
        }

        // Delete references first
        $this->deleteReferences($pk);

        // Delete the row by primary key.
        $query = $this->_db->getQuery(true);

        $query->delete()
              ->from($this->_tbl)
              ->where($this->_tbl_key . ' = ' . $this->_db->quote($pk));

        $this->_db->setQuery($query);
        $this->_db->execute();

        return true;
    }


    /**
     * Method to delete referenced data of an item.
     *
     * @param     mixed      $pk    An primary key value to delete.
     *
     * @return    boolean
     */
    public function deleteReferences($pk = null)
    {
        return true;
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
            $checkin = ' AND (checked_out = 0 OR checked_out = ' .(int) $uid . ')';
        }

        // Update the state for rows with the given primary keys.
        $this->_db->setQuery(
            'UPDATE ' . $this->_db->quoteName($this->_tbl).
            ' SET ' . $this->_db->quoteName('state').' = ' .(int) $state .
            ' WHERE (' . $where . ')' .
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

        if ($this->_trackAssets && $this->_update_children && in_array('state', $this->_update_fields)) {
            foreach($pks as $pk)
            {
                $data = array('state' => $state);
                $this->updateChildren($pk, $data);
            }
        }

        return true;
    }


    /**
     * Method to set the publishing state for a row or list of rows in the database
     * table.  The method respects checked out rows by other users and will attempt
     * to checkin rows that it can after adjustments are made.
     *
     * @param     mixed      $pks      An optional array of primary key values to update.  If not set the instance property value is used.
     * @param     integer    $state    The publishing state. eg. [0 = unpublished, 1 = published]
     * @param     integer    $uid      The user id of the user performing the operation.
     *
     * @return    boolean              True on success.
     */
    public function publish($pks = null, $state = 1, $uid = 0)
    {
        return $this->setState($pks, $state, $uid);
    }


    /**
     * Method to update child items
     *
     * @param    integer    $pk      The record id who's children to update
     * @param    array      $data    The new data
     *
     * return boolean
     */
    public function updateChildren($pk, $data)
    {
        static $tables      = array();
        static $tbl_methods = array();
        static $tbl_fields  = array();

        // This feature requires asset tracking
        if (!$this->_trackAssets) {
            return true;
        }

        $field_names = array_keys($data);

        // Get and the asset name.
        $k = $this->_tbl_key;
        $this->$k = $pk;

        $asset = JTable::getInstance('Asset');
        $name  = $this->_getAssetName();

        if (!$asset->loadByName($name)) {
            return false;
        }

        // Get child assets
        $query = $this->_db->getQuery(true);
        $query->select('*')
              ->from('#__assets')
              ->where('parent_id = ' . $this->_db->quote($asset->id));

        $this->_db->setQuery($query);
        $direct_children   = (array) $this->_db->loadObjectList();
        $indirect_children = (array) $this->_findAssetChildren($name);

        $children = array_merge($direct_children, $indirect_children);

        foreach ($children AS $child)
        {
            if ($child->id == $asset->id) {
                // Skip self
                continue;
            }

            list($component, $asset_name, $asset_id) = explode('.', $child->name, 3);

            $table_prefix = 'PFtable';
            $table_name   = ucfirst($asset_name);
            $cache_key    = $table_prefix . '.' . $table_name;
            $asset_id     = (int) $asset_id;

            // Try to get an instance of the table
            if (!array_key_exists($cache_key, $tables)) {
                $tables[$cache_key] = JTable::getInstance($table_name, $table_prefix);

                if ($tables[$cache_key]) {
                    // We have an instance. now check if the "updateChildren" method is available
                    $methods = get_class_methods($tables[$cache_key]);

                    $tbl_methods[$cache_key] = $methods;
                    $tbl_fields[$cache_key]  = array();

                    $child_props = array_keys($tables[$cache_key]->getProperties(true));

                    foreach($child_props AS $prop)
                    {
                        if (in_array($prop, $field_names)) {
                            $tbl_fields[$cache_key][$prop] = $data[$prop];
                        }
                    }
                }
            }

            $child_table = $tables[$cache_key];

            if (!$child_table) {
                // Not a valid table instance, go to the next record
                continue;
            }

            // Update the fields
            if (count($tbl_fields[$cache_key])) {
                $has_changed = false;

                // Load the record
                $child_table->reset();

                if (!$child_table->load($asset_id)) {
                    continue;
                }

                foreach($tbl_fields[$cache_key] AS $field => $value)
                {
                    $current = $child_table->$field;
                    $parts   = explode('_', $field);
                    $method  = 'set';

                    foreach ($parts AS $part)
                    {
                        if (!$part) {
                            continue;
                        }

                        $method .= ucfirst($part);
                    }

                    $method .= 'Value';

                    if (in_array($method, $tbl_methods[$cache_key]) && $method != 'setValue') {
                        $child_table->$field = $child_table->$method($child_table->$field, $value);
                    }
                    else {
                        $child_table->$field = $value;
                    }

                    if ($current != $child_table->$field) {
                        $has_changed = true;
                    }
                }

                if ($has_changed) {
                    $child_table->store();

                    if (in_array('updateReferences', $tbl_methods[$cache_key])) {
                        $child_table->updateReferences($asset_id, $tbl_fields[$cache_key]);
                    }
                }
            }

            // Update children
            if (in_array('updateChildren', $tbl_methods[$cache_key])) {
                $child_table->reset();
                $child_table->updateChildren($asset_id, $data);
            }
        }

        return true;
    }


    /**
     * Method to set referenced data of an item.
     *
     * @param     mixed      $pk      An primary key value of the updated item.
     * @param     array      $data    The changed data
     *
     * @return    boolean
     */
    public function updateReferences($pk = null, $data = array())
    {
        return true;
    }


    /**
     * Method to set the "start_date" field value of a record
     *
     * @param     mixed    $old    The current value
     * @param     mixed    $new    The new value
     *
     * @return    mixed
     */
    public function setStartDateValue($old = null, $new = null)
    {
        $nulldate = $this->_db->getNullDate();
        $old_time = ($old == $nulldate ? 0 : strtotime($old));
        $new_time = ($new == $nulldate ? 0 : strtotime($new));

        if ($old_time > $new_time) {
            return $new;
        }

        return $old;
    }


    /**
     * Method to set the "end_date" field value of a record
     *
     * @param     mixed    $old    The current value
     * @param     mixed    $new    The new value
     *
     * @return    mixed
     */
    public function setEndDateValue($old = null, $new = null)
    {
        $nulldate = $this->_db->getNullDate();
        $old_time = ($old == $nulldate ? 0 : strtotime($old));
        $new_time = ($new == $nulldate ? 0 : strtotime($new));

        if ($old_time > $new_time) {
            return $new;
        }

        return $old;
    }


    /**
     * Method to set the "access" field value of a record
     *
     * @param     mixed    $old    The current value
     * @param     mixed    $new    The new value
     *
     * @return    mixed
     */
    public function setAccessValue($old = null, $new = null)
    {
        $allowed = PFAccessHelper::getAccessTree($new);

        if (!in_array($old, $allowed)) {
            return $new;
        }

        return $old;
    }


    /**
     * Method to set the "state" field value of a record
     *
     * @param     mixed    $old    The current value
     * @param     mixed    $new    The new value
     *
     * @return    mixed
     */
    public function setStateValue($old = null, $new = null)
    {
        if ($new == '1') {
            return $old;
        }

        $ignore = array('-2');

        if ($new == '0')  $ignore = array('-2', '0', '2');
        if ($new == '-2') $ignore = array('-2');
        if ($new == '2')  $ignore = array('-2');

        if (!in_array($old, $ignore)) {
            return $new;
        }

        return $old;
    }
}
