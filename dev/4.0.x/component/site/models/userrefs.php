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

jimport('joomla.application.component.modellist');


/**
 * Methods supporting a list of user references.
 *
 */
class ProjectforkModelUserRefs extends JModelList
{
	/**
	 * Constructor
	 *
	 * @param	array	An optional associative array of configuration settings.
	 * @see		JController
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);
	}


    /**
	 * Method to auto-populate the model state.
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @return	void
	 */
	protected function populateState($ordering = 'title', $direction = 'ASC')
	{
		// Item type
        $value = str_replace('form', '', JRequest::getCmd('view', 'taskform'));
        $this->setState('item.type', $value);

        // Item id
        $value = JRequest::getInt('id');
        $this->setState('item.id');
	}


	/**
	 * Method to get a list of user references.
	 *
	 * @return	mixed	An array of data items on success, false on failure.
	 */
	public function getItems($item_type, $item_id)
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true);
        $items = array();

        $query->select('a.id, a.user_id, u.username, u.name')
              ->from('#__pf_ref_users AS a')
              ->join('INNER', '#__users AS u ON u.id = a.user_id')
              ->where('a.item_type = '.$db->quote($item_type))
              ->where('a.item_id = '.$db->quote($item_id));

        $db->setQuery($query->__toString());
        $items = (array) $db->loadObjectList();

		return $items;
	}


    public function store($users, $item_type = null, $item_id = 0)
    {
        $db = $this->getDbo();

        if(is_null($item_type) || $item_type == '') {
            $item_type = $this->getState('item.type');
        }

        if((int) $item_id == 0) {
            $item_type = (int) $this->getState('item.id');
        }

        if($item_id == 0) {
            $this->setError('COM_PROJECTFORK_ERROR_USER_REFERENCE_ID');
            return false;
        }

        if(!is_array($users) || !count($users)) {
            $this->setError('COM_PROJECTFORK_ERROR_EMPTY_USER_REFERENCE');
            return false;
        }

        $list   = $this->getItems($item_type, $item_id);
        $stored = array();

        foreach($list AS $ref)
        {
            $stored[] = (int) $ref->user_id;
        }

        foreach($users AS $user)
        {
            $uid   = (int) $user;
            $query = $db->getQuery(true);

            if(!$uid || in_array($uid, $stored)) continue;


            $query->insert('#__pf_ref_users');
            $query->values('NULL, '.$db->quote($item_type).', '.$db->quote($item_id).', '.$db->quote($uid));

            $db->setQuery($query->__toString());
            $db->query();

            if($db->getError()) {
                $this->setError('COM_PROJECTFORK_ERROR_DATABASE_USER_REF_STORE_FAILED');
                return false;
            }

            $stored[] = $uid;
        }

        return true;
    }
}
