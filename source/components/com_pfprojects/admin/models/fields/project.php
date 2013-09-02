<?php
/**
 * @package      Projectfork
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;


jimport('joomla.html.html');
jimport('joomla.form.formfield');
jimport('projectfork.framework');

/**
 * Form Field class for selecting a project.
 *
 */
class JFormFieldProject extends JFormField
{
    /**
     * The form field type.
     *
     * @var    string
     */
    public $type = 'Project';


    /**
     * Method to get the field input markup.
     *
     * @return    string    The html field markup
     */
    protected function getInput()
    {
        // Load the modal behavior script
        JHtml::_('behavior.modal', 'a.modal_' . $this->id);

        // Add the script to the document head.
        $script = $this->getJavascript();
        JFactory::getDocument()->addScriptDeclaration(implode("\n", $script));

        // Load the current project title a value is set.
        $title = ($this->value ? $this->getProjectTitle() : JText::_('COM_PROJECTFORK_SELECT_A_PROJECT'));

        if ($this->value == 0) $this->value = '';

        $html = $this->getHTML($title);

        return implode("\n", $html);
    }


    /**
     * Method to generate the input markup.
     *
     * @param     string    $title    The title of the current value
     *
     * @return    string              The html field markup
     */
    protected function getHTML($title)
    {
        if (JFactory::getApplication()->isSite() || version_compare(JVERSION, '3.0.0', 'ge')) {
            return $this->getHTMLSelect2($title);
            // return $this->getSiteHTML($title);
        }

        return $this->getAdminHTML($title);
    }


    protected function getHTMLSelect2($title)
    {
        JHtml::_('pfhtml.script.jQuerySelect2');

        static $field_id = 0;

        $field_id++;

        $doc = JFactory::getDocument();
        $app = JFactory::getApplication();




        // Prepare field attributes
        $can_change = isset($this->element['readonly']) ? (bool) $this->element['readonly'] : true;
        $onchange   = isset($this->element['onchange']) ? $this->element['onchange'] : '';
        $attr_read  = ($can_change ? '' : ' readonly="readonly"');
        $css_txt    = ($can_change ? '' : ' disabled muted') . (!empty($value) ? ' success' : ' warning');
        $value      = (int) $this->value;
        $placehold  = htmlspecialchars(JText::_('COM_PROJECTFORK_SELECT_PROJECT'), ENT_COMPAT, 'UTF-8');
        $title      = htmlspecialchars($title, ENT_COMPAT, 'UTF-8');

        if (empty($title)) {
            $title = $placehold;
        }

        // Query url
        $url = 'index.php?option=com_pfprojects&view=projects&tmpl=component&format=json&select2=1';

        // Prepare JS select2 script
        $js = array();
        $js[] = "jQuery(document).ready(function()";
        $js[] = "{";
        $js[] = "    jQuery('#" . $this->id . "_id').select2({";
        $js[] = "        placeholder: '" . $placehold . "',";
        if ($value) $js[] = "        allowClear: true,";
        $js[] = "        minimumInputLength: 0,";
        $js[] = "        ajax: {";
        $js[] = "            url: '" . $url . "',";
        $js[] = "            dataType: 'json',";
        $js[] = "            quietMillis: 200,";
        $js[] = "            data: function (term, page) {return {filter_search: term, limit: 10, limitstart: ((page - 1) * 10)};},";
        $js[] = "            results: function (data, page) {var more = (page * 10) < data.total;return {results: data.items, more: more};}";
        $js[] = "        },";
        $js[] = "        initSelection: function(element, callback) {";
        $js[] = "           callback({id:" . $value . ", text: '" . $title . "'});";
        $js[] = "        }";
        $js[] = "    });";
        $js[] = "    jQuery('#" . $this->id . "_id').change(function(){" . $onchange . "});";
        $js[] = "});";

        // Prepare html output
        $html = array();

        $html[] = '<input type="hidden" id="' . $this->id . '_id" name="' . $this->name . '" placeholder="' . $title . '"';
        $html[] = ' value="' . $value . '" autocomplete="off"' . $attr_read . ' class="input-large" tabindex="-1" />';

        if ($can_change) {
            // Add script
            JFactory::getDocument()->addScriptDeclaration(implode("\n", $js));
        }

        return $html;
    }

