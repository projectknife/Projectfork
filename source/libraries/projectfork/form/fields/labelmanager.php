<?php
/**
 * @package      pkg_projectfork
 * @subpackage   lib_projectfork
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2013 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;


jimport('joomla.html.html');
jimport('joomla.form.formfield');


/**
 * Form Field class for managing labels.
 *
 */
class JFormFieldLabelManager extends JFormField
{
    /**
     * The form field type.
     *
     * @var    string
     */
    public $type = 'LabelManager';


    /**
     * The existing label items
     *
     * @var    array
     */
    protected $items;


    /**
     * Method to get the field input markup.
     *
     * @return    string    The html field markup
     */
    protected function getInput()
    {
        // Add the script to the document head.
        $script = $this->getJavascript();
        JFactory::getDocument()->addScriptDeclaration(implode("\n", $script));

        if (!is_array($this->value)) {
            $this->value = array();
        }

        $this->items = array();

        foreach ($this->value AS $item)
        {
            if (!array_key_exists($item->asset_group, $this->items)) {
                $this->items[$item->asset_group] = array();
            }

            $this->items[$item->asset_group][] = $item;
        }

        $assets = array(
            'com_pfprojects.project',
            'com_pfmilestones.milestone',
            'com_pftasks.task',
            'com_pfforum.topic',
            'com_pfrepo.directory',
            'com_pfrepo.note',
            'com_pfrepo.file',
            'com_pfdesigns.design'
        );

        $html = $this->getHTML($assets);

        return implode("\n", $html);
    }


    /**
     * Method to generate the input markup.
     *
     * @return    string    The html field markup
     */
    protected function getHTML($assets)
    {
        if (JFactory::getApplication()->isSite() || version_compare(JVERSION, '3.0.0', 'ge')) {
            return $this->getSiteHTML($assets);
        }

        return $this->getAdminHTML($assets);
    }


    /**
     * Method to generate the backend input markup.
     *
     * @return    array    $html    The html field markup
     */
    protected function getAdminHTML($assets)
    {
        $html = array();

        foreach ($assets AS $asset)
        {
            list($component, $asset_name) = explode('.', $asset, 2);

            if (!PFApplicationHelper::enabled($component)) {
                continue;
            }

            $asset_id = str_replace('.', '_', $asset);

            $html[] = '<ul class="unstyled">';
            $html[] = '<li class="well well-small">';
            $html[] = '<h3>' . JText::_(strtoupper($asset_id) . '_LABEL_TITLE') . '</h3>';
            $html[] = '';
            $html[] = '<ul id="' . $this->id . '_' . $asset_id . '" class="unstyled">';

            if (array_key_exists($asset, $this->items)) {
                foreach ($this->items[$asset] AS $item)
                {
                    $html[] = '<li>';
                    $html[] = '<input type="text" class="inputbox input-medium"';
                    $html[] = 'name="' . $this->name . '[' . $asset . '][title][]" placeholder="' . JText::_('COM_PROJECTFORK_LABEL_TITLE_PLACEHOLDER') . '"';
                    $html[] = ' value="' . htmlspecialchars($item->title, ENT_COMPAT, 'UTF-8') . '" maxlength="32"/>';
                    $html[] = '<select class="inputbox input-medium" name="' . $this->name . '[' . $asset . '][style][]">';
                    $html[] = '<option value="">' . JText::_('COM_PROJECTFORK_LABEL_OPTION_LABEL_STYLE_DEFAULT') . '</option>';
                    $html[] = '<option value="label-success"' . ($item->style == 'label-success' ? ' selected="selected"' : '') . '>' . JText::_('COM_PROJECTFORK_LABEL_OPTION_LABEL_STYLE_SUCCESS') . '</option>';
                    $html[] = '<option value="label-warning"' . ($item->style == 'label-warning' ? ' selected="selected"' : '') . '>' . JText::_('COM_PROJECTFORK_LABEL_OPTION_LABEL_STYLE_WARNING') . '</option>';
                    $html[] = '<option value="label-important"' . ($item->style == 'label-important' ? ' selected="selected"' : '') . '>' . JText::_('COM_PROJECTFORK_LABEL_OPTION_LABEL_STYLE_IMPORTANT') . '</option>';
                    $html[] = '<option value="label-info"' . ($item->style == 'label-info' ? ' selected="selected"' : '') . '>' . JText::_('COM_PROJECTFORK_LABEL_OPTION_LABEL_STYLE_INFO') . '</option>';
                    $html[] = '<option value="label-inverse"' . ($item->style == 'label-inverse' ? ' selected="selected"' : '') . '>' . JText::_('COM_PROJECTFORK_LABEL_OPTION_LABEL_STYLE_INVERSE') . '</option>';
                    $html[] = '</select>';
                    $html[] = '<div class="button2-left"><div class="blank">';
                    $html[] = '<a href="javascript:void(0);" onclick="pfRemoveLabel_' . $this->id . '(this)">' . JText::_('JACTION_DELETE') . '</a>';
                    $html[] = '</div></div>';
                    $html[] = '<input type="hidden" name="' . $this->name . '[' . $asset . '][id][]" value="' . intval($item->id) . '"/>';
                    $html[] = '<div class="clr"></li>';
                }
            }

            $html[] = '</ul>';
            $html[] = '<div class="button2-left"><div class="blank">';
            $html[] = '<a class="btn" href="javascript:void(0);" onclick="pfAddLabel_' . $this->id . '(\'' . $asset_id . '\', \'' . $asset . '\');">';
            $html[] = JText::_('JACTION_ADD_LABEL');
            $html[] = '</a>';
            $html[] = '</div></div>';
            $html[] = '<div class="clr"></div></li>';
            $html[] = '</ul>';
            $html[] = '<input type="hidden" name="' . $this->name . '[]" id="' . $this->id . '" value=""/>';
        }

        return $html;

    }


