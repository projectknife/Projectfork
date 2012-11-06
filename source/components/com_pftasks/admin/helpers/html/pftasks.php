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


abstract class JHtmlPFtasks
{
    static public function assignedLabel($users = null, $i = 0)
    {
        if (!is_array($users) || !count($users)) {
            return '<span id="assigned_' . $i . '"></span>';
        }

        $html  = array();
        $count = count($users);

        if ($count == 1) {
            $html[] = '<span id="assigned_' . $i . '" class="label user">';
            $html[] = '<i class="icon-user icon-white"></i> ';
            $html[] = htmlspecialchars($users[0]->name, ENT_COMPAT, 'UTF-8');
            $html[] = '</span>';
        }
        else {
            $count = $count - 1;
            $first = array_pop(array_reverse($users));
            $names = array();

            foreach ($users AS $user)
            {
                $names[] = htmlspecialchars($user->name, ENT_COMPAT, 'UTF-8');
            }

            $tooltip = '::' . htmlspecialchars(implode('<br/>', $names), ENT_COMPAT, 'UTF-8');

            $html[] = '<span id="assigned_' . $i . '" class="label user hasTip" title="' . $tooltip . '" style="cursor: help">';
            $html[] = '<i class="icon-user icon-white"></i> ';
            $html[] = htmlspecialchars($first->name, ENT_COMPAT, 'UTF-8') . ' +' . $count;
            $html[] = '</span>';
        }

        return implode('', $html);
    }


    static public function priorityLabel($id, $i = 0, $value = null)
    {
        switch((int) $value)
        {
            case 2:
                $class = 'label-success low-priority';
                $text  = JText::_('COM_PROJECTFORK_PRIORITY_LOW');
                break;

            case 3:
                $class = 'label-info medium-priority';
                $text  = JText::_('COM_PROJECTFORK_PRIORITY_MEDIUM');
                break;

            case 4:
                $class = 'label-warning high-priority';
                $text  = JText::_('COM_PROJECTFORK_PRIORITY_HIGH');
                break;

            case 5:
                $class = 'label-important very-high-priority';
                $text  = JText::_('COM_PROJECTFORK_PRIORITY_VERY_HIGH');
                break;

            default:
            case 1:
                return '<span id="priority_' . $i . '_label"></span>'
                     . '<input type="hidden" name="priority[' . $id . ']" id="priority' . $i . '" value="1"/>';
                break;
        }

        $html = '<span id="priority_' . $i . '_label" class="label ' . $class . '"><i class="icon-warning icon-white"></i> ' . $text . '</span>'
              . '<input type="hidden" name="priority[' . $id . ']" id="priority' . $i . '" value="' . (int) $value . '"/>';

        return $html;

    }
}