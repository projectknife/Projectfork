<?php
/**
 * @package      pkg_projectfork
 * @subpackage   com_pfmilestones
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2013 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


/**
 * Milestone table
 *
 */
class PFtableMilestone extends JTable
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
     *
     * @return    string
     */
    protected function _getAssetName()
    {
        $k = $this->_tbl_key;
        return 'com_pfmilestones.milestone.' . (int) $this->$k;
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

        // Get the asset id of the component
        $query->select('id')
              ->from('#__assets')
              ->where('name' . ' = ' . $this->_db->quote("com_pfmilestones"));

        // Get the asset id from the database.
        $this->_db->setQuery($query);
        $result = $this->_db->loadResult();

        if ($result) $asset_id = (int) $result;

        // Return the asset id.
        if ($asset_id) return $asset_id;

        return parent::_getAssetParentId($table, $id);
    }


    /**
     * Method to get the project access level id
     *
     * @return    integer
     */
    protected function _getParentAccess()
    {
        if ((int) $this->project_id == 0) return (int) JFactory::getConfig()->get('access');

        $db    = $this->getDbo();
        $query = $db->getQuery(true);

        $query->select('access')
              ->from('#__pf_projects')
              ->where('id = ' . (int) $this->project_id);

        $db->setQuery($query);
        $access = (int) $db->loadResult();

        if (!$access) $access = (int) JFactory::getConfig()->get('access');

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
        if ((int) $this->project_id <= 0) {
            $this->setError(JText::_('COM_PROJECTFORK_WARNING_SELECT_PROJECT'));
            return false;
        }

        // Check for selected access level
        if ($this->access <= 0) {
            $this->access = $this->_getParentAccess();
        }

        // Get the project start and end date for comparison
        $query    = $this->_db->getQuery(true);
        $nulldate = $this->_db->getNullDate();

        $query->select('start_date, end_date')
              ->from('#__pf_projects')
              ->where('id = ' . $this->_db->quote((int) $this->project_id));

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
            // New item. A created_by field can be set by the user,
            // so we don't touch it if set.
            $this->created = $date->toSql();
            if (empty($this->created_by)) $this->created_by = $user->get('id');
        }

        // Verify that the alias is unique
        $table = JTable::getInstance('Milestone', 'PFtable');

        if ($table->load(array('alias' => $this->alias, 'project_id' => $this->project_id)) && ($table->id != $this->id || $this->id==0)) {
            $this->setError(JText::_('JLIB_DATABASE_ERROR_MILESTONE_UNIQUE_ALIAS'));
            return false;
        }

        return parent::store($updateNulls);
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
        $k  = $this->_tbl_key;
        $pk = (is_null($pk)) ? $this->$k : $pk;

        // Delete related attachments
        $query = $this->_db->getQuery(true);
        $query->delete('#__pf_ref_attachments')
              ->where('item_id = ' . $this->_db->quote((int) $pk))
              ->where('item_type = ' . $this->_db->quote('com_pfmilestones.milestone'));

        $this->_db->setQuery($query);
        $this->_db->execute();

        // Delete related label references
        $query->clear();
        $query->delete('#__pf_ref_labels')
              ->where('item_id = ' . $this->_db->quote((int) $pk))
              ->where('item_type = ' . $this->_db->quote('com_pfmilestones.milestone'));

        $this->_db->setQuery($query);
        $this->_db->execute();

        // Delete related watching users
        $query->clear();
        $query->delete('#__pf_ref_observer')
              ->where('item_id = ' . $this->_db->quote((int) $pk))
              ->where('item_type = ' . $this->_db->quote('com_pfmilestones.milestone'));

        $this->_db->setQuery($query);
        $this->_db->execute();

        return true;
    }


    /**
     * Method to set the publishing state for a row or list of rows in the database
     * table.
     *
     * @param     mixed      $pks      An optional array of primary key values to update.
     * @param     integer    $state    The publishing state
     * @param     integer    $uid      The user id of the user performing the operation.
     *
     * @return    boolean              True on success.
     */
    public function publish($pks = null, $state = 1, $uid = 0)
    {
        return $this->setState($pks, $state, $uid);
    }


    /**
     * Method to set the state for a row or list of rows in the database
     * table.
     *
     * @param     mixed      $pks      An optional array of primary key values to update.
     * @param     integer    $state    The state.
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

        return true;
    }
}
