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


class ProjectforkHelperToolbar
{
    private $buttons;


    public function __construct()
    {
        $this->buttons = array();
    }


    public function button($text, $task = '', $list = false)
    {
        $this->buttons[] = $this->renderButton($text, $task, $list);
    }


    public function dropdownButton($items, $text, $action = '', $list = false)
    {
        $this->buttons[] = $this->renderDropdownButton($items, $text, $action, $list);
    }


    protected function renderButton($text, $task = '', $list = false)
    {
        $html = array();

        $html[] = '<button class="button btn btn-info" ';

        if($task) {
            $html[] = 'onclick="';

            if($list) {
                $message = JText::_('JLIB_HTML_PLEASE_MAKE_A_SELECTION_FROM_THE_LIST');
		        $message = addslashes($message);
                $html[] = "if(document.adminForm.boxchecked.value==0){alert('$message');}else{Joomla.submitbutton('$task')}";
            }
            else {
                $html[] = "Joomla.submitbutton('$task');";
            }

            $html[] = '" ';
        }

        $html[] = '>';
        $html[] = '<i class="icon-plus icon-white"></i> ';
        $html[] = addslashes(JText::_($text));
        $html[] = '</button>';

        return implode('', $html);
    }


    protected function renderLink($text, $task = '', $list = false)
    {
        $html = array();

        $html[] = '<a href="javascript:void();" ';

        if($task) {
            $html[] = 'onclick="';

            if($list) {
                $message = JText::_('JLIB_HTML_PLEASE_MAKE_A_SELECTION_FROM_THE_LIST');
		        $message = addslashes($message);
                $html[] = "if(document.adminForm.boxchecked.value==0){alert('$message');}else{Joomla.submitbutton('$task')}";
            }
            else {
                $html[] = "Joomla.submitbutton('$task');";
            }

            $html[] = '" ';
        }

        $html[] = '>';
        $html[] = '<i class="icon-plus icon-white"></i> ';
        $html[] = addslashes(JText::_($text));
        $html[] = '</a>';

        return implode('', $html);
    }


    protected function renderDropdownButton($items, $text, $task = '', $list = false)
    {
        $html = array();

        $html[] = '<div class="btn-group">';
        $html[] = '    '.$this->renderButton($text, $task, $list);
        $html[] = '    <button class="button btn btn-info dropdown-toggle" data-toggle="dropdown">';
        $html[] = '        <span class="caret"></span>';
        $html[] = '    </button>';
        $html[] = '    <ul class="dropdown-menu">';

        if(is_array($items)) {
            foreach($items AS $task => $item)
            {
                $txt  = (isset($item['text']) ? $item['text'] : '');
                $list = (isset($item['list']) ? (boolean) $item['list'] : false);

                $html[] = '<li>';
                $html[] = $this->renderLink($txt, $task, $list);
                $html[] = '</li>';
            }
        }

        $html[] = '    </ul>';
        $html[] = '</div>';

        return implode('', $html);
    }


    public function __toString()
    {
        return implode('', $this->buttons);
    }
}