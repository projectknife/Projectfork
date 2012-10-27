<?php
/**
* @package      Projectfork.Library
* @subpackage   Html
*
* @author       Tobias Kuhn (eaxs)
* @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
* @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
**/

defined('_JEXEC') or die();


abstract class PFhtmlFormat
{
    /**
     * Method to format a floating point value according the configured monetary settings
     *
     * @param     float    $value      The amount of money
     * @param     int      $project    Optional project id from which to use the settings
     *
     * @return    array    $options    The object list
     */
    public static function money($value = 0.00, $project = 0)
    {
        $value  = (float) $value;
        $params = PFApplicationHelper::getProjectParams((int) $project);

        $nf_dec   = $params->get('decimal_delimiter', '.');
        $nf_th    = $params->get('thousands_delimiter', ',');
        $currency = $params->get('currency_sign', '$');

        $html = array();

        if ($params->get('currency_position') == '0') {
            $html[] = $currency . '&nbsp;';
        }

        $html[] = number_format($value, 2, $nf_dec, $nf_th);

        if ($params->get('currency_position') == '1') {
            $html[] = '&nbsp;' . $currency;
        }

        return implode('', $html);
    }

}