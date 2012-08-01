<?php
/**
 * @package      Projectfork
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
class ProjectforkModelUserRefs extends JModelList
{
    /**
     * Constructor
     *
     * @param    array          An optional associative array of configuration settings.
     * @see      jcontroller    
     */
    public function __construct($config = array())
    {
        parent::__construct($config);
    }


    /**
     * Method to get a list of user references.
     *
     * @param     string     The item type
     * @param     integer    The item id
     * @return    mixed      An array of data items on success, false on failure.
     */
    public function getItems($item_type, $item_id)
    {
        $db    = $this->getDbo();
        $query = $db->getQuery(true);
        $items = array();

        $query->select('a.id, a.user_id, u.username, u.name')
              ->from('#__pf_ref_users AS a')
              ->join('INNER', '#__users AS u ON u.id = a.user_id')
              ->where('a.item_type = ' . $db->quote($item_type))
              ->where('a.item_id = ' . $db->quote($item_id));

        $db->setQuery((string) $query);
        $items = (array) $db->loadObjectList();

        return $items;
    }
}
