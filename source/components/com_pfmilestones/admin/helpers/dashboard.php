<?php
/**
 * @package      Projectfork
 * @subpackage   Milestones
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
abstract class PFmilestonesHelperDashboard
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

        if ($user->authorise('core.create', 'com_pfmilestones')) {
            $buttons[] = array(
                'title' => 'MOD_PF_DASH_BUTTONS_ADD_MILESTONE',
                'link'  => PFmilestonesHelperRoute::getMilestonesRoute() . '&task=form.add',
                'icon'  => JHtml::image('com_projectfork/projectfork/header/icon-48-milestoneform.add.png', JText::_('MOD_PF_DASH_BUTTONS_ADD_MILESTONE'), null, true)
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

        if ($user->authorise('core.manage', 'com_pfmilestones')) {
            $buttons[] = array(
                'title' => 'COM_PROJECTFORK_SUBMENU_MILESTONES',
                'link'  => 'index.php?option=com_pfmilestones',
                'icon'  => JHtml::image('com_projectfork/projectfork/header/icon-48-milestones.png', JText::_('COM_PROJECTFORK_SUBMENU_MILESTONES'), null, true)
            );
        }

        return $buttons;
    }
}