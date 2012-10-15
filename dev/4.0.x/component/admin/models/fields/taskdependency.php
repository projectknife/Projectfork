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


/**
 * Form Field class for selecting a task dependency.
 *
 */
class JFormFieldTaskDependency extends JFormField
{
    /**
     * The form field type.
     *
     * @var    string
     */
    public $type = 'TaskDependency';


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

        $project = (int) $this->form->getValue('project_id');
        $hidden  = '<input type="hidden" name="' . $this->name . '[]" value="" />';

        if (!$project) {
            $project = ProjectforkHelper::getActiveProjectId();
        }

        if (!$project) {
            return '<span class="readonly">' . JText::_('COM_PROJECTFORK_FIELD_PROJECT_REQ') . '</span>' . $hidden;
        }

        $html = $this->getHTML($project);

        return implode("\n", $html);
    }


    /**
     * Method to generate the input markup.
     *
     * @return    string    The html field markup
     */
    protected function getHTML($project)
    {
        if (JFactory::getApplication()->isSite() || version_compare(JVERSION, '3.0.0', 'ge')) {
            return $this->getSiteHTML($project);
        }

        return $this->getAdminHTML($project);
    }


    /**
     * Method to generate the backend input markup.
     *
     * @return    array    $html    The html field markup
     */
    protected function getAdminHTML($project)
    {
        $html    = array();
        $options = $this->getOptions($project);

        $html[] = '<ul id="' . $this->id . '_list" class="unstyled">';

        foreach($this->value AS $item)
        {
            $html[] = '<li>';
            $html[] = JHtml::_('select.genericlist', $options, $this->name . '[]', null, 'value', 'text', $item);
            $html[] = '<div class="button2-left"><div class="blank">';
            $html[] = '<a class="btn" href="javascript:void(0);" onclick="pfRemoveTaskDependency_' . $this->id . '(this);">';
            $html[] = JText::_('COM_PROJECTFORK_FIELD_CLEAR_LABEL');
            $html[] = '</a>';
            $html[] = '</div></div>';
            $html[] = '<div class="clr"></div>';
            $html[] = '</li>';
        }

        $html[] = '</ul>';

        // Add a hidden list and dropdown which serves as template for the JS-add butotn
        $html[] = '<ul style="display:none !important">';
        $html[] = '<li>';
        $html[] = JHtml::_('select.genericlist', $options, $this->name . '[]', null, 'value', 'text', null, $this->id);
        $html[] = '<div class="button2-left"><div class="blank">';
        $html[] = '<a class="btn" href="javascript:void(0);" onclick="pfRemoveTaskDependency_' . $this->id . '(this);">';
        $html[] = JText::_('COM_PROJECTFORK_FIELD_CLEAR_LABEL');
        $html[] = '</a>';
        $html[] = '</div></div>';
        $html[] = '<div class="clr"></div>';
        $html[] = '</li>';
        $html[] = '</ul>';

        // Create the "add" button.
        if ($this->element['readonly'] != 'true') {
            $html[] = '<div class="button2-left"><div class="blank">';
            $html[] = '<a class="btn" href="javascript:void(0);" onclick="pfAddTaskDependency_' . $this->id . '();">';
            $html[] = JText::_('JACTION_ADD_DEPENDENCY');
            $html[] = '</a>';
            $html[] = '</div></div>';
            $html[] = '<div class="clr"></div>';
        }

        return $html;
    }


    /**
     * Method to generate the frontend input markup.
     *
     * @return    array    $html    The html field markup
     */
    protected function getSiteHTML($project)
    {
        $html    = array();
        $options = $this->getOptions($project);

        $html[] = '<ul id="' . $this->id . '_list" class="unstyled">';

        foreach($this->value AS $item)
        {
            $html[] = '<li>';
            $html[] = '<a class="btn btn-mini" href="javascript:void(0);" onclick="pfRemoveTaskDependency_' . $this->id . '(this);">';
            $html[] = '<i class="icon-remove"></i> ';
            $html[] = '</a>';
            $html[] = JHtml::_('select.genericlist', $options, $this->name . '[]', null, 'value', 'text', $item);
            $html[] = '</li>';
        }

        $html[] = '</ul>';

        // Add a hidden list and dropdown which serves as template for the JS-add butotn
        $html[] = '<ul style="display:none !important">';
        $html[] = '<li>';
        $html[] = '<a class="btn btn-mini" href="javascript:void(0);" onclick="pfRemoveTaskDependency_' . $this->id . '(this);">';
        $html[] = '<i class="icon-remove"></i> ';
        $html[] = '</a>';
        $html[] = JHtml::_('select.genericlist', $options, $this->name . '[]', null, 'value', 'text', null, $this->id);
        $html[] = '</li>';
        $html[] = '</ul>';

        // Create the "add" button.
        if ($this->element['readonly'] != 'true') {
            $html[] = '<a class="btn" href="javascript:void(0);" onclick="pfAddTaskDependency_' . $this->id . '();">';
            $html[] = JText::_('JACTION_ADD_DEPENDENCY');
            $html[] = '</a>';
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

        $script[] = 'function pfAddTaskDependency_' . $this->id . '()';
        $script[] = '{';
        $script[] = '    var l = jQuery("#' . $this->id . '_list");';
        $script[] = '    var s = jQuery("#' . $this->id . '").closest("li").clone();';
        $script[] = '    l.append(s);';
        $script[] = '    ' . $onchange;
        $script[] = '}';
        $script[] = 'function pfRemoveTaskDependency_' . $this->id . '(el)';
        $script[] = '{';
        $script[] = '    jQuery(el).closest("li").remove();';
        $script[] = '}';

        return $script;
    }


    /**
     * Method to get the task select options
     *
     * @param     integer    $project    The project id
     * @return    array                  The options
     */
    protected function getOptions($project)
    {
        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);
        $opts  = array();

        $query->select('a.id AS value, a.title AS text')
              ->from('#__pf_tasks AS a')
              ->where('a.complete = 0')
              ->where('a.state = 1')
              ->where('a.project_id = ' . (int) $project)
              ->order('a.title ASC');

        $db->setQuery($query);
        $items = (array) $db->loadObjectList();

        $opts[] = JHtml::_('select.option', '', JText::_('COM_PROJECTFORK_OPTION_SELECT_TASK'));

        foreach ($items AS $item)
        {
            $opts[] = JHtml::_('select.option', $item->value, $item->text);
        }

        return $opts;
    }
}
