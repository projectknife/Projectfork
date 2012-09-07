<?php
/**
* @package      Projectfork Dashboard Buttons
*
* @author       Tobias Kuhn (eaxs)
* @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
* @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
**/

defined('_JEXEC') or die();


/**
 * Module helper class
 *
 */
abstract class modPFdashButtonsHelper
{
    /**
     * Method to get a list of available buttons
     *
     * @return    array    $buttons    The available buttons
     */
    public static function getButtons()
    {
        $access  = ProjectforkHelperAccess::getActions(NULL, 0, true);
        $buttons = array();

        if ($access->get('project.create')) {
            $buttons['projectform.add'] = array('label' => 'MOD_PF_DASH_BUTTONS_ADD_PROJECT',
                                                'link'  => ProjectforkHelperRoute::getProjectsRoute());
        }

        if ($access->get('milestone.create')) {
            $buttons['milestoneform.add'] = array('label' => 'MOD_PF_DASH_BUTTONS_ADD_MILESTONE',
                                                  'link'  => ProjectforkHelperRoute::getMilestonesRoute());
        }

        if ($access->get('tasklist.create')) {
            $buttons['tasklistform.add'] = array('label' => 'MOD_PF_DASH_BUTTONS_ADD_TASKLIST',
                                                 'link'  => ProjectforkHelperRoute::getTasksRoute());
        }

        if ($access->get('task.create')) {
            $buttons['taskform.add'] = array('label' => 'MOD_PF_DASH_BUTTONS_ADD_TASK',
                                             'link'  => ProjectforkHelperRoute::getTasksRoute());
        }

        return $buttons;
    }
}
