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
}
