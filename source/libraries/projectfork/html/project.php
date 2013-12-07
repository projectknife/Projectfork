<?php
/**
* @package      pkg_projectfork
* @subpackage   lib_projectfork
*
* @author       Tobias Kuhn (eaxs)
* @copyright    Copyright (C) 2006-2013 Tobias Kuhn. All rights reserved.
* @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
**/

defined('_JEXEC') or die();


abstract class PFhtmlProject
{
    /**
     * Renders a filter input field for selecting a project
     *
     * @param     int       $value         The state value
     * @param     bool      $can_change
     *
     * @return    string                   The input field html
     */
    public static function filter($value = 0, $can_change = true)
    {
        $app = JFactory::getApplication();

        if (version_compare(JVERSION, '3.0', 'lt') && $app->isAdmin()) {
            // Show the modal window selector in the backend of J2.5
            return self::modal($value, $can_change);
        }

        // For all other versions and locations show the typeahead
        // return self::typeahead($value, $can_change);

        // Show select2 dropdown
        return self::select2($value, $can_change);
    }


    /**
     * Renders an input field which opens a modal window for selecting a project
     *
     * @param     int       $value         The state value
     * @param     bool      $can_change
     *
     * @return    string                   The input field html
     */
    public static function modal($value = 0, $can_change = true)
    {
        JHtml::_('behavior.modal', 'a.modal');

        $doc = JFactory::getDocument();
        $app = JFactory::getApplication();

        // Get currently active project data
        $active_id    = (int) PFApplicationHelper::getActiveProjectId();
        $active_title = PFApplicationHelper::getActiveProjectTitle();

        $tt_class = '';
        $tt_title = ' title="::' . JText::_('JSELECT') . '"';

        if (!$active_title) {
            $active_title = JText::_('COM_PROJECTFORK_SELECT_PROJECT');
        }
        else {
            if (strlen($active_title) > 30) {
                $tt_class     = ' hasTip';
                $tt_title     = ' title="' . JText::_('JSELECT') . '::' . htmlspecialchars($active_title, ENT_COMPAT, 'UTF-8') . '"';
                $active_title = JHtml::_('pf.html.truncate', $active_title, 30);
            }
        }

        // Set the JS functions
        $link = 'index.php?option=com_pfprojects&amp;view=projects&amp;layout=modal&amp;tmpl=component&amp;function=pfSelectActiveProject';
        $rel  = "{handler: 'iframe', size: {x: 800, y: 450}}";

        $js_clear = 'document.id(\'filter_project_title\').value = \'\';'
                  . 'document.id(\'filter_project\').value = \'0\';'
                  . 'this.form.submit();';

        $js_select = 'SqueezeBox.open(\'' . $link.'\', ' . $rel.');';

        $js_head = "
        function pfSelectActiveProject(id, title) {
            document.getElementById('filter_project').value = id;
            document.getElementById('filter_project_title').value = title;
            SqueezeBox.close();
            Joomla.submitbutton('');
        }";
        $doc->addScriptDeclaration($js_head);

        // Setup the buttons
        $btn_clear = '';
        if ($active_id && $can_change) {
            $btn_clear = '<button type="button" class="btn" onclick="' . $js_clear.'"><i class="icon-remove"></i> '.JText::_('JSEARCH_FILTER_CLEAR').'</button>';
        }

        $btn_select = '';
        if ($can_change) {
            $btn_select  = '<button type="button" class="btn' . $tt_class . '" onclick="' . $js_select . '"' . $tt_title . '> ';
            $btn_select .= '<span aria-hidden="true" class="icon-briefcase"></span> ' . $active_title;
            $btn_select .= '</button>';
        }

        // HTML output
        $html = '<span class="btn-group">'
                . $btn_select
                . $btn_clear
                . '</span>'
                .'<span class="btn-group">'
                . '<input type="hidden" name="filter_project_title" id="filter_project_title" class="btn disabled input-small" readonly="readonly" value="' . $active_title.'" />'
                . '<input type="hidden" name="filter_project" id="filter_project" value="' . $active_id.'" />'
                . '</span>';

        return $html;
    }


