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


class PFtasksHelper
{
    /**
     * The component name
     *
     * @var    string    
     */
    public static $extension = 'com_pftasks';


    /**
     * Configure the Linkbar.
     *
     * @param     string    $view    The name of the active view.
     *
     * @return    void               
     */
    public static function addSubmenu($view)
    {
        $components = PFApplicationHelper::getComponents();
        $option     = JFactory::getApplication()->input->get('option');

        foreach ($components AS $component)
        {
            if ($component->enabled == '0') {
                continue;
            }

            $title = JText::_($component->element);
            $parts = explode('-', $title, 2);

            if (count($parts) == 2) {
                $title = trim($parts[1]);
            }

            JSubMenuHelper::addEntry(
                $title,
                'index.php?option=' . $component->element,
                ($option == $component->element)
            );

            if ($option == $component->element) {
                JSubMenuHelper::addEntry(
                    JText::_('COM_PROJECTFORK_SUBMENU_TASKLISTS'),
                    'index.php?option=' . $component->element . '&view=tasklists',
                    ($option == $component->element && $view == 'tasklists')
                );
            }
        }
    }


    /**
     * Gets a list of actions that can be performed.
     *
     * @param     integer    $id      The item id
     * @param     integer    $list    The list id
     *
     * @return    jobject             
     */
    public static function getActions($id = 0, $list = 0)
    {
        $user   = JFactory::getUser();
        $result = new JObject;

        if ((int) $id > 0) {
            $asset = 'com_pftasks.task.' . (int) $id;
        }
        elseif ((int) $list > 0) {
            $asset = 'com_pftasks.tasklist.' . (int) $list;
        }
        else {
            $asset = self::$extension;
        }

        $actions = array(
            'core.admin', 'core.manage',
            'core.create', 'core.edit',
            'core.edit.own', 'core.edit.state',
            'core.delete'
        );

        foreach ($actions as $action)
        {
            $result->set($action, $user->authorise($action, $asset));
        }

        return $result;
    }


    /**
     * Gets a list of actions that can be performed on a task list.
     *
     * @param     integer    $id    The item id
     *
     * @return    jobject           
     */
    public static function getListActions($id = 0)
    {
        $user   = JFactory::getUser();
        $result = new JObject;

        if ((int) $id > 0) {
            $asset = 'com_pftasks.tasklist.' . (int) $id;
        }
        else {
            $asset = self::$extension;
        }

        $actions = array(
            'core.admin', 'core.manage',
            'core.create', 'core.edit',
            'core.edit.own', 'core.edit.state',
            'core.delete'
        );

        foreach ($actions as $action)
        {
            $result->set($action, $user->authorise($action, $asset));
        }

        return $result;
    }
}
