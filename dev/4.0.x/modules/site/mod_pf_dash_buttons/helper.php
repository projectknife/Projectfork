<?php
/**
* @package   Projectfork Dashboard Buttons
* @copyright Copyright (C) 2012 Tobias Kuhn. All rights reserved.
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
*
* This file is part of Projectfork.
*
* Projectfork is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
*
* Projectfork is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with Projectfork. If not, see <http://www.gnu.org/licenses/gpl.html>.
**/

// no direct access
defined('_JEXEC') or die;


abstract class modPFdashButtonsHelper
{
    public static function getButtons()
    {
        $app  = JFactory::getApplication();
        $user = JFactory::getUser();
        $pid  = $app->getUserState('com_projectfork.project.active.id', 0);

        $asset  = 'com_projectfork';

        if($pid) $asset .= '.project.'.$pid;


        $buttons = array();

        if($user->authorise('core.create', 'com_projectfork') || $user->authorise('project.create', 'com_projectfork')) {
            $buttons['projectform.add'] = 'MOD_PF_DASH_BUTTONS_ADD_PROJECT';
        }
        if($user->authorise('core.create', 'com_projectfork') || $user->authorise('milestone.create', $asset)) {
            $buttons['milestoneform.add'] = 'MOD_PF_DASH_BUTTONS_ADD_MILESTONE';
        }
        if($user->authorise('core.create', 'com_projectfork') || $user->authorise('tasklist.create', $asset)) {
            $buttons['tasklistform.add'] = 'MOD_PF_DASH_BUTTONS_ADD_TASKLIST';
        }
        if($user->authorise('core.create', 'com_projectfork') || $user->authorise('task.create', $asset)) {
            $buttons['taskform.add'] = 'MOD_PF_DASH_BUTTONS_ADD_TASK';
        }

        return $buttons;
    }
}
