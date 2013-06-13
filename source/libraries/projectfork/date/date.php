<?php
/**
* @package      pkg_projectfork
* @subpackage   lib_projectfork
*
* @author       Tobias Kuhn (eaxs)
* @copyright    Copyright (C) 2006-2013 Tobias Kuhn. All rights reserved.
* @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
**/

defined('_JEXEC') or die();


abstract class PFDate
{
    public static function relative($date = null, $tz = true)
    {
        static $today_day_of_week = null;
        static $time_offset = null;
        static $time_format = null;
        static $nulldate    = null;

        if (is_null($time_offset)) {
            $config = JFactory::getConfig();
		    $user   = JFactory::getUser();

            $today_day_of_week = date('N');

            $time_offset = $user->getParam('timezone', $config->get('offset'));
            $time_format = 'Y-m-d H:i:s';
            $nulldate    = JFactory::getDbo()->getNullDate();
        }

        if ($tz) {
            $now_date = JFactory::getDate('now', 'UTC');

            $now_date->setTimeZone(new DateTimeZone($time_offset));

            $now = strtotime($now_date->format($time_format, true, false));
        }
        else {
            $now = time();
        }

        if (!$date || $date == $nulldate) {
            return false;
        }

        if ($tz) {
            // Get a date object based on UTC.
			$dateObj = JFactory::getDate($date, 'UTC');

			// Set the correct time zone based on the user configuration.
			$dateObj->setTimeZone(new DateTimeZone($time_offset));

            $timestamp = strtotime($dateObj->format($time_format, true, false));
        }
        else {
            $timestamp = strtotime($date);
        }

        $remaining = $timestamp - $now;
        $is_past   = ($remaining <= 0) ? true : false;
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


    /**
     * Method to shift a time span along a constrained time line
     *
     * @param     array    $timespan           The time span to adjust
     * @param     array    $constraint         Constrained time line
     * @param     array    $prev_constraint    Previous time line
     * @param     array    $options            Config options
     *
     * @return    array    $result             The update start and end date
     */
    public static function shiftTimeline($timespan, $constraint = array(), $prev_constraint = array(), $options = array())
    {
        static $db = null;
        static $nd = null;

        if (is_null($db)) {
            $db = JFactory::getDbo();
            $nd = $db->getNullDate();
        }

        // Prepare current constraint vars
        $start = (isset($constraint[0]) ? $constraint[0] : null);
        $end   = (isset($constraint[1]) ? $constraint[1] : null);

        $has_start  = !(empty($start) || $start == $nd);
        $has_end    = !(empty($end)   || $end == $nd);
        $start_time = ($has_start ? strtotime($start) : 0);
        $end_time   = ($has_end   ? strtotime($end)   : 0);
        $time_span  = ($has_start && $has_end) ? $end_time - $start_time : 0;

        // Prepare previous constraint vars
        $prev_start = (isset($prev_constraint[0]) ? $prev_constraint[0] : null);
        $prev_end   = (isset($prev_constraint[1]) ? $prev_constraint[1] : null);

        $prev_has_start  = !(empty($prev_start) || $prev_start == $nd);
        $prev_has_end    = !(empty($prev_end)   || $prev_end == $nd);
        $prev_start_time = ($prev_has_start ? strtotime($prev_start) : 0);
        $prev_end_time   = ($prev_has_end   ? strtotime($prev_end)   : 0);
        $prev_time_span  = ($prev_has_start && $prev_has_end) ? $prev_end_time - $prev_start_time : 0;

        // Prepare timeline item vars
        $item_start_date = (isset($timespan[0]) ? $timespan[0] : null);
        $item_end_date   = (isset($timespan[1]) ? $timespan[1] : null);

        $item_has_start  = !(empty($item_start_date) || $item_start_date == $nd);
        $item_has_end    = !(empty($item_end_date)   || $item_end_date == $nd);
        $item_start_time = ($item_has_start ? strtotime($item_start_date) : 0);
        $item_end_time   = ($item_has_end   ? strtotime($item_end_date)   : 0);
        $item_time_span  = ($item_has_start && $item_has_end) ? $item_end_time - $item_start_time : 0;

        // Calculate the offsets
        $item_offset_start_to_start = ($has_start && $item_has_start) ? $item_start_time - $start_time : 0;
        $item_offset_start_to_end   = ($has_end && $item_has_start)   ? $end_time - $item_start_time   : 0;
        $item_offset_end_to_end     = ($has_end && $item_has_end)     ? $end_time - $item_end_time     : 0;
        $item_offset_end_to_start   = ($has_start && $item_has_end)   ? $item_end_time - $start_time   : 0;

        // Start date offset
        $item_offset_start = 0;

        if ($item_offset_start_to_start < 0) {
            $item_offset_start = $item_offset_start_to_start;
        }

        if ($item_offset_start_to_end < 0 && $item_offset_start_to_end < $item_offset_start) {
            $item_offset_start = $item_offset_start_to_end;
        }

        // End date offset
        $item_offset_end = 0;

        if ($item_offset_end_to_end < 0) {
            $item_offset_end = $item_offset_end_to_end;
        }

        if ($item_offset_end_to_start < 0 && $item_offset_end_to_start < $item_offset_end) {
            $item_offset_end = $item_offset_end_to_start;
        }

        if ($item_offset_start < 0 && $item_offset_end < 0) {
            // Time span is entirely out of bounds

            // Can we can preserve the time span duration?
            if ($item_time_span <= $time_span && $item_time_span > 0) {
                // Did the previous constraint have a set time span?
                if ($prev_time_span) {
                    $item_start_offset = $item_start_time - $prev_start_time;
                    $item_end_offset   = $prev_end_time - $item_end_time;

                    // Shift the entire span relative along the contrained timeline if possible
                    if ($item_time_span + $item_start_offset <= $time_span) {
                        // Shift by start date offset
                        $item_start_time = $start_time + $item_start_offset;
                        $item_end_time   = $item_start_time + $item_time_span;
                    }
                    elseif ($item_time_span + $item_end_offset <= $time_span) {
                        // Shift by end date offset
                        $item_end_time   = $end_time - $item_end_offset;
                        $item_start_time = $item_end_time - $item_time_span;
                    }
                    else {
                        // Dont shift
                        $item_start_time = $start_time;
                        $item_end_time   = $item_start_time + $item_time_span;
                    }
                }
                else {
                    // No previous time line
                    $item_start_time = $start_time;
                    $item_end_time   = $item_start_time + $item_time_span;
                }
            }
            else {
                // Unable to preserve duration
                $item_start_time = $start_time;
                $item_end_time   = $end_time;
            }
        }
        elseif ($item_offset_start < 0) {
            // Start date is out of bounds

            // Cut it off
            if ($has_start) {
                $item_start_time = $start_time;
            }
            elseif ($has_end) {
                $item_start_time = $end_time;
            }
            else {
                $item_start_time = 0;
            }
        }
        elseif ($item_offset_end < 0) {
            // End date is out of bounds

            // Cut it off
            if ($has_end) {
                $item_end_time = $end_time;
            }
            elseif ($has_start) {
                $item_end_time = $start_time;
            }
            else {
                $item_end_time = 0;
            }
        }

        // Prepare return data
        $result = array();

        if ($item_has_start && strtotime($item_start_date) != $item_start_time) {
            $date = new JDate(($item_start_time == 0 ? $nd : $item_start_time));
            $result[0] = $date->toSql();
        }
        else {
            $result[0] = $item_start_date;
        }

        if ($item_has_end && strtotime($item_end_date) != $item_end_time) {
            $date = new JDate(($item_end_time == 0 ? $nd : $item_end_time));
            $result[1] = $date->toSql();
        }
        else {
            $result[1] = $item_end_date;
        }

        return $result;
    }
}
