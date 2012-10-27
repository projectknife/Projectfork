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
 * Abstract class for Projectfork HTML elements
 *
 */
abstract class JHtmlProjectfork
{
    /**
     * Returns priority select list option objects
     *
     * @return    array    $options    The object list
     */
    public static function priorityOptions()
    {
        $options   = array();
        $options[] =  JHtml::_('select.option', '1', JText::_('COM_PROJECTFORK_PRIORITY_VERY_LOW'));
        $options[] =  JHtml::_('select.option', '2', JText::_('COM_PROJECTFORK_PRIORITY_LOW'));
        $options[] =  JHtml::_('select.option', '3', JText::_('COM_PROJECTFORK_PRIORITY_MEDIUM'));
        $options[] =  JHtml::_('select.option', '4', JText::_('COM_PROJECTFORK_PRIORITY_HIGH'));
        $options[] =  JHtml::_('select.option', '5', JText::_('COM_PROJECTFORK_PRIORITY_VERY_HIGH'));

        return $options;
    }


    /**
     * Method to format a floating point value according the configured monetary settings
     *
     * @param     float    $value      The amount of money
     * @param     int      $project    Optional project id from which to use the settings
     *
     * @return    array    $options    The object list
     */
    public static function moneyFormat($value = 0.00, $project = 0)
    {
        $value  = (float) $value;
        $params = ProjectforkHelper::getProjectParams((int) $project);

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
