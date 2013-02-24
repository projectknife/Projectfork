<?php
/**
 * @package      Projectfork
 * @subpackage   Tasks
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2013 Tobias Kuhn. All rights reserved.
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
        $is_j3 = version_compare(JVERSION, '3.0.0', 'ge');
        $forms = array('task', 'tasklist');

        if (in_array($view, $forms) && $is_j3) return;

        $components = PFApplicationHelper::getComponents();
        $option     = JFactory::getApplication()->input->get('option');
        $class      = ($is_j3 ? 'JHtmlSidebar' : 'JSubMenuHelper');

        foreach ($components AS $component)
        {
            if ($component->enabled == '0') continue;

            $title = JText::_($component->element);
            $parts = explode('-', $title, 2);

            if (count($parts) == 2) $title = trim($parts[1]);

            $class::addEntry(
                $title,
                'index.php?option=' . $component->element,
                ($option == $component->element && $view == 'tasks')
            );

            if ($option == $component->element) {
                $class::addEntry(
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


    static public function priority2string($value = null)
    {
        switch((int) $value)
        {
            case 2:
                $text  = JText::_('COM_PROJECTFORK_PRIORITY_LOW');
                break;

            case 3:
                $text  = JText::_('COM_PROJECTFORK_PRIORITY_MEDIUM');
                break;

            case 4:
                $text  = JText::_('COM_PROJECTFORK_PRIORITY_HIGH');
                break;

            case 5:
                $text  = JText::_('COM_PROJECTFORK_PRIORITY_VERY_HIGH');
                break;

            default:
            case 1:
                $text  = JText::_('COM_PROJECTFORK_PRIORITY_VERY_LOW');
                break;
        }

        return $text;
    }
}
