<?php
/**
* @package      Projectfork
* @subpackage   Timetracking
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
abstract class JHtmlTime
{
    /**
     * Formats the logged time into hours and seconds
     *
     * @param     integer    $secs     The seconds spent
     * @param     string     $style    (Optional) Format style
     *
     * @return    string               The formatted time string
     */
    public static function format($secs = 0, $style = 'literal')
    {
        $secs   = intval($secs);
        $format = '';

        if (!$secs) return $format;

        $minutes = $secs / 60;
        $hours   = floor($minutes / 60);

        // Literal style
        switch(strtolower($style))
        {
            case 'decimal':
                if ($minutes > 0) {
                    $format = number_format($minutes / 60, 1);
                }
                else {
                    $format = 0.00;
                }
                break;

            case 'literal':
            default:
                if ($hours > 0) {
                    $minutes = $minutes - ($hours * 60);
                }

                $minutes = floor($minutes);

                if ($hours) {
                    $format .= $hours . ' ' . ($hours > 1 ? JText::_('COM_PROJECTFORK_TIME_HOURS') : JText::_('COM_PROJECTFORK_TIME_HOUR'));
                }

                if ($minutes) {
                    if ($hours) $format .= ' ';
                    $format .= $minutes . ' ' . ($minutes > 1 ? JText::_('COM_PROJECTFORK_TIME_MINUTES') : JText::_('COM_PROJECTFORK_TIME_MINUTE'));
                }

                if (!$minutes && !$hours) {
                    $format .= '0 ' . JText::_('COM_PROJECTFORK_TIME_MINUTES');
                }
                break;
        }


        return $format;
    }
}
