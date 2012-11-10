<?php
/**
 * @package      Projectfork
 * @subpackage   Tasks
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
abstract class PFtasksHelperDashboard
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

        if ($user->authorise('core.create', 'com_pftasks')) {
            $buttons[] = array(
                'title' => 'MOD_PF_DASH_BUTTONS_ADD_TASK',
                'link'  => PFtasksHelperRoute::getTasksRoute() . '&task=taskform.add',
                'icon'  => JHtml::image('com_projectfork/projectfork/header/icon-48-taskform.add.png', JText::_('MOD_PF_DASH_BUTTONS_ADD_TASK'), null, true)
            );
            $buttons[] = array(
                'title' => 'MOD_PF_DASH_BUTTONS_ADD_TASKLIST',
                'link'  => PFtasksHelperRoute::getTasksRoute() . '&task=tasklistform.add',
                'icon'  => JHtml::image('com_projectfork/projectfork/header/icon-48-tasklistform.add.png', JText::_('MOD_PF_DASH_BUTTONS_ADD_TASKLIST'), null, true)
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

        if ($user->authorise('core.manage', 'com_pftasks')) {
            $buttons[] = array(
                'title' => 'COM_PROJECTFORK_SUBMENU_TASKS',
                'link'  => 'index.php?option=com_pftasks',
                'icon'  => JHtml::image('com_projectfork/projectfork/header/icon-48-tasks.png', JText::_('COM_PROJECTFORK_SUBMENU_TASKS'), null, true)
            );
            $buttons[] = array(
                'title' => 'COM_PROJECTFORK_SUBMENU_TASKLISTS',
                'link'  => 'index.php?option=com_pftasks&view=tasklists',
                'icon'  => JHtml::image('com_projectfork/projectfork/header/icon-48-tasklists.png', JText::_('COM_PROJECTFORK_SUBMENU_TASKLISTS'), null, true)
            );
        }

        return $buttons;
    }
}