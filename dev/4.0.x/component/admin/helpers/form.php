<?php
/**
 * @package      Projectfork
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


/**
 * Projectfork Component Form Helper
 *
 * @static
 */
class ProjectforkHelperForm
{
    /**
     * Method to filter the rules coming from the groupaccess field
     *
     * @param     mixed    $value     The field value
     *
     * @return    mixed    $return    The filtered value
     */
    public static function filterRules($value)
    {
        $return = array();

        foreach ((array) $value as $action => $ids)
        {
            if (is_numeric($action) && is_numeric($ids)) {
                $return[$action] = $ids;
            }
            else {
                // Build the rules array.
                $return[$action] = array();

                foreach ($ids as $id => $p)
                {
                    if ($p !== '') {
                        $return[$action][$id] = ($p == '1' || $p == 'true') ? true : false;
                    }
                }
            }
        }

        return $return;
    }
}
