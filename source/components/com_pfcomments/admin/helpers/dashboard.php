<?php
/**
 * @package      Projectfork
 * @subpackage   Comments
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
abstract class PFcommentsHelperDashboard
{

    /**
     * Returns a list of buttons for the backend
     *
     * @return    array
     */
    public static function getAdminButtons()
    {
        $user    = JFactory::getUser();
        $buttons = array();

        if ($user->authorise('core.manage', 'com_pfcomments')) {
            $buttons[] = array(
                'title' => 'COM_PROJECTFORK_SUBMENU_COMMENTS',
                'link'  => 'index.php?option=com_pfcomments',
                'icon'  => JHtml::image('com_projectfork/projectfork/header/icon-48-comments.png', JText::_('COM_PROJECTFORK_SUBMENU_COMMENTS'), null, true)
            );
        }

        return $buttons;
    }
}