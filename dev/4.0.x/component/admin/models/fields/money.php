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

        if ($js_loaded === false) {
            // Make sure the JS is loaded only once
            $js_loaded = true;
            $script    = $this->getJavascript();

            JFactory::getDocument()->addScriptDeclaration(implode("\n", $script));
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

        // Get params
        $params = ProjectforkHelper::getProjectParams();

        // Initialize some field attributes.
        $attribs = array();
        $attribs['readonly']  = ((string) $this->element['readonly'] == 'true') ? ' readonly="readonly"' : '';
        $attribs['disabled']  = ((string) $this->element['disabled'] == 'true') ? ' disabled="disabled"' : '';
        $attribs['maxlength'] = ((int) $this->element['maxlength'] != '') ? $this->element['maxlength'] : 4;
        $attribs['currency']  = $params->get('currency_sign');
        $attribs['decimal']   = $params->get('decimal_delimiter');
        $attribs['position']  = $params->get('currency_position');

        if ($attribs['readonly'] == '' && $attribs['disabled'] == '') {
            $attribs['onchange'] = ' onchange="setMoneyFieldValue(\'' . $this->id  . '\');"';
        }
        else {
            $attribs['onchange'] = '';
        }

        // Get HTML
        $html = $this->getHTML($v1, $v2, $attribs);

        // Return HTML
        return implode("\n", $html);
    }


    /**
     * Method to generate the input markup.
     *
     * @param     string    $v1
     * @param     string    $v2
     * @param     string    $attribs
     *
     * @return    string              The html field markup
     */
    protected function getHTML($v1, $v2, $attribs)
    {
        if (JFactory::getApplication()->isSite()) {
            return $this->getSiteHTML($v1, $v2, $attribs);
        }

        return $this->getAdminHTML($v1, $v2, $attribs);
    }


    /**
     * Method to generate the backend input markup.
     *
     * @param     string    $v1
     * @param     string    $v2
     * @param     string    $attribs
     *
     * @return    array $html              The html field markup
     */
    protected function getAdminHTML($v1, $v2, $attribs)
    {
        $html = array();

        if ($attribs['position'] == '0') {
            $html[] = '<span style="float:left;margin: 5px 0px 0px 0;">'  . $attribs['currency'] . '</span>';
        }

        $html[] = '<input type="text" name="' . $this->name . '_v1" id="' . $this->id . '_v1" size="10" '
                . 'maxlength="' . $attribs['maxlength'] . '" value="' . $v1 . '" ' . $attribs['onchange'] . $attribs['disabled'] . $attribs['readonly'] . '/>';
        $html[] = '<span style="float:left;margin: 5px 0px 0px 0;"><strong>' . $attribs['decimal'] . '</strong>&nbsp;&nbsp;</span>';
        $html[] = '<input type="text" name="' . $this->name . '_v2" id="' . $this->id . '_v2" size="10" '
                . 'maxlength="2" value="' . $v2 . '" ' . $attribs['onchange'] . $attribs['disabled'] . $attribs['readonly'] . '/>';
        $html[] = '<input type="hidden" name="' . $this->name . '" id="' . $this->id . '" '
                . 'value="' . htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8') . '" ' . $attribs['disabled'] . $attribs['readonly'] . '/>';

        if ($attribs['position'] == '1') {
            $html[] = '<span style="float:left;margin: 5px 0px 0px 0;">'  . $attribs['currency'] . '</span>';
        }

        return $html;
    }


    /**
     * Method to generate the frontend input markup.
     *
     * @param     string    $v1
     * @param     string    $v2
     * @param     string    $attribs
     *
     * @return    array $html              The html field markup
     */
    protected function getSiteHTML($v1, $v2, $attribs)
    {
        $html = array();

        if ($attribs['position'] == '0') {
            $html[] = '<span>'  . $attribs['currency'] . '</span>';
        }

        $html[] = '<input type="text" name="' . $this->name . '_v1" id="' . $this->id . '_v1" size="10" class="inputbox input-small" '
                . 'maxlength="' . $attribs['maxlength'] . '" value="' . $v1 . '" ' . $attribs['onchange'] . $attribs['disabled'] . $attribs['readonly'] . '/>';
        $html[] = '<span>&nbsp;<strong>' . $attribs['decimal'] . '</strong>&nbsp;</span>';
        $html[] = '<input type="text" name="' . $this->name . '_v2" id="' . $this->id . '_v2" size="10" class="inputbox input-mini" '
                . 'maxlength="2" value="' . $v2 . '" ' . $attribs['onchange'] . $attribs['disabled'] . $attribs['readonly'] . '/>';
        $html[] = '<input type="hidden" name="' . $this->name . '" id="' . $this->id . '" value="'
                . htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8') . '" ' . $attribs['disabled'] . $attribs['readonly'] . '/>';

        if ($attribs['position'] == '1') {
            $html[] = '<span>'  . $attribs['currency'] . '</span>';
        }

        return $html;
    }


    protected function getJavascript()
    {
        $script = array();

        $script[] = "function setMoneyFieldValue(fid)";
        $script[] = "{";
        $script[] = "    var v1 = document.id(fid + '_v1').get('value');";
        $script[] = "    var v2 = document.id(fid + '_v2').get('value');";
        $script[] = "    document.id(fid).setProperty('value', v1 + '.' + v2);";
        $script[] = "}";

        return $script;
    }
}
