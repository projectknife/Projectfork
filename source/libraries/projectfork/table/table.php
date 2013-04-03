<?php
/**
 * @package      pkg_projectfork
 * @subpackage   lib_projectfork
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2013 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


require_once dirname(__FILE__) . '/helper.php';


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
     * Method to delete a row from the database table by primary key value.
     * This method is derived from JTable, with the difference that it will also
     * try to delete foreign, associated assets and items.
     *
     * @param     mixed      $pk    An optional primary key value to delete.
     *
     * @return    boolean           True on success.
     */
    public function delete($pk = null)
    {
        // Initialise variables.
        $k  = $this->_tbl_key;
        $pk = (is_null($pk)) ? $this->$k : $pk;

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
            $query = $this->_db->getQuery(true);

            // Try to load the asset by its name
            if (!$asset->loadByName($name)) {
                $this->setError($asset->getError());
                return false;
            }

            if ($this->_delete_children) {
                // Get all children of this asset
                $query->clear()
                      ->select('*')
                      ->from('#__assets')
                      ->where('parent_id = ' . (int) $asset->id)
                      ->order('level DESC');

                $this->_db->setQuery($query);

                $children   = (array) $this->_db->loadObjectList();
                $foreign    = (array) $this->getForeignAssets($name);
                $all_assets = array_merge($children, $foreign);

                unset($children, $foreign);

                foreach ($all_assets AS $item)
                {
                    // Skip self
                    if ($item->id == $asset->id) continue;

                    // Extract component, name and id
                    list($component, $asset_name, $id) = explode('.', $item->name, 3);

                    $context = $component . '.' . $asset_name;
                    $methods = PFTableHelper::getMethods($context);

                    if (!in_array('deletechild', $methods) && !in_array('deletebycontext', $methods)) {
                        continue;
                    }

                    $table = PFTableHelper::getInstance($context);

                    if (empty($table)) continue;

                    // Delete item by context
                    $table->reset();

                    if (!$table->load($id)) {
                        $this->setError($table->getError());
                        return false;
                    }

                    if (in_array('deletebycontext', $methods)) {
                        if (!$table->deleteByContext($id, $name)) {
                            $this->setError($table->getError());
                            return false;
                        }
                    }
                    elseif (in_array('deletechild', $methods)) {
                        $table->deleteChild($id);
                    }
                }

                unset($item);
            }

            // Delete this asset
            if (!$asset->delete()) {
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
              ->where($this->_tbl_key . ' = ' . (int) $pk);

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
     * Method to delete a record by the give context
     *
     * @param     integer    $pk         A primary key value to delete.
     * @param     string     $context    The foreign asset context that initiated the delete action
     *
     * @return    boolean                True on success, False on error
     */
    public function deleteByContext($pk, $context = null)
    {
        return $this->delete($pk);
    }


    /**
     * @deprecated    use               deleteByContext instead
     *
     * @param         mixed      $pk    An primary key value to delete.
     *
     * @return        boolean           True on success, False on error
     */
    public function deleteChild($pk)
    {
        return $this->deleteByContext($pk);
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
        // Sanitize input.
        JArrayHelper::toInteger($pks);

        $k     = $this->_tbl_key;
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
        // This feature requires asset tracking
        if (!$this->_trackAssets || !$this->_update_children) return true;

        $k = $this->_tbl_key;
        $this->$k = $pk;

        // Get and the asset name.
        $fields = array_keys($data);
        $asset  = JTable::getInstance('Asset');
        $name   = $this->_getAssetName();
        $query  = $this->_db->getQuery(true);

        // Try to load the asset by its name
        if (!$asset->loadByName($name)) {
            $this->setError($asset->getError());
            return false;
        }

        // Get all children of this asset
        $query->clear()
              ->select('*')
              ->from('#__assets')
              ->where('parent_id = ' . (int) $asset->id)
              ->order('level DESC');

        $this->_db->setQuery($query);

        $children   = (array) $this->_db->loadObjectList();
        $foreign    = (array) $this->getForeignAssets($name);
        $all_assets = array_merge($children, $foreign);

        unset($children, $foreign);

        foreach ($all_assets AS $item)
        {
            // Skip self
            if ($item->id == $asset->id) continue;

            // Extract component, name and id
            list($component, $asset_name, $id) = explode('.', $item->name, 3);

            $context = $component . '.' . $asset_name;
            $methods = PFTableHelper::getMethods($context);
            $table   = PFTableHelper::getInstance($context);

            if (empty($table)) continue;

            $props   = $table->getProperties(true);
            $update  = array();
            $changed = false;

            // Load the record
            $table->reset();

            if (!$table->load($id)) {
                $this->setError($table->getError());
                return false;
            }

            // Update the fields
            $changed = $this->updateFields($table, $methods, $data);

            if ($changed) {
                if (!$table->store()) {
                    $this->setError($table->getError());
                    return false;
                }
                if (in_array('updatereferences', $methods)) {

                    $fields   = array_keys($data);
                    $props    = $table->getProperties(true);
                    $new_data = array();

                    foreach ($fields AS $field)
                    {
                        if (!isset($props[$field])) continue;

                        $new_data[$field] = $table->$field;
                    }

                    $table->updateReferences($id, $data);
                }
            }
        }

        unset($item);

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

        if ($old_time > $new_time && $new_time > 0) {
            return $new;
        }
        elseif ($new_time > $old_time && $old_time == 0) {
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

        if ($old_time > $new_time && $new_time > 0) {
            return $new;
        }
        elseif ($new_time > $old_time && $old_time == 0) {
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

        return (in_array($old, $allowed) ? $old : $new);
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


    protected function updateFields(&$table, $methods, $data)
    {
        $fields  = array_keys($data);
        $props   = $table->getProperties(true);
        $changed = false;

        foreach ($fields AS $field)
        {
            $method = 'set' . str_replace('_', '', strtolower($field)) . 'value';

            if(!isset($props[$field])) continue;

            $value     = $data[$field];
            $old_value = $table->$field;

            if (in_array($method, $methods) && $method != 'setvalue') {
                $table->$field = call_user_func_array(array($table, $method), array($old_value, $value));
            }
            else {
                $table->$field = $value;
            }

            if ($table->$field != $old_value) {
                $changed = true;
            }
        }

        return $changed;
    }


    /**
     * Method to find all foreign component table classes and their child assets
     *
     * @deprecated    use                getForeignAssets instead
     * @param         string    $name    The name of the parent asset
     *
     * @return        array              The names of the child assets
     */
    protected function _findAssetChildren($name)
    {
        return $this->getForeignAssets($name);
    }


    /**
     * Method to find all associated foreign assets
     *
     * @param     string    $context    The name of the asset
     *
     * @return    array                 The names of foreign assets
     */
    protected function getForeignAssets($context)
    {
        // Discover component tables
        PFTableHelper::discover();

        $assets   = array();
        $contexts = PFTableHelper::getContexts();

        foreach ($contexts AS $hash)
        {
            $methods = PFTableHelper::getMethods($hash);

            if (!in_array('getassetchildren', $methods)) continue;

            $table = PFTableHelper::getInstance($hash);

            if (empty($table)) continue;

            $foreign = (array) $table->getAssetChildren($context);

            if (count($foreign)) {
                $assets = array_merge($assets, $foreign);
            }
        }

        return $assets;
    }
}
