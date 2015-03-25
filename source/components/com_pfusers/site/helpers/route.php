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


jimport('joomla.application.component.helper');


/**
 * Component Route Helper
 *
 * @static
 */
abstract class PFusersHelperRoute
{
    /**
     * Creates a link to the users overview
     *
     * @param     string    $project    The project slug. Optional
     *
     * @return    string    $link       The link
     */
    public static function getUsersRoute($project = '')
    {
        $link  = 'index.php?option=com_pfusers&view=users';
        $link .= '&filter_project=' . $project;

        $needles = array('filter_project' => array((int) $project)
                        );

        if ($item = PFApplicationHelper::itemRoute($needles, 'com_pfusers.users')) {
            $link .= '&Itemid=' . $item;
        }
        elseif ($item = PFApplicationHelper::itemRoute(null, 'com_pfusers.users')) {
            $link .= '&Itemid=' . $item;
        }

        return $link;
    }


    /**
     * Creates a link to a user item view
     *
     * @param     string    $id      The user slug
     *
     * @return    string    $link    The link
     */
    public static function getUserRoute($id)
    {
        static $dest = null;

        if (is_null($dest)) {
            $params = JComponentHelper::getParams('com_projectfork');

            $dest = $params->get('user_profile_link');
        }

        $link = null;

        switch ($dest)
        {
            case 'cb':
                $link = self::getCBRoute($id);
                break;

            case 'js':
                $link = self::getJSRoute($id);
                break;

            case 'kunena':
                $link = self::getKRoute($id);
                break;
        }

        if (!empty($link)) {
            return $link;
        }

        // Default - Projectfork Profile
        $link  = 'index.php?option=com_pfusers&view=user';
        $link .= '&id=' . $id;

        $needles = array('id' => array((int) $id));

        if ($item = PFApplicationHelper::itemRoute($needles, 'com_pfusers.user')) {
            $link .= '&Itemid=' . $item;
        }
        elseif ($item = PFApplicationHelper::itemRoute(null, 'com_pfusers.users')) {
            $link .= '&Itemid=' . $item;
        }

        return $link;
    }


    /**
     * Method to link to a Community Builder user profile page
     *
     * @param     integer    $id      The user slug
     *
     * @return    string              The profile url
     */
    protected static function getCBRoute($id)
    {
        static $itemid = null;

        // Try to find a suitable menu item
        if (is_null($itemid)) {
            $app	= JFactory::getApplication();
			$menu	= $app->getMenu();
			$com	= JComponentHelper::getComponent('com_comprofiler');

            if (empty($com) || !isset($com->id)) {
                $itemid = 0;
                return null;
            }

			$items	= $menu->getItems('component_id', $com->id);

            $profile_id = 0;
            $cb_id = 0;

			// If no items found, set to empty array.
			if (!$items) $items = array();

            foreach ($items as $item)
            {
                if (!isset($item->query['task']) || $item->query['task'] == 'userProfile') {
    				$itemid = $item->id;
    				break;
    			}
            }

            if (!$itemid) $itemid = 0;
        }

        // Return null if item id was not found
        if (!$itemid) return null;

        // Return link
        return 'index.php?option=com_comprofiler&task=userProfile&user=' . intval($id) . ($itemid ? '&Itemid=' . $itemid : '');
    }


    /**
     * Method to link to a JomSocial user profile page
     *
     * @param     integer    $id      The user id
     *
     * @return    string              The profile url
     */
    protected static function getJSRoute($id)
    {
        static $exists = null;

        // Include the route helper once
        if (is_null($exists)) {
            $file   = JPATH_SITE . '/components/com_community/helpers/url.php';
            $exists = file_exists($file);

            if ($exists) {
                require_once $file;

                $file   = JPATH_ROOT . '/components/com_community/libraries/core.php';
                $exists = file_exists($file);

                if ($exists) require_once $file;
            }
        }

        // Return null if router was not found
        if (!$exists) return null;

        // Return link
        return CUrlHelper::userLink((int) $id, true);
    }


    /**
     * Method to link to a Kunena user profile page
     *
     * @param     integer    $id      The user id
     *
     * @return    string              The profile url
     */
    protected static function getKRoute($id)
    {
        static $router = null;

        // Include the route helper once
        if (is_null($router)) {
            $file = JPATH_SITE . '/components/com_kunena/lib/kunena.link.class.php';

            if (!file_exists($file)) {
                $router = false;
            }
            else {
                require_once $file;
                $router = true;
            }
        }

        // Return null if router was not found
        if (!$router) return null;

        // Return link
        return CKunenaLink::GetMyProfileURL((int) $id);
    }
}
