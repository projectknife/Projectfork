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
 * Field to enter a monetary, decimal value.
 *
 */
class JFormFieldMoney extends JFormField
{
    /**
     * The form field type.
     *
     * @var    string
     */
    public $type = 'Money';


    /**
     * Method to get the user field input markup.
     *
     * @return    string    The field input markup.
     */
    protected function getInput()
    {
        static $js_loaded = false;

        $html = array();
        $js   = array();

        if ($js_loaded === false) {
            // Create js function
            $js[] = "function setMoneyFieldValue(fid)";
            $js[] = "{";
            $js[] = "    var v1 = document.id(fid + '_v1').get('value');";
            $js[] = "    var v2 = document.id(fid + '_v2').get('value');";
            $js[] = "    document.id(fid).setProperty('value', v1 + '.' + v2);";
            $js[] = "}";

            $doc       = JFactory::getDocument();
            $js_loaded = true;

            $doc->addScriptDeclaration(implode("\n", $js));
        }

        // Setup field values
        if ($this->value) {
            $v1 = substr($this->value, 0, -3);
            $v2 = substr($this->value, -2);
        }
        else {
            $this->value = '0.00';
            $v1 = '0';
            $v2 = '00';
        }

        // Initialize some field attributes.
        $readonly  = ((string) $this->element['readonly'] == 'true') ? ' readonly="readonly"' : '';
        $disabled  = ((string) $this->element['disabled'] == 'true') ? ' disabled="disabled"' : '';
        $maxlength = ((int) $this->element['maxlength'] != '') ? $this->element['maxlength'] : 4;

        if ($readonly == '' && $disabled == '') {
            $onchange = ' onchange="setMoneyFieldValue(\'' . $this->id  . '\');"';
        }
        else {
            $onchange = '';
        }

        // Get params
        $params = ProjectforkHelper::getProjectParams();
        $currency = $params->get('currency_sign');
        $decimal  = $params->get('decimal_delimiter');


        // Prepare HTML
        if ($params->get('currency_position') == '0') {
            $html[] = '<span style="float:left;margin: 5px 0px 0px 0;">'  . $currency . '</span>';
        }

        $html[] = '<input type="text" name="' . $this->name . '_v1" id="' . $this->id . '_v1" size="10" maxlength="' . $maxlength . '" value="' . $v1 . '" ' . $onchange . $disabled . $readonly . '/>';
        $html[] = '<span style="float:left;margin: 5px 0px 0px 0;"><strong>' . $decimal . '</strong>&nbsp;&nbsp;</span>';
        $html[] = '<input type="text" name="' . $this->name . '_v2" id="' . $this->id . '_v2" size="10" maxlength="2" value="' . $v2 . '" ' . $onchange . $disabled . $readonly . '/>';
        $html[] = '<input type="hidden" name="' . $this->name . '" id="' . $this->id . '" value="' . htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8') . '" ' . $disabled . $readonly . '/>';

        if ($params->get('currency_position') == '1') {
            $html[] = '<span style="float:left;margin: 5px 0px 0px 0;">'  . $currency . '</span>';
        }

        // Return HTML
        return implode("\n", $html);
    }
}
