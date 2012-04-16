<?php
/**
* @package   Projectfork
* @copyright Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL, see license.txt
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

// No direct access
defined('_JEXEC') or die;


class ProjectforkHelperContextMenu
{
    public function start()
    {
        $html = array();

        $html[] = '<div class="btn-group">';
        $html[] = '    <a class="btn dropdown-toggle" data-toggle="dropdown" href="#"><span class="caret"></span></a>';
        $html[] = '    <ul class="dropdown-menu">';

        return implode("\n", $html);
    }


    public function itemLink($icon, $title, $action)
    {
        $html = array();

        $html[] = '        <li>';
        $html[] = '            <a href="'.$action.'"><i class="'.$icon.'"></i> '.JText::_($title).'</a>';
        $html[] = '        </li>';

        return implode("\n", $html);
    }


    public function itemJavaScript($icon, $title, $action)
    {
        $html = array();

        $html[] = '        <li>';
        $html[] = '            <a onclick="'.$action.'" href="javascript:void(0);"><i class="'.$icon.'"></i> '.JText::_($title).'</a>';
        $html[] = '        </li>';

        return implode("\n", $html);
    }


    public function itemDivider()
    {
        return '        <li class="divider"></li>';
    }


    public function itemEdit($asset, $id = 0, $access = false)
    {
        if(!$access) return '';

        $icon   = 'icon-pencil';
        $action = JRoute::_('index.php?option=com_projectfork&task='.strval($asset).'.edit&id='.intval($id));
        $title  = JText::_('COM_PROJECTFORK_ACTION_EDIT');

        return $this->itemLink($icon, $title, $action);
    }


    public function itemTrash($asset, $id, $access = false)
    {
        if(!$access) return '';

        $icon   = 'icon-trash';
        $action = "return listItemTask('cb".$id."','".$asset.".trash');";
        $title  = JText::_('COM_PROJECTFORK_ACTION_TRASH');

        return $this->itemJavaScript($icon, $title, $action);
    }


    public function bulkItems($actions)
    {
        $message = addslashes(JText::_('JLIB_HTML_PLEASE_MAKE_A_SELECTION_FROM_THE_LIST'));
        $html    = array();

        $html[] = '<div class="btn-group" id="bulk-action-menu">';
        $html[] = '    <a class="btn btn-primary dropdown-toggle" data-toggle="dropdown" href="#"><span class="caret"></span></a>';
        $html[] = '    <ul class="dropdown-menu">';


        foreach($actions AS $action)
        {
            $js = "if(document.adminForm.boxchecked.value==0){alert('".$message."');}"
                . "else{Joomla.submitbutton('".$action->value."')}";


            $icon = 'icon-chevron-right';

            if(strpos($action->value, '.publish') !== false)   $icon = 'icon-eye-open';
            if(strpos($action->value, '.unpublish') !== false) $icon = 'icon-eye-close';
            if(strpos($action->value, '.archive') !== false)   $icon = 'icon-folder-open';
            if(strpos($action->value, '.trash') !== false)     $icon = 'icon-trash';
            if(strpos($action->value, '.delete') !== false)    $icon = 'icon-remove';
            if(strpos($action->value, '.checkin') !== false)   $icon = 'icon-ok-sign';

            $html[] = $this->itemJavaScript($icon, $action->text, $js);
        }

        $html[] = '    </ul>';
        $html[] = '</div>';

        return implode("\n", $html);

    }


    public function end()
    {
        $html = array();

        $html[] = '    </ul>';
        $html[] = '</div>';

        return implode("\n", $html);
    }
}