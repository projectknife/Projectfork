<?php
/**
* @package   Projectfork Task Statistics
* @copyright Copyright (C) 2012 Tobias Kuhn. All rights reserved.
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.php
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
?>
<table id="mod-pf-stats-tasks" style="display:none;">
    <thead>
        <tr>
            <td></td>
            <th scope="col"></th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <th scope="row"><?php echo JText::_('MOD_PF_STATS_TASKS_COMPLETE');?></th>
            <td scope="row"><?php echo $stats['complete'];?></td>
        </tr>
        <tr>
            <th scope="row"><?php echo JText::_('MOD_PF_STATS_TASKS_INCOMPLETE');?></th>
            <td scope="row"><?php echo $stats['incomplete'];?></td>
        </tr>
        <tr>
            <th scope="row"><?php echo JText::_('MOD_PF_STATS_TASKS_ARCHIVED');?></th>
            <td scope="row"><?php echo $stats['archived'];?></td>
        </tr>
        <tr>
            <th scope="row"><?php echo JText::_('MOD_PF_STATS_TASKS_TRASHED');?></th>
            <td scope="row"><?php echo $stats['trashed'];?></td>
        </tr>
    </tbody>
</table>
