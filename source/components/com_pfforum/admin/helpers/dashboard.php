<?php
/**
 * @package      Projectfork
 * @subpackage   Forum
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
abstract class PFforumHelperDashboard
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

        if ($user->authorise('core.create', 'com_pfforum')) {
            $buttons[] = array(
                'title' => 'MOD_PF_DASH_BUTTONS_ADD_TOPIC',
                'link'  => PFforumHelperRoute::getTopicsRoute() . '&task=topicform.add',
                'icon'  => JHtml::image('com_projectfork/projectfork/header/icon-48-topicform.add.png', JText::_('MOD_PF_DASH_BUTTONS_ADD_TOPIC'), null, true)
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

        if ($user->authorise('core.manage', 'com_pfforum')) {
            $buttons[] = array(
                'title' => 'COM_PROJECTFORK_SUBMENU_FORUM',
                'link'  => 'index.php?option=com_pfforum',
                'icon'  => JHtml::image('com_projectfork/projectfork/header/icon-48-forum.png', JText::_('COM_PROJECTFORK_SUBMENU_FORUM'), null, true)
            );
        }

        return $buttons;
    }
}