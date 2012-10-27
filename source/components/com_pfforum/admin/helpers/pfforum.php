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


class PFforumHelper
{
    /**
     * The component name
     *
     * @var    string
     */
    public static $extension = 'com_pfforum';


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

            JSubMenuHelper::addEntry(
                JText::_($component->element),
                'index.php?option=' . $component->element,
                ($option == $component->element)
            );
        }
    }


    /**
     * Gets a list of actions that can be performed on a topic.
     *
     * @param     integer    $id         The item id
     * @param     integer    $project    The project id
     *
     * @return    jobject
     */
    public static function getActions($id = 0, $project = 0)
    {
        $user   = JFactory::getUser();
        $result = new JObject;

        if ((empty($id) || $id == 0) && (empty($project) || $project == 0)) {
            $asset = self::$extension;
        }
        elseif (empty($id) || $id == 0) {
            $asset = 'com_pfprojects.project.' . (int) $project;
        }
        else {
            $asset = 'com_pfforum.topic.' . (int) $id;
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
     * Gets a list of actions that can be performed on a reply.
     *
     * @param     integer    $id         The item id
     * @param     integer    $topic      The topic id
     *
     * @return    jobject
     */
    public static function getReplyActions($id = 0, $topic = 0)
    {
        $user   = JFactory::getUser();
        $result = new JObject;

        if ((empty($id) || $id == 0) && (empty($topic) || $topic == 0)) {
            $asset = self::$extension;
        }
        elseif (empty($id) || $id == 0) {
            $asset = 'com_pfforum.topic.' . (int) $topic;
        }
        else {
            $asset = 'com_pfforum.reply.' . (int) $id;
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