    /**
     * Renders a typeahead input field for selecting a project
     *
     * @param     int       $value         The state value
     * @param     bool      $can_change
     *
     * @return    string                   The input field html
     */
    public static function typeahead($value = 0, $can_change = true)
    {
        static $field_id = 0;

        $doc = JFactory::getDocument();
        $app = JFactory::getApplication();

        // Get currently active project data
        $active_id    = (int) PFApplicationHelper::getActiveProjectId();
        $active_title = PFApplicationHelper::getActiveProjectTitle();

        $field_id++;

        // Prepare title value
        $title_val = htmlspecialchars($active_title, ENT_COMPAT, 'UTF-8');

        // Prepare field attributes
        $attr_read = ($can_change ? '' : ' readonly="readonly"');
        $css_txt   = ($can_change ? '' : ' disabled muted') . ($active_id ? ' success' : ' warning');
        $placehold = htmlspecialchars(JText::_('COM_PROJECTFORK_SELECT_PROJECT'), ENT_COMPAT, 'UTF-8');

        // Query url
        $url = 'index.php?option=com_pfprojects&view=projects&tmpl=component&format=json&typeahead=1&limit=10&filter_search=';

        // Prepare JS typeahead script
        $js = array();
        $js[] = "jQuery(document).ready(function()";
        $js[] = "{";
        $js[] = "    var pta_done" . $field_id . " = true;";
        $js[] = "    ";
        $js[] = "    jQuery('#filter_project_title" . $field_id . "').typeahead({";
        $js[] = "        source: function(query, process)";
        $js[] = "        {";
        $js[] = "            if (!pta_done" . $field_id . ") return {};";
        $js[] = "            pta_done" . $field_id . " = false;";
        $js[] = "            jQuery.getJSON('" . $url . "' + query, {}, function(response)";
        $js[] = "            {";
        $js[] = "                var data = new Array();";
        $js[] = "                for(var i in response) { if (response.hasOwnProperty(i)) {data.push(i+'_'+response[i]);} }";
        $js[] = "                process(data);";
        $js[] = "                pta_done" . $field_id . " = true;";
        $js[] = "            });";
        $js[] = "        },";
        $js[] = "        highlighter: function(item)";
        $js[] = "        {";
        $js[] = "            var parts = item.split('_');";
        $js[] = "            parts.shift();";
        $js[] = "            return parts.join('_');";
        $js[] = "        },";
        $js[] = "        updater: function(item)";
        $js[] = "        {";
        $js[] = "            var parts = item.split('_');";
        $js[] = "            jQuery('#filter_project_id" . $field_id . "').val(parts.shift());";
        $js[] = "            jQuery('#filter_project_title" . $field_id . "').addClass('disabled muted');";
        $js[] = "            jQuery('#filter_project_title" . $field_id . "').attr('readonly', 'readonly');";
        $js[] = "            jQuery('#filter_project_id" . $field_id . "').change();";
        $js[] = "            return parts.join('_');";
        $js[] = "        },";
        $js[] = "        minLength: 0,";
        $js[] = "        items: 5";
        $js[] = "    });";
        $js[] = "    jQuery('#filter_project_id" . $field_id . "').change(function()";
        $js[] = "    {";
        $js[] = "        this.form.submit();";
        $js[] = "    });";
        $js[] = "    jQuery('#filter_project_title" . $field_id . "').click(function()";
        $js[] = "    {";
        $js[] = "        jQuery(this).select();";
        $js[] = "    });";
        $js[] = "});";

        // Prepare html output
        $html = array();
        $html[] = '<span class="btn-group form-inline input-append">';

        $html[] = '<input type="text" id="filter_project_title' . $field_id . '" class="input-medium' . $css_txt . '"';
        $html[] = ' autocomplete="off" ' . $attr_read . ' value="' . $title_val.'" placeholder="' . $placehold . '" />';

        if ($active_id && $can_change) {
            $clr_js = 'jQuery(\'#filter_project_id' . $field_id . '\').val(\'0\').change();';
            $html[] = '<button type="button" class="btn" onclick="' . $clr_js . '"><i class="icon-remove"></i></button>';
        }
        elseif (!$active_id) {
            $html[] = '<span class="add-on"><i class="icon-briefcase"></i></span>';
        }
        elseif (!$can_change) {
            $html[] = '<span class="add-on"><i class="icon-lock"></i></span>';
        }

        $html[] = '</span>';



        $html[] = '<input type="hidden" id="filter_project_id' . $field_id . '" name="filter_project"';
        $html[] = ' value="' . $active_id.'" autocomplete="off"' . $attr_read . '/>';

        if ($can_change) {
            // Add script
            JFactory::getDocument()->addScriptDeclaration(implode("\n", $js));
        }

        return implode("\n", $html);
    }


