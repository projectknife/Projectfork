<?php
/**
* @package      Projectfork
*
* @author       Tobias Kuhn (eaxs)
* @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
* @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
**/

defined('_JEXEC') or die();


/**
 * Abstract class for Time sheet HTML elements
 *
 */
abstract class JHtmlTimesheet
{
    /**
     * Formats the logged time into hours and seconds
     *
     * @param     integer    $secs    The seconds spent
     *
     * @return    string              The formatted time string
     */
    public static function format($secs = 0)
    {
        $secs = intval($secs);
        if (!$secs) return '';

        $minutes = $secs / 60;
        $hours   = floor($minutes / 60);

        if ($hours > 0) {
            $minutes = $minutes - ($hours * 60);
        }

        $minutes = floor($minutes);
        $format  = '';

        if ($hours) {
            $format .= $hours . ' ' . ($hours > 1 ? JText::_('COM_PROJECTFORK_TIME_HOURS') : JText::_('COM_PROJECTFORK_TIME_HOUR'));
        }

        if ($minutes) {
            if ($hours) $format .= ' ';
            $format .= $minutes . ' ' . ($minutes > 1 ? JText::_('COM_PROJECTFORK_TIME_MINUTES') : JText::_('COM_PROJECTFORK_TIME_MINUTE'));
        }

        return $format;
    }
}
