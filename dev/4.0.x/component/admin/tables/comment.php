<?php
/**
* @package   Projectfork
* @copyright Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
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
 * Project table
 *
 */
class PFTableComment extends JTable
{
	/**
	 * Constructor
	 *
	 * @param    database    &$db    A database connector object
	 * @return   JTableProject
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
	 * @return  string
	 */
	protected function _getAssetName()
	{
		$k = $this->_tbl_key;
		return 'com_projectfork.comment.'.(int) $this->$k;
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
	 * Get the parent asset id for the record
	 *
	 * @param   JTable   $table  A JTable object for the asset parent.
	 * @param   integer  $id     The id for the asset
	 *
	 * @return  integer  The id of the asset's parent
	 */
	protected function _getAssetParentId($table = null, $id = null)
	{
		// Initialise variables.
		$assetId = null;
        $query   = $this->_db->getQuery(true);


		// Build the query to get the asset id for the parent category.
		$query->select($this->_db->quoteName('id'))
		      ->from($this->_db->quoteName('#__assets'))
			  ->where($this->_db->quoteName('name') . ' = ' . $this->_db->quote("com_projectfork"));


		// Get the asset id from the database.
		$this->_db->setQuery($query);
		if ($result = $this->_db->loadResult())$assetId = (int) $result;

		// Return the asset id.
		if ($assetId) return $assetId;

		return parent::_getAssetParentId($table, $id);
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

        if (isset($array['description']) && is_array($array['description'])) {
            if (isset($array['parent_id']) && array_key_exists($array['parent_id'], $array['description'])) {
                $key = $array['parent_id'];
                $array['description'] = $array['description'][$key];
            }
            else {
                $array['description'] = '';
            }
        }

        if (!isset($array['state'])) {
            $array['state'] = 1;
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
        if (trim($this->description) == '') {
			$this->setError(JText::_('COM_PROJECTFORK_WARNING_PROVIDE_VALID_TITLE'));
			//return false;
		}

        // Check attribs
        $registry = new JRegistry;
		$registry->loadJSON($this->attribs);

        $this->attribs = (string) $registry;


		return true;
	}


    /**
	 * Method to recursively rebuild the nested set tree.
	 *
	 * @param   integer  $parent_id  The root of the tree to rebuild.
	 * @param   integer  $left       The left id to start with in building the tree.
	 *
	 * @return  boolean  True on success
	 *
	 * @since   11.1
	 */
	public function rebuild($context, $item_id, $parent_id = 0, $left = 0)
	{
		// Get the database object
		$db    = &$this->_db;
        $query = $db->getQuery(true);

        $query->select('id')
              ->from($this->_tbl)
              ->where('context   = '. $db->quote($context))
              ->where('parent_id = ' . (int) $parent_id)
              ->where('item_id   = '. (int) $item_id)
              ->order('parent_id, created');

		// Get all children of this node
		$db->setQuery($query->__toString());
		$children = $db->loadColumn();

		// The right value of this node is the left value + 1
		$right = $left + 1;

		// Execute this function recursively over all children
		for ($i = 0, $n = count($children); $i < $n; $i++)
		{
			// $right is the current right value, which is incremented on recursion return
			$right = $this->rebuild($context, $item_id, $children[$i], $right);

			// Ff there is an update failure, return false to break out of the recursion
			if ($right === false)
			{
				return false;
			}
		}

		// We've got the left value, and now that we've processed
		// the children of this node we also know the right value
        $query = $db->getQuery(true);

        $query->update($this->_tbl)
              ->set('lft = ' . (int) $left)
              ->set('rgt = ' . (int) $right)
              ->where('id   = '. $parent_id);

		$db->setQuery($query->__toString());

		// If there is an update failure, return false to break out of the recursion
		if (!$db->query()) {
			return false;
		}

		// return the right value of this node + 1
		return $right + 1;
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
			// New item. A project created_by field can be set by the user,
			// so we don't touch it if set.
			$this->created = $date->toMySQL();
			if (empty($this->created_by)) $this->created_by = $user->get('id');
		}

		$result = parent::store($updateNulls);

        if($result) {
            $this->rebuild($this->context, $this->item_id);
        }

        return $result;
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

        // Get all comment children
        $children = array();

        foreach($pks AS $id)
        {
            $db    = $this->_db;
            $query = $db->getQuery(true);

            $query->select('id, lft, rgt, context, item_id')
                  ->from($db->quoteName($this->_tbl))
                  ->where('id = ' . (int) $id);

            $db->setQuery($query->__toString());
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

    		$db->setQuery($query->__toString());
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
		} else {
			$checkin = ' AND (checked_out = 0 OR checked_out = '.(int) $userId.')';
		}

		// Update the state for rows with the given primary keys.
		$this->_db->setQuery(
			'UPDATE '.$this->_db->quoteName($this->_tbl).
			' SET '.$this->_db->quoteName('state').' = '.(int) $state .
			' WHERE '. $where .
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