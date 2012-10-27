<?php
/**
* @package      Projectfork.Library
* @subpackage   Date
*
* @author       Tobias Kuhn (eaxs)
* @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
* @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
**/

defined('_JEXEC') or die();


abstract class PFDate
{
    public static function relative($date = null)
    {
        static $today_day_of_week;

        if (!$today_day_of_week) {
            $today_day_of_week = date('N');
        }

        if (!$date || $date == JFactory::getDbo()->getNullDate()) {
            return false;
        }

        $timestamp = strtotime($date);
        $now       = time();
        $remaining = $timestamp - $now;
        $is_past   = ($remaining < 0) ? true : false;
        $format    = '';

        if ($is_past) {
            // Reverse to positive value
            $remaining = $now - $timestamp;
        }

        $minutes = floor($remaining / 60);
        $hours   = floor($minutes / 60);
        $days    = floor($hours / 24);

        if ($days >= 1) {
            if ($days == '1') {
                $format = JText::_('COM_PROJECTFORK_DAY_' . ($is_past ? 'YESTERDAY' : 'TOMORROW'));
            }
            else {
                if ($days <= 7) {
                    $date_n    = date('N', $timestamp);
                    $day_names = array(1 => 'MONDAY', 2 => 'TUESDAY', 3 => 'WEDNESDAY',
                                       4 => 'THURSDAY', 5 => 'FRIDAY', 6 => 'SATURDAY', 7 => 'SUNDAY');

                    $format = JText::_('COM_PROJECTFORK_DAY_' . ($is_past ? 'LAST_' : 'THIS_') . $day_names[$date_n]);
                }
                else {
                    $format = JText::sprintf('COM_PROJECTFORK_DAYS' . ($is_past ? '_PAST' : ''), $days);
                }
            }
        }
        elseif ($hours >= 1) {
            $format = JText::sprintf('COM_PROJECTFORK_HOUR' . ($hours > 1 ? 'S' : '') . ($is_past ? '_PAST' : ''), $hours);
        }
        elseif ($minutes >= 1) {
            $format = JText::sprintf('COM_PROJECTFORK_MINUTE' . ($minutes > 1 ? 'S' : '') . ($is_past ? '_PAST' : ''), $minutes);
        }
        else {
            $format = JText::_('COM_PROJECTFORK_MOMENT' . ($is_past ? '_PAST' : ''));
        }

        return $format;
    }
}
