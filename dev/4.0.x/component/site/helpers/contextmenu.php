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
    protected $items;


    public function __construct()
    {
        $this->items = array();
    }


    protected function addItem($html)
    {
        $this->items[] = $html;
    }


    public function render($disabled_options = array())
    {
        $class = '';

        if(isset($disabled_options['class']) && $disabled_options['class'] != '') {
            $class = ' '.$disabled_options['class'];
        }

        if(count($this->items) <= 2) {
            $this->items = array();

            $html = array();
            $html[] = '<div class="btn-group">';
            $html[] = '    <a class="btn disabled' . $class . '" href="javascript: void(0);"><span class="caret"></span></a>';
            $html[] = '</div>';

            return implode("\n", $html);
        }
        else {
            $html = implode("\n", $this->items);

            $this->items = array();

            return $html;
        }
    }


    public function start($options = array(), $return = false)
    {
        $class  = '';
        $title  = '';
        $pull   = '';
        $single = false;

        if(isset($options['class']) && $options['class'] != '') {
            $class = ' '.$options['class'];
        }

        if(isset($options['title']) && $options['title'] != '') {
            $title = $options['title'].' ';
        }

        if(isset($options['single-button']) && $options['single-button'] != '') {
            $single = (bool) $options['single-button'];
        }

        if(isset($options['pull']) && $options['pull'] != '') {
            $pull = ' pull-' . $options['pull'];
        }

        $html = array();

        if(!$single) {
            $html[] = '<div class="btn-group' . $pull . '">';
            $html[] = '    <a class="btn dropdown-toggle'.$class.'" data-toggle="dropdown" href="#">'.$title.'<span class="caret"></span></a>';
            $html[] = '    <ul class="dropdown-menu">';
        }
        else {
            $html[] = '<div class="btn '.$class.'">'.$title.'</div>';
        }

        if($return) return implode("\n", $html);

        $this->addItem(implode("\n", $html));
    }


    public function itemLink($icon, $title, $action, $return = false)
    {
        $html = array();

        $html[] = '        <li>';
        $html[] = '            <a href="'.$action.'"><i class="'.$icon.'"></i> '.JText::_($title).'</a>';
        $html[] = '        </li>';

        if($return) return implode("\n", $html);

        $this->addItem(implode("\n", $html));
    }


    public function itemPlaceholder($icon, $title, $return = false)
    {
        $html = array();

        $html[] = '        <li>';
        $html[] = '            <i class="'.$icon.'"></i> '.JText::_($title);
        $html[] = '        </li>';

        if($return) return implode("\n", $html);

        $this->addItem(implode("\n", $html));
    }


    public function itemModal($icon, $title, $action, $size_x = '800', $size_y = '500', $return = false)
    {
        static $modal;

        // Load the modal behavior script.
        if(!isset($modal)) JHtml::_('behavior.modal', 'a.modal_item');
        $html = array();

        $html[] = '        <li>';
        $html[] = '            <a class="modal_item" href="'.$action.'" rel="{handler: \'iframe\', size: {x: '.$size_x.', y: '.$size_y.'}}"><i class="'.$icon.'"></i> '.JText::_($title).'</a>';
        $html[] = '        </li>';

        if($return) return implode("\n", $html);

        $this->addItem(implode("\n", $html));
    }


    public function itemJavaScript($icon, $title, $action, $return = false)
    {
        $html = array();

        $html[] = '        <li>';
        $html[] = '            <a onclick="'.$action.'" href="javascript:void(0);"><i class="'.$icon.'"></i> '.JText::_($title).'</a>';
        $html[] = '        </li>';

        if($return) return implode("\n", $html);

        $this->addItem(implode("\n", $html));
    }


    public function itemDivider($return = false)
    {
        if($return) return '        <li class="divider"></li>';
        $this->addItem('        <li class="divider"></li>');
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


    public function priorityList($i, $id, $asset, $selected = 0, $access = false, $css_class = 'btn-mini')
    {
        $priorities = JHtml::_('projectfork.priorityOptions');
        $html  = array();
        $title = '';
        $class = 'very-low-priority';

        // Find the current priority and class
        foreach($priorities AS $priority)
        {
            if($priority->value == $selected) {
                $title = $priority->text;

                switch($priority->value)
                {
                    case 0:
                        $class = 'very-low-priority';
                        break;

                    case 2:
                        $class = 'btn-info low-priority';
                        break;

                    case 3:
                        $class = 'btn-primary medium-priority';
                        break;

                    case 4:
                        $class = 'btn-warning high-priority';
                        break;

                    case 5:
                        $class = 'btn-danger very-high-priority';
                        break;

                    default:
                        $class = 'very-low-priority';
                        break;
                }
            }
        }

        $class .= ' '.$css_class;


        if($access) {
            $html[] = $this->start(array('title' => $title, 'class' => $class), true);
            foreach($priorities AS $priority)
            {
                if($title == $priority->text) continue;
                $action = "document.id('priority".$i."').set('value', ".intval($priority->value)."); return listItemTask('cb".$i."','".$asset.".savePriority');";
                $html[] = $this->itemJavaScript('icon-flag', $priority->text, $action, true);
            }
            $html[] = $this->end(true);
        }
        else {
            $html[] = $this->start(array('title' => $title, 'class' => $class, 'single-button' => true), true);
        }

        $html[] = '<input type="hidden" id="priority'.$i.'" name="priority['.$id.']" value="'.intval($selected).'"/>';


        return implode("\n", $html);
    }


    public function assignedUsers($i, $id, $asset, $assigned, $access = false, $css_class = 'btn-mini')
    {
        $count = count($assigned);
        $class = '';

        $title = ($count > 0) ? $assigned[0]->name : JText::_('COM_PROJECTFORK_UNASSIGNED');
        $title .= ($count > 1) ? ' +'.($count - 1) : '';

        $class .= ' '.$css_class;
        $link  = ProjectforkHelperRoute::getUsersRoute().'&amp;layout=modal&amp;tmpl=component&amp;field=assigned'.$i;

        if(!$access && $count < 2) {
            $html[] = $this->start(array('title' => $title, 'class' => $class, 'single-button' => true), true);
            $html[] = '<input type="hidden" id="assigned'.$i.'" name="assigned['.$id.']" value="0"/>';

            return implode("\n", $html);
        }
        else {
            // Build the script.
    		$script = array();
    		$script[] = '	function jSelectUser_assigned' . $i . '(id, title) {';
    		$script[] = '		document.getElementById("assigned' . $i . '").value = id';
    		$script[] = '		SqueezeBox.close();';
    		$script[] = '		return listItemTask("cb'.$i.'","'.$asset.'.addUsers");';
    		$script[] = '	}';

    		// Add the script to the document head.
    		JFactory::getDocument()->addScriptDeclaration(implode("\n", $script));

            $html[] = $this->start(array('title' => $title, 'class' => $class), true);
        }

        if($access) {
            $html[] = $this->itemModal('icon-plus', 'COM_PROJECTFORK_ASSIGN_TO_USER', $link, 800, 500, true);
            if($count > 1) $html[] = $this->itemDivider(true);
        }

        foreach($assigned AS $user)
        {
            /*if($access) {
                $action = "$('assigned".$i."').set('value', ".intval($user->user_id)."); return listItemTask('cb".$i."','".$asset.".deleteUsers');";
                $html[] = $this->itemJavaScript('icon-remove', $user->name, $action, true);
            }
            else {
                $html[] = $this->itemPlaceholder('icon-user', $user->name, true);
            }*/
            $action = "$('filter_assigned').set('value', ".intval($user->user_id)."); submitbutton();";
            $html[] = $this->itemJavaScript('icon-user', $user->name, $action, true);
        }

        $html[] = $this->end(true);
        $html[] = '<input type="hidden" id="assigned'.$i.'" name="assigned['.$id.']" value="0"/>';


        return implode("\n", $html);
    }


    public function bulkItems($actions)
    {
        $message = addslashes(JText::_('JLIB_HTML_PLEASE_MAKE_A_SELECTION_FROM_THE_LIST'));
        $html    = array();


        if(count($actions) == 0) {
            $html[] = '<div class="btn-group" id="bulk-action-menu">';
            $html[] = '    <a class="btn btn-primary disabled" href="javascript: void(0);"><span class="caret"></span></a>';
            $html[] = '</div>';

            return implode("\n", $html);
        }


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

            $html[] = $this->itemJavaScript($icon, $action->text, $js, true);
        }

        $html[] = '    </ul>';
        $html[] = '</div>';

        return implode("\n", $html);
    }


    public function end($return = false)
    {
        $html = array();

        $html[] = '    </ul>';
        $html[] = '</div>';

        if($return) return implode("\n", $html);

        $this->addItem(implode("\n", $html));
    }
}
