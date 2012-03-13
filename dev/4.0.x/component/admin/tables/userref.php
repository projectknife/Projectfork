<?php
/**
* @package   Projectfork
* @copyright Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
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
 * User Reference table
 *
 */
class PFTableUserRef extends JTable
{
	/**
	 * Constructor
	 *
	 * @param    database    &$db    A database connector object
	 * @return   JTableProject
	 */
	public function __construct(&$db)
	{
		parent::__construct('#__pf_ref_users', 'id', $db);
	}


	/**
	 * Overrides JTable::store to set modified data and user id.
	 *
	 * @param   boolean  True to update fields even if they are null.
	 * @return  boolean  True on success.
	 */
	public function store($updateNulls = false)
	{
		// Verify that the reference is unique
		$table = JTable::getInstance('UserRef','PFTable');
		if ($table->load(array('item_type'=>$this->item_type, 'item_id'=>$this->item_id, 'user_id'=>$this->user_id)) && ($table->id != $this->id || $this->id==0)) {
            return true;
		}

		return parent::store($updateNulls);
	}



	/**
	 * Converts record to XML
	 *
	 * @param   boolean  $mapKeysToText  Map foreign keys to text values
	 * @return  string    Record in XML format
	 */
	public function toXML($mapKeysToText=false)
	{
		$db = JFactory::getDbo();

		if ($mapKeysToText) {
			$query = 'SELECT name'
			. ' FROM #__users'
			. ' WHERE id = ' . (int) $this->user_id;
			$db->setQuery($query);
			$this->user_name = $db->loadResult();
		}

		return parent::toXML($mapKeysToText);
	}
}