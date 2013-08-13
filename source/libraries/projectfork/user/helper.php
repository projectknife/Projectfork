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


/**
 * Projectfork User Helper Class
 *
 */
abstract class PFUserHelper
{
    /**
     * Projects the user is allowed to access
     *
     * @var    array
     */
    protected static $allowed_projects;


    /**
     * Returns a list of project id's the user is allowed to access
     *
     * @param    integer $uid Optional user id
     *
     * @return    array
     */
    protected static $routes;


    public function getAuthorisedProjects($uid = null)
    {
        $cache_key  = (int) $uid;

        // Check the cache
        if (isset(self::$allowed_projects[$cache_key])) {
            return self::$allowed_projects[$cache_key];
        }

        // Not yet cached
        $levels = JFactory::getUser($uid)->getAuthorisedViewLevels();
        $db     = JFactory::getDbo();
        $query  = $db->getQuery(true);

        $query->select('id')
              ->from('#__pf_projects')
              ->where('access IN(' . implode(', ', $levels)  . ')')
              ->order('id ASC');

        $db->setQuery($query);
        $result = $db->loadColumn();

        if (empty($result)) $result = array();

        self::$allowed_projects[$cache_key] = $result;

        return self::$allowed_projects[$cache_key];
    }
}
