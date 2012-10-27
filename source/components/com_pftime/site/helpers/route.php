<?php
/**
 * @package      Projectfork
 * @subpackage   Timetracking
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


jimport('joomla.application.component.helper');


/**
 * Component Route Helper
 *
 * @static
 */
abstract class PFtimeHelperRoute
{
    /**
     * Creates a link to the timesheet overview
     *
     * @param     string    $project    The project slug. Optional
     *
     * @return    string    $link       The link
     */
    public static function getTimesheetRoute($project = '')
    {
        $link  = 'index.php?option=com_pftime&view=timesheet';
        $link .= '&filter_project=' . $project;

        $needles = array('filter_project'   => array((int) $project)
                        );

        if ($item = PFApplicationHelper::itemRoute($needles, 'com_pftime.timesheet')) {
            $link .= '&Itemid=' . $item;
        }
        elseif ($item = PFApplicationHelper::itemRoute(null, 'com_pftime.timesheet')) {
            $link .= '&Itemid=' . $item;
        }

        return $link;
    }
}
