<?php
/**
 * @package      Projectfork
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


jimport('joomla.database.tablenested');


/**
 * Comment table
 *
 */
class PFTableComment extends JTableNested
{
    /**
     * Constructor
     *
     * @param    database    $db    A database connector object
     */
    public function __construct(&$db)
    {
        parent::__construct('#__pf_comments', 'id', $db);
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
        return 'com_projectfork.comment.' . (int) $this->$k;
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
     * Get the parent asset id for the record
     *
     * @param     jtable     $table    A JTable object for the asset parent.
     * @param     integer    $id       The id for the asset
     *
     * @return    integer              The id of the asset's parent
     */
    protected function _getAssetParentId($table = null, $id = null)
    {
        $asset_id = null;
        $result   = null;
        $query    = $this->_db->getQuery(true);

        // This is a comment under another comment.
        if ($this->parent_id > 1) {
            // Build the query to get the asset id for the parent comment.
            $query->select($this->_db->quoteName('asset_id'))
                  ->from($this->_db->quoteName('#__pf_comments'))
                  ->where($this->_db->quoteName('id') . ' = ' . $this->parent_id);

            // Get the asset id from the database.
            $this->_db->setQuery($query);
            $result = $this->_db->loadResult();
        }
        elseif ($asset_id === null) {
            // This is a comment that needs to parent with the context item.
            // Build the query to get the asset id for the parent comment.
            $query->select($this->_db->quoteName('id'))
                  ->from($this->_db->quoteName('#__assets'))
                  ->where($this->_db->quoteName('name') . ' = ' . $this->_db->quote($this->context . '.' . $this->item_id));

            // Get the asset id from the database.
            $this->_db->setQuery($query);
            $result = $this->_db->loadResult();
        }

        if (!empty($result)) {
            $asset_id = $result;
        }

        // Return the asset id.
        if ($asset_id) {
            return $asset_id;
        }
        else {
            return parent::_getAssetParentId($table, $id);
        }
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

        if (!isset($array['state'])) {
            $array['state'] = 1;
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
        if (trim($this->description) == '') {
            $this->setError(JText::_('COM_PROJECTFORK_WARNING_PROVIDE_VALID_DESC'));
            return false;
        }

        // Check attribs
        $registry = new JRegistry;
        $registry->loadString($this->attribs);

        $this->attribs = (string) $registry;

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

        // Get all comment children
        $children = array();
        $db       = $this->_db;
        $query    = $db->getQuery(true);

        foreach($pks AS $id)
        {
            $query->select('id, lft, rgt, context, item_id')
                  ->from($db->quoteName($this->_tbl))
                  ->where('id = ' . (int) $id);

            $db->setQuery((string) $query);
            $item = $db->loadObject();

            if (!is_object($item)) {
                continue;
            }

            $query->clear();
            $query->select('c.' . $k)
                  ->from($db->quoteName($this->_tbl) . 'AS c')
                  ->where($db->quoteName('c.lft') . ' >= ' . (int) $item->lft)
                  ->where($db->quoteName('c.rgt') . ' <= ' . (int) $item->rgt)
                  ->where($db->quoteName('c.item_id') . ' = ' . (int) $item->item_id)
                  ->where($db->quoteName('c.context') . ' = ' . $db->quote($item->context));

            $db->setQuery((string) $query);
            $ids = (array) $db->loadColumn();

            $query->clear();

            if (count($ids)) {
                $children = array_merge($children, $ids);
            }
        }


        if (count($children)) {
            $pks = array_merge($pks, $children);
        }

        // Build the WHERE clause for the primary keys.
        if (count($pks) == 1) {
            $where = $k . ' = ' . $pks[0];
        }
        else {
            $where = $k . ' IN(' . implode(', ', $pks) . ')';
        }

        // Determine if there is checkin support for the table.
        if (!property_exists($this, 'checked_out') || !property_exists($this, 'checked_out_time')) {
            $checkin = '';
        }
        else {
            $checkin = ' AND (checked_out = 0 OR checked_out = '.(int) $uid.')';
        }

        // Update the state for rows with the given primary keys.
        $query->clear();
        $query->update($this->_db->quoteName($this->_tbl))
              ->set($this->_db->quoteName('state').' = '.(int) $state)
              ->where($where);

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
            foreach($pks as $pk) {
                $this->checkin($pk);
            }
        }

        // If the JTable instance value is in the list of primary keys that were set, set the instance.
        if (in_array($this->$k, $pks)) $this->state = $state;
        $this->setError('');

        return true;
    }


    /**
     * Overridden JTable::store to set created/modified and user id.
     *
     * @param     boolean    $updateNulls    True to update fields even if they are null.
     *
     * @return    boolean                    True on success.
     */
    public function store($updateNulls = false)
    {
        $date = JFactory::getDate();
        $user = JFactory::getUser();

        if ($this->id) {
            // Existing category
            $this->modified    = $date->toSql();
            $this->modified_by = $user->get('id');
        }
        else {
            // New category
            $this->created    = $date->toSql();
            $this->created_by = $user->get('id');
        }
        //return false;

        return parent::store($updateNulls);
    }


    public function publish($pks = null, $state = 1, $userId = 0)
    {
        return $this->setState($pks, $state, $userId);
    }


    /**
     * Converts record to XML
     *
     * @param     boolean    $mapKeysToText    Map foreign keys to text values
     * @return    string                       Record in XML format
     */
    public function toXML($mapKeysToText = false)
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