    /**
     * Method to generate the backend input markup.
     *
     * @param     string    $title    The title of the current value
     *
     * @return    array     $html     The html field markup
     */
    protected function getAdminHTML($title)
    {
        $html = array();
        $link = 'index.php?option=com_pfprojects&amp;view=projects'
              . '&amp;layout=modal&amp;tmpl=component'
              . '&amp;function=pfSelectProject_' . $this->id;

        // Initialize some field attributes.
        $attr = $this->element['class'] ? ' class="' . (string) $this->element['class'] . '"' : '';
        $attr .= $this->element['size'] ? ' size="' . (int) $this->element['size'] . '"'      : '';

        // Create a dummy text field with the project title.
        $html[] = '<div class="fltlft">';
        $html[] = '    <input type="text" id="' . $this->id . '_name" value="' . htmlspecialchars($title, ENT_COMPAT, 'UTF-8') . '" disabled="disabled"' . $attr . ' />';
        $html[] = '</div>';

        // Create the project select button.
        if ($this->element['readonly'] != 'true') {
            $html[] = '<div class="button2-left">';
            $html[] = '    <div class="blank">';
            $html[] = '<a class="modal_' . $this->id . '" title="' . JText::_('COM_PROJECTFORK_SELECT_PROJECT') . '"'
                    . ' href="' . $link . '" rel="{handler: \'iframe\', size: {x: 800, y: 500}}">';
            $html[] = JText::_('COM_PROJECTFORK_SELECT_PROJECT') . '</a>';
            $html[] = '    </div>';
            $html[] = '</div>';
        }

        // Create the hidden field, that stores the id.
        $html[] = '<input type="hidden" id="' . $this->id . '_id" name="' . $this->name . '" value="' . htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8') . '" />';

        return $html;
    }


    /**
     * Method to generate the frontend input markup.
     *
     * @param     string    $title    The title of the current value
     *
     * @return    array     $html     The html field markup
     */
    protected function getSiteHTML($title)
    {
        $html = array();
        $isJ3 = version_compare(JVERSION, '3.0.0', 'ge');

        if (JFactory::getApplication()->isSite()) {
            $link = PFprojectsHelperRoute::getProjectsRoute()
                  . '&amp;layout=modal&amp;tmpl=component'
                  . '&amp;function=pfSelectProject_' . $this->id;
        }
        else {
            $link = 'index.php?option=com_pfprojects&amp;view=projects'
                  . '&amp;layout=modal&amp;tmpl=component'
                  . '&amp;function=pfSelectProject_' . $this->id;
        }

        // Initialize some field attributes.
        $attr  = $this->element['class'] ? ' class="'.(string) $this->element['class'].'"' : '';
        $attr .= $this->element['size']  ? ' size="'.(int) $this->element['size'].'"'      : '';

        if ($isJ3) {
            $html[] = '<div class="input-append">';
        }
        // Create a dummy text field with the project title.
        $html[] = '<input type="text" id="' . $this->id . '_name" value="' . htmlspecialchars($title, ENT_COMPAT, 'UTF-8') . '" disabled="disabled"' . $attr . ' />';

        // Create the project select button.
        if ($this->element['readonly'] != 'true') {
            $html[] = '<a class="modal_' . $this->id . ' btn" title="' . JText::_('COM_PROJECTFORK_SELECT_PROJECT') . '"'
                    . ' href="' . JRoute::_($link) . '" rel="{handler: \'iframe\', size: {x: 800, y: 500}}">';
            $html[] = JText::_('COM_PROJECTFORK_SELECT_PROJECT') . '</a>';
        }

        if ($isJ3) {
            $html[] = '</div>';
        }

        // Create the hidden field, that stores the id.
        $html[] = '<input type="hidden" id="' . $this->id . '_id" name="' . $this->name . '" value="' . (int) $this->value . '" />';

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

        $script[] = 'function pfSelectProject_' . $this->id . '(id, title)';
        $script[] = '{';
        $script[] = '    var old_id = document.getElementById("' . $this->id . '_id").value;';
        $script[] = '     if (old_id != id) {';
        $script[] = '         document.getElementById("' . $this->id . '_id").value = id;';
        $script[] = '         document.getElementById("' . $this->id . '_name").value = title;';
        $script[] = '         SqueezeBox.close(); ';
        $script[] = '         ' . $onchange;
        $script[] = '     }';
        $script[] = '}';

        return $script;
    }


    /**
     * Method to get the title of the currently selected project
     *
     * @return    string    The project title
     */
    protected function getProjectTitle()
    {
        $default = JText::_('COM_PROJECTFORK_SELECT_A_PROJECT');

        if (empty($this->value)) {
            return $default;
        }

        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->select('title')
              ->from('#__pf_projects')
              ->where('id = ' . $db->quote($this->value));

        $db->setQuery((string) $query);
        $title = $db->loadResult();

        if (empty($title)) {
            return $default;
        }

        return $title;
    }
}