    /**
     * Method to generate the frontend input markup.
     *
     * @return    array    $html    The html field markup
     */
    protected function getSiteHTML($assets)
    {
        $html = array();

        foreach ($assets AS $asset)
        {
            list($component, $asset_name) = explode('.', $asset, 2);

            if (!PFApplicationHelper::enabled($component)) {
                continue;
            }

            $asset_id = str_replace('.', '_', $asset);

            $html[] = '<ul class="unstyled">';
            $html[] = '<li class="well well-small">';
            $html[] = '<strong>' . JText::_(strtoupper($asset_id) . '_LABEL_TITLE') . '</strong>';
            $html[] = '<hr/>';
            $html[] = '<ul id="' . $this->id . '_' . $asset_id . '" class="unstyled">';

            if (array_key_exists($asset, $this->items)) {
                foreach ($this->items[$asset] AS $item)
                {
                    $html[] = '<li><div class="control-group">';
                    $html[] = '<a class="btn btn-mini" href="javascript:void(0);" onclick="pfRemoveLabel_' . $this->id . '(this)"><i class="icon-remove"></i></a>';
                    $html[] = '<input type="text" class="inputbox input-medium" onkeyup="pfPreviewLabel_' . $this->id . '(this, \'text\')"';
                    $html[] = 'name="' . $this->name . '[' . $asset . '][title][]" placeholder="' . JText::_('COM_PROJECTFORK_LABEL_TITLE_PLACEHOLDER') . '"';
                    $html[] = ' value="' . htmlspecialchars($item->title, ENT_COMPAT, 'UTF-8') . '" maxlength="32"/>';
                    $html[] = '<select class="inputbox input-medium" name="' . $this->name . '[' . $asset . '][style][]" onchange="pfPreviewLabel_' . $this->id . '(this, \'style\')">';
                    $html[] = '<option value="">' . JText::_('COM_PROJECTFORK_LABEL_OPTION_LABEL_STYLE_DEFAULT') . '</option>';
                    $html[] = '<option value="label-success"' . ($item->style == 'label-success' ? ' selected="selected"' : '') . '>' . JText::_('COM_PROJECTFORK_LABEL_OPTION_LABEL_STYLE_SUCCESS') . '</option>';
                    $html[] = '<option value="label-warning"' . ($item->style == 'label-warning' ? ' selected="selected"' : '') . '>' . JText::_('COM_PROJECTFORK_LABEL_OPTION_LABEL_STYLE_WARNING') . '</option>';
                    $html[] = '<option value="label-important"' . ($item->style == 'label-important' ? ' selected="selected"' : '') . '>' . JText::_('COM_PROJECTFORK_LABEL_OPTION_LABEL_STYLE_IMPORTANT') . '</option>';
                    $html[] = '<option value="label-info"' . ($item->style == 'label-info' ? ' selected="selected"' : '') . '>' . JText::_('COM_PROJECTFORK_LABEL_OPTION_LABEL_STYLE_INFO') . '</option>';
                    $html[] = '<option value="label-inverse"' . ($item->style == 'label-inverse' ? ' selected="selected"' : '') . '>' . JText::_('COM_PROJECTFORK_LABEL_OPTION_LABEL_STYLE_INVERSE') . '</option>';
                    $html[] = '</select>';
                    $html[] = JText::_('COM_PROJECTFORK_LABEL_PREVIEW') . ': ';
                    $html[] = '<span class="label ' . $item->style . '"><i class="icon-bookmark"></i> ' . htmlspecialchars($item->title, ENT_COMPAT, 'UTF-8') . '</span>';
                    $html[] = '<input type="hidden" name="' . $this->name . '[' . $asset . '][id][]" value="' . intval($item->id) . '"/>';
                    $html[] = '</div></li>';
                }
            }

            $html[] = '</ul>';
            $html[] = '<div class="control-group">';
            $html[] = '<a class="btn" href="javascript:void(0);" onclick="pfAddLabel_' . $this->id . '(\'' . $asset_id . '\', \'' . $asset . '\');">';
            $html[] = JText::_('JACTION_ADD_LABEL');
            $html[] = '</a>';
            $html[] = '</div>';
            $html[] = '</li>';
            $html[] = '</ul>';
            $html[] = '<input type="hidden" name="' . $this->name . '[]" id="' . $this->id . '" value=""/>';
        }

        return $html;
    }


