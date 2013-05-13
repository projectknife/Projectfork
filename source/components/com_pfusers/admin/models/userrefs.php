<?php
/**
 * @package      Projectfork
 * @subpackage   Users
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


jimport('joomla.application.component.modellist');


/**
 * Methods supporting a list of user references.
 *
 */
class PfusersModelUserRefs extends JModelList
{
    /**
     * Method to get a list of user references.
     *
     * @return    mixed    An array of data items on success, false on failure.
     */
    public function getItems($item_type = null, $item_id = 0)
    {
        $db    = $this->getDbo();
        $query = $db->getQuery(true);
        $items = array();

        $query->select('a.id, a.user_id, u.username, u.name')
              ->from('#__pf_ref_users AS a')
              ->join('INNER', '#__users AS u ON u.id = a.user_id')
              ->where('a.item_type = ' . $db->quote($item_type))
              ->where('a.item_id = ' . (int) $item_id);

        $db->setQuery((string) $query);
        $items = (array) $db->loadObjectList();

        return $items;
    }


    public function store($users, $item_type = null, $item_id = 0)
    {
        $db = $this->getDbo();

        if (is_null($item_type) || $item_type == '') {
            $item_type = $this->getState('item.type');
        }

        if ((int) $item_id == 0) {
            $item_type = (int) $this->getState('item.id');
        }

        if ($item_id == 0) {
            $this->setError('COM_PROJECTFORK_ERROR_USER_REFERENCE_ID');
            return false;
        }

        if (!is_array($users) || !count($users)) {
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

            if (!$uid || in_array($uid, $stored)) continue;


            $query->insert('#__pf_ref_users');
            $query->values('NULL, ' . $db->quote($item_type) . ', ' . $db->quote($item_id) . ', ' . $db->quote($uid));

            $db->setQuery((string) $query);
            $db->query();

            if ($db->getError()) {
                $this->setError('COM_PROJECTFORK_ERROR_DATABASE_USER_REF_STORE_FAILED');
                return false;
            }

            $stored[] = $uid;
        }

        return true;
    }


    /**
     * Method to auto-populate the model state.
     * Note. Calling getState in this method will result in recursion.
     *
     * @return    void
     */
    protected function populateState($ordering = 'title', $direction = 'ASC')
    {
        // Item type
        $value = str_replace('form', '', JRequest::getCmd('view', 'taskform'));
        $this->setState('item.type', $value);

        // Item id
        $value = JRequest::getUint('id');
        $this->setState('item.id');
    }
}
