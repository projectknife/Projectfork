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
 * Abstract class for Task HTML elements
 *
 */
abstract class ProjectforkTask
{
    public static function complete($i, $complete = 0, $can_change = false)
    {
        $html  = array();

        if ($can_change) {
            $class = ($complete ? ' btn-success active' : '');

            $html[] = '<div class="btn-group">';
            $html[] = '<a id="complete-btn-' . $i . '" class="btn btn-mini' . $class . '" href="javascript:void(0);" onclick="PFtask.complete(' . $i . ');">';
            $html[] = '<i class="icon-ok"></i>';
            $html[] = '</a>';
            $html[] = '</div>';
            $html[] = '<input type="hidden" id="complete' . $i . '" value="' . (int) $complete . '"/>';
        }
        else {
            $class = ($complete ? ' label-success' : '');

            $html[] = '<span id="complete-btn-complete' . $i . '" class="label' . $class . '"><i class="icon-ok"></i></span>';
        }

        return implode('', $html);
    }
}