    /**
     * Generates the javascript needed for this field
     *
     * @param     boolean    $submit    Whether to submit the form or not
     * @param     string     $view      The name of the view
     *
     * @return    array      $script    The generated javascript
     */
    protected function getJavascript()
    {
        $script   = array();
        $onchange = $this->element['onchange'] ? $this->element['onchange'] : '';

        if (JFactory::getApplication()->isSite() || version_compare(JVERSION, '3.0.0', 'ge')) {
            $script[] = 'function pfAddLabel_' . $this->id . '(asset, name)';
            $script[] = '{';
            $script[] = '    var l = jQuery("#' . $this->id . '_" + asset);';
            $script[] = '    var c = "<li><div class=\"control-group\">"';
            $script[] = '          + "<a class=\"btn btn-mini\" href=\"javascript:void(0);\" onclick=\"pfRemoveLabel_' . $this->id . '(this)\"><i class=\"icon-remove\"></i></a>"';
            $script[] = '          + "<input type=\"text\" class=\"inputbox input-medium\" onkeyup=\"pfPreviewLabel_' . $this->id . '(this, \'text\')\"  maxlength=\"32\""';
            $script[] = '          + "name=\"' . $this->name . '["+name+"][title][]\" placeholder=\"' . JText::_('COM_PROJECTFORK_LABEL_TITLE_PLACEHOLDER') . '\"/>"';
            $script[] = '          + "<select class=\"inputbox input-medium\" name=\"' . $this->name . '["+name+"][style][]\" onchange=\"pfPreviewLabel_' . $this->id . '(this, \'style\')\">"';
            $script[] = '          + "<option value=\"\">' . JText::_('COM_PROJECTFORK_LABEL_OPTION_LABEL_STYLE_DEFAULT') . '</option>"';
            $script[] = '          + "<option value=\"label-success\">' . JText::_('COM_PROJECTFORK_LABEL_OPTION_LABEL_STYLE_SUCCESS') . '</option>"';
            $script[] = '          + "<option value=\"label-warning\">' . JText::_('COM_PROJECTFORK_LABEL_OPTION_LABEL_STYLE_WARNING') . '</option>"';
            $script[] = '          + "<option value=\"label-important\">' . JText::_('COM_PROJECTFORK_LABEL_OPTION_LABEL_STYLE_IMPORTANT') . '</option>"';
            $script[] = '          + "<option value=\"label-info\">' . JText::_('COM_PROJECTFORK_LABEL_OPTION_LABEL_STYLE_INFO') . '</option>"';
            $script[] = '          + "<option value=\"label-inverse\">' . JText::_('COM_PROJECTFORK_LABEL_OPTION_LABEL_STYLE_INVERSE') . '</option>"';
            $script[] = '          + "</select>"';
            $script[] = '          + "' . JText::_('COM_PROJECTFORK_LABEL_PREVIEW') . ': "';
            $script[] = '          + "<span class=\"label\"><i class=\"icon-bookmark\"></i> ' . JText::_('COM_PROJECTFORK_LABEL_TITLE_PLACEHOLDER') . '</span>"';
            $script[] = '          + "<input type=\"hidden\" name=\"' . $this->name . '["+name+"][id][]\" value=\"0\"/>"';
            $script[] = '          + "</div></li>";';
            $script[] = '    ';
            $script[] = '    l.append(c);';
            $script[] = '    ' . $onchange;
            $script[] = '}';
            $script[] = 'function pfRemoveLabel_' . $this->id . '(el)';
            $script[] = '{';
            $script[] = '    jQuery(el).parent().parent().remove();';
            $script[] = '}';
            $script[] = 'function pfPreviewLabel_' . $this->id . '(el, data)';
            $script[] = '{';
            $script[] = '    var lbl = jQuery(el).parent().find(".label");';
            $script[] = '    if (lbl.length) {';
            $script[] = '        if (data == "text") {';
            $script[] = '            var v = jQuery(el).val();';
            $script[] = '            if (v == "") v = "' . JText::_('COM_PROJECTFORK_LABEL_TITLE_PLACEHOLDER') . '";';
            $script[] = '            lbl.html("<i class=\"icon-bookmark\"></i> " + v)';
            $script[] = '        }';
            $script[] = '        if (data == "style") {';
            $script[] = '            var v = jQuery(el).val();';
            $script[] = '            lbl.removeClass("label-success");';
            $script[] = '            lbl.removeClass("label-warning");';
            $script[] = '            lbl.removeClass("label-important");';
            $script[] = '            lbl.removeClass("label-info");';
            $script[] = '            lbl.removeClass("label-inverse");';
            $script[] = '            if (v != "") {';
            $script[] = '                lbl.addClass(v);';
            $script[] = '            }';
            $script[] = '        }';
            $script[] = '    }';
            $script[] = '}';
        }
        else {
            $script[] = 'function pfAddLabel_' . $this->id . '(asset, name)';
            $script[] = '{';
            $script[] = '    var l = jQuery("#' . $this->id . '_" + asset);';
            $script[] = '    var c = "<li>"';
            $script[] = '          + "<input type=\"text\" class=\"inputbox\" maxlength=\"32\""';
            $script[] = '          + "name=\"' . $this->name . '["+name+"][title][]\" placeholder=\"' . JText::_('COM_PROJECTFORK_LABEL_TITLE_PLACEHOLDER') . '\"/>"';
            $script[] = '          + "<select class=\"inputbox\" name=\"' . $this->name . '["+name+"][style][]\">"';
            $script[] = '          + "<option value=\"\">' . JText::_('COM_PROJECTFORK_LABEL_OPTION_LABEL_STYLE_DEFAULT') . '</option>"';
            $script[] = '          + "<option value=\"label-success\">' . JText::_('COM_PROJECTFORK_LABEL_OPTION_LABEL_STYLE_SUCCESS') . '</option>"';
            $script[] = '          + "<option value=\"label-warning\">' . JText::_('COM_PROJECTFORK_LABEL_OPTION_LABEL_STYLE_WARNING') . '</option>"';
            $script[] = '          + "<option value=\"label-important\">' . JText::_('COM_PROJECTFORK_LABEL_OPTION_LABEL_STYLE_IMPORTANT') . '</option>"';
            $script[] = '          + "<option value=\"label-info\">' . JText::_('COM_PROJECTFORK_LABEL_OPTION_LABEL_STYLE_INFO') . '</option>"';
            $script[] = '          + "<option value=\"label-inverse\">' . JText::_('COM_PROJECTFORK_LABEL_OPTION_LABEL_STYLE_INVERSE') . '</option>"';
            $script[] = '          + "</select>"';
            $script[] = '          + "<div class=\"button2-left\"><div class=\"blank\">"';
            $script[] = '          + "<a href=\"javascript:void(0);\" onclick=\"pfRemoveLabel_' . $this->id . '(this)\">' . JText::_('JACTION_DELETE') . '</a>"';
            $script[] = '          + "</div></div>"';
            $script[] = '          + "<input type=\"hidden\" name=\"' . $this->name . '["+name+"][id][]\" value=\"0\"/>"';
            $script[] = '          + "<div class=\"clr\"></div></li>";';
            $script[] = '    ';
            $script[] = '    l.append(c);';
            $script[] = '    ' . $onchange;
            $script[] = '}';
            $script[] = 'function pfRemoveLabel_' . $this->id . '(el)';
            $script[] = '{';
            $script[] = '    jQuery(el).parent().parent().parent().remove();';
            $script[] = '}';
        }


        return $script;
    }
}
