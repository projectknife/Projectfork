<?php
/**
* @package   Projectfork
* @copyright Copyright (C) 2006-2011 Tobias Kuhn. All rights reserved.
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL, see license.txt
*
* This file is part of Projectfork.
*
* Projectfork is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
*
* Projectfork is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with Projectfork. If not, see <http://www.gnu.org/licenses/gpl.html>.
**/

// No direct access
defined('_JEXEC') or die;

jimport('joomla.database.tableasset');

/**
 * Milestone table
 *
 */
class JTableMilestone extends JTable
{
	/**
	 * Constructor
	 *
	 * @param    database    &$db    A database connector object
	 * @return   JTableProject
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
	 * @return  string
	 */
	protected function _getAssetName()
	{
		$k = $this->_tbl_key;
		return 'com_projectfork.milestone.'.(int) $this->$k;
	}


	/**
	 * Method to return the title to use for the asset table.
	 *
	 * @return  string
	 */
	protected function _getAssetTitle()
	{
		return $this->title;
	}


    /**
	 * Method to get the parent asset id for the record
	 *
	 * @param   JTable   $table  A JTable object for the asset parent
	 * @param   integer  $id
	 * @return  integer
	 */
	protected function _getAssetParentId($table = null, $id = null)
	{
		// Initialise variables.
		$assetId = null;
		$db = $this->getDbo();


		// This is a milestone under a project.
		if ($this->project_id) {
			// Build the query to get the asset id for the parent project.
			$query = $db->getQuery(true);

			$query->select('asset_id');
			$query->from('#__pf_projects');
			$query->where('id = '.(int) $this->project_id);

			// Get the asset id from the database.
			$this->_db->setQuery($query);
			if ($result = $this->_db->loadResult()) $assetId = (int) $result;
		}


		// Return the asset id.
		if ($assetId) return $assetId;
		return parent::_getAssetParentId($table, $id);
	}


    /**
	 * Method to get the project access level id
	 *
	 * @return  integer
	 */
    protected function _getAccessProjectId()
    {
        if((int) $this->project_id == 0) return 1;

        $db    = $this->getDbo();
        $query = $db->getQuery(true);

        $query->select('access');
		$query->from('#__pf_projects');
		$query->where('id = '.(int) $this->project_id);

        $db->setQuery($query);
        $access = (int) $db->loadResult();

        if(!$access) $access = 1;

        return $access;
    }


	/**
	 * Overloaded bind function
	 *
	 * @param   array  $array   Named array
	 * @param   mixed  $ignore  An optional array or space separated list of properties
	 *                          to ignore while binding.
	 * @return  mixed  Null if operation was satisfactory, otherwise returns an error string
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
	 * @return  boolean  True on success, false on failure
	 */
	public function check()
	{
		if (trim($this->title) == '') {
			$this->setError(JText::_('COM_PROJECTFORK_WARNING_PROVIDE_VALID_TITLE'));
			return false;
		}

		if (trim($this->alias) == '') $this->alias = $this->title;
		$this->alias = JApplication::stringURLSafe($this->alias);
		if (trim(str_replace('-','',$this->alias)) == '') $this->alias = JFactory::getDate()->format('Y-m-d-H-i-s');


		if (trim(str_replace('&nbsp;', '', $this->description)) == '') $this->description = '';


        // Check if a project is selected
        if((int) $this->project_id == 0) {
            $this->setError(JText::_('COM_PROJECTFORK_WARNING_SELECT_PROJECT'));
			return false;
        }


        // Check for selected access level
        if($this->access == 0) $this->access = $this->_getAccessProjectId();


		// Check the start date is not earlier than the end date.
		if ($this->end_date > $this->_db->getNullDate() && $this->end_date < $this->start_date) {
			// Swap the dates
			$temp = $this->start_date;
			$this->start_date = $this->end_date;
			$this->end_date = $temp;
		}


        // Check if the start and end dates are in bounds of the parent dates
        $project = JTable::getInstance('project');
        $project->load((int)$this->project_id);

        $a_start = strtotime($project->start_date);
        $a_end   = strtotime($project->end_date);
        $b_start = strtotime($this->start_date);
        $b_end   = strtotime($this->end_date);

        if($a_start > $b_start) $this->start_date = $project->start_date;
        if($a_end < $b_end)     $this->end_date   = $project->end_date;


		return true;
	}


	/**
	 * Overrides JTable::store to set modified data and user id.
	 *
	 * @param   boolean  True to update fields even if they are null.
	 * @return  boolean  True on success.
	 */
	public function store($updateNulls = false)
	{
		$date = JFactory::getDate();
		$user = JFactory::getUser();

		if ($this->id) {
			// Existing item
			$this->modified		= $date->toMySQL();
			$this->modified_by	= $user->get('id');
		}
        else {
			// New item. A created_by field can be set by the user,
			// so we don't touch it if set.
			$this->created = $date->toMySQL();
			if (empty($this->created_by)) $this->created_by = $user->get('id');
		}

		// Verify that the alias is unique
		$table = JTable::getInstance('Milestone','JTable');
		if ($table->load(array('alias'=>$this->alias)) && ($table->id != $this->id || $this->id==0)) {
			$this->setError(JText::_('JLIB_DATABASE_ERROR_PROJECT_UNIQUE_ALIAS'));
			return false;
		}

		return parent::store($updateNulls);
	}

	/**
	 * Method to set the state for a row or list of rows in the database
	 * table. The method respects checked out rows by other users and will attempt
	 * to checkin rows that it can after adjustments are made.
	 *
	 * @param   mixed    $pks     An optional array of primary key values to update.  If not
	 *                            set the instance property value is used.
	 * @param   integer  $state   The state. eg. [0 = Inactive, 1 = Active, 2 = Archived, -2 = Trashed]
	 * @param   integer  $userId  The user id of the user performing the operation.
	 * @return  boolean  True on success.
	 */
	public function setState($pks = null, $state = 1, $userId = 0)
	{
		// Initialise variables.
		$k = $this->_tbl_key;


		// Sanitize input.
		JArrayHelper::toInteger($pks);
		$userId = (int) $userId;
		$state  = (int) $state;


		// If there are no primary keys set check to see if the instance key is set.
		if (empty($pks)) {
			if ($this->$k) {
				$pks = array($this->$k);
			}
			// Nothing to set state on, return false.
			else {
				$this->setError(JText::_('JLIB_DATABASE_ERROR_NO_ROWS_SELECTED'));
				return false;
			}
		}


		// Build the WHERE clause for the primary keys.
		$where = $k.'='.implode(' OR '.$k.'=', $pks);


		// Determine if there is checkin support for the table.
		if (!property_exists($this, 'checked_out') || !property_exists($this, 'checked_out_time')) {
			$checkin = '';
		} else {
			$checkin = ' AND (checked_out = 0 OR checked_out = '.(int) $userId.')';
		}


		// Update the state for rows with the given primary keys.
		$this->_db->setQuery(
			'UPDATE '.$this->_db->quoteName($this->_tbl).
			' SET '.$this->_db->quoteName('state').' = '.(int) $state .
			' WHERE ('.$where.')' .
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
	 * @see    setState
     *
	 */
    public function publish($pks = null, $state = 1, $userId = 0)
    {
        return $this->setState($pks, $state, $userId);
    }


	/**
	 * Converts record to XML
	 *
	 * @param   boolean  $mapKeysToText  Map foreign keys to text values
	 * @return  string    Record in XML format
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
?>