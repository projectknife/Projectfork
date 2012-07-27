<?php
/**
 * @package      Projectfork
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


/**
 * Projectfork Access Helper Class
 *
 */
class ProjectforkHelperAccess
{
    /**
     * Calculates and returns all available actions for the given asset
     *
     * @param     string     $asset_name    Optional asset item name
     * @param     integer    $asset_id      Optional asset id
     *
     * @return    object
     */
    public static function getActions($asset_name = NULL, $asset_id = 0)
    {
        static $results = array();

        $asset  = 'com_projectfork';

        if ($asset_name) $asset .= '.' . $asset_name;
        if ($asset_id)   $asset .= '.' . $asset_id;

        if (array_key_exists($asset, $results)) {
            // Return cached result
            return $results[$asset];
        }
        else {
            // Actions for this asset not in cache yet
            $result  = new JObject;
            $user    = JFactory::getUser();
            $assets  = array('core', 'project', 'milestone', 'tasklist', 'task', 'comment');
            $actions = array('create', 'edit', 'edit.own', 'edit.state', 'delete');

            $auth_admin = $user->authorise('core.admin',  $asset);

            $result->set('core.admin',  $auth_admin);
            $result->set('core.manage', $user->authorise('core.manage', $asset));

            // Check if the asset name is given and reduce the assets to check to this one
            if ($asset_name && in_array($asset_name, $assets)) {
                $assets = array($asset_name);
            }

            foreach($assets AS $name)
            {
                foreach($actions AS $action)
                {
                    if ($name == 'core') {
                        $result->set($name . '.' . $action, $user->authorise($name . '.' . $action, $asset));
                    }
                    else {
                        // Auth non core assets against core and admin
                        $auth_1 = $user->authorise($name . '.' . $action, $asset);
                        $auth_2 = $user->authorise('core.' . $action, $asset);
                        $result->set($name . '.' . $action, ($auth_1 || $auth_2 || $auth_admin));
                    }
                }
            }

            $results[$asset] = $result;
        }

        return $results[$asset];
    }
}