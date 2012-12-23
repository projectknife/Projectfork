<?php
/**
 * @package      Projectfork
 * @subpackage   Time
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


/**
 * Dashboard Helper Class
 *
 */
abstract class PFtimeHelperDashboard
{
    /**
     * Returns a list of buttons for the frontend
     *
     * @return    array
     */
    public static function getSiteButtons()
    {
        $user    = JFactory::getUser();
        $buttons = array();

        if ($user->authorise('core.create', 'com_pftime')) {
            $buttons[] = array(
                'title' => 'MOD_PF_DASH_BUTTONS_ADD_TIME',
                'link'  => PFtimeHelperRoute::getTimesheetRoute() . '&task=form.add',
                'icon'  => JHtml::image('com_projectfork/projectfork/header/icon-48-timeform.add.png', JText::_('MOD_PF_DASH_BUTTONS_ADD_TIME'), null, true)
            );
        }

        return $buttons;
    }


    /**
     * Returns a list of buttons for the backend
     *
     * @return    array
     */
    public static function getAdminButtons()
    {
        $user    = JFactory::getUser();
        $buttons = array();

        if ($user->authorise('core.manage', 'com_pftime')) {
            $buttons[] = array(
                'title' => 'COM_PROJECTFORK_SUBMENU_TIME_TRACKING',
                'link'  => 'index.php?option=com_pftime',
                'icon'  => JHtml::image('com_projectfork/projectfork/header/icon-48-time.png', JText::_('COM_PROJECTFORK_SUBMENU_TIME_TRACKING'), null, true)
            );
        }

        return $buttons;
    }
}