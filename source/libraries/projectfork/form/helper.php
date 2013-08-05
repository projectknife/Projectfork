<?php
/**
 * @package      Projectfork.Library
 * @subpackage   Form
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


/**
 * Projectfork Form Helper
 *
 * @static
 */
class PFFormHelper
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


    /**
     * Method to filter the component asset rules coming from the access manager field
     *
     * @param     mixed    $value     The field value
     *
     * @return    mixed    $return    The filtered value
     */
    public static function filterComponentRules($value)
    {
        $return = array();

        foreach ((array) $value as $component => $rules)
        {
            $rules = self::filterRules($rules);

            $return[$component] = $rules;
        }

        return $return;
    }
}