    /**
     * Renders a select2 input field for selecting a project
     *
     * @param     int       $value         The state value
     * @param     bool      $can_change
     *
     * @return    string                   The input field html
     */
     public static function select2($value = 0, $can_change = true)
     {
        JHtml::_('pfhtml.script.jQuerySelect2');

        static $field_id = 0;

        $doc = JFactory::getDocument();
        $app = JFactory::getApplication();

        // Get currently active project data
        $active_id    = (int) PFApplicationHelper::getActiveProjectId();
        $active_title = PFApplicationHelper::getActiveProjectTitle();

        $field_id++;

        // Prepare title value
        $title_val = htmlspecialchars($active_title, ENT_COMPAT, 'UTF-8');

        // Prepare field attributes
        $attr_read = ($can_change ? '' : ' readonly="readonly"');
        $css_txt   = ($can_change ? '' : ' disabled muted') . ($active_id ? ' success' : ' warning');
        $placehold = htmlspecialchars(JText::_('COM_PROJECTFORK_SELECT_PROJECT'), ENT_COMPAT, 'UTF-8');

        // Query url
        $url = 'index.php?option=com_pfprojects&view=projects&tmpl=component&format=json&select2=1';

        // Prepare JS typeahead script
        $js = array();
        $js[] = "jQuery(document).ready(function()";
        $js[] = "{";
        $js[] = "    jQuery('#filter_project_id" . $field_id . "').select2({";
        $js[] = "        placeholder: '" . $placehold . "',";
        if ($active_id) $js[] = "        allowClear: true,";
        $js[] = "        minimumInputLength: 0,";
        $js[] = "        ajax: {";
        $js[] = "            url: '" . $url . "',";
        $js[] = "            dataType: 'json',";
        $js[] = "            quietMillis: 200,";
        $js[] = "            data: function (term, page) {return {filter_search: term, limit: 10, limitstart: ((page - 1) * 10)};},";
        $js[] = "            results: function (data, page) {var more = (page * 10) < data.total;return {results: data.items, more: more};}";
        $js[] = "        },";
        $js[] = "        escapeMarkup:function(markup) { return markup; },";
        $js[] = "        initSelection: function(element, callback) {";
        $js[] = "           callback({id:" . $active_id . ", text: '" . ($active_id ? $active_title : $placehold) . "'});";
        $js[] = "        }";
        $js[] = "    });";
        $js[] = "    jQuery('#filter_project_id" . $field_id . "').change(function(){this.form.submit();});";
        $js[] = "});";

        // Prepare html output
        $html = array();

        $html[] = '<input type="hidden" id="filter_project_id' . $field_id . '" name="filter_project" placeholder="' . $placehold . '"';
        $html[] = ' value="' . $active_id . '" autocomplete="off"' . $attr_read . ' class="input-large" tabindex="-1" />';

        if ($can_change) {
            // Add script
            JFactory::getDocument()->addScriptDeclaration(implode("\n", $js));
        }

        return implode("\n", $html);
     }
}
