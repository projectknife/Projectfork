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


abstract class JHtmlPfforum
{
    static function repliesLabel($replies = 0, $activity = null)
    {
        static $format = null;

        if (is_null($format)) {
            $params = JComponentHelper::getParams('com_projectfork');
            $format = $params->get('date_format');

            if (!$format) {
                $format = JText::_('DATE_FORMAT_LC1');
            }
        }

        $html = array();
        $text = JText::plural('COM_PROJECTFORK_N_REPLIES', (int) $replies);

        if ($replies == 0) {
            $html[] = '<span class="label">' . $text . '</span>';
        }
        else {
            $title = '';
            $class = '';
            $style = '';

            if ($activity && $activity != JFactory::getDbo()->getNullDate()) {
                $title = ' title="' . PFDate::relative($activity) . '::' . JHtml::_('date', $activity, $format) . '"';
                $style = ' style="cursor: help"';
                $class = ' hasTip';
            }

            $html[] = '<span class="label label-success' . $class . '"' . $title . $style . '>' . $text . '</span>';
        }

        return implode('', $html);
    }
}
