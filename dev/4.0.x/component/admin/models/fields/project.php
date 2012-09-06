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

        $submit = ($this->element['submit'] == 'true') ? true : false;
        $view   = (string) JRequest::getCmd('view');
        $title  = JText::_('COM_PROJECTFORK_SELECT_A_PROJECT');

        // Add the script to the document head.
        $script = $this->getJavascript($submit, $view);
        JFactory::getDocument()->addScriptDeclaration(implode("\n", $script));

        // Load the current project title a value is set.
        if ($this->value) {
            $title = $this->getProjectTitle();
        }

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
        if (JFactory::getApplication()->isSite()) {
            return $this->getSiteHTML($title);
        }

        return $this->getAdminHTML($title);
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
        $link = 'index.php?option=com_projectfork&amp;view=projects'
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
        $link = ProjectforkHelperRoute::getProjectsRoute()
              . '&amp;layout=modal&amp;tmpl=component'
              . '&amp;function=pfSelectProject_' . $this->id;

        // Initialize some field attributes.
        $attr  = $this->element['class'] ? ' class="'.(string) $this->element['class'].'"' : '';
        $attr .= $this->element['size']  ? ' size="'.(int) $this->element['size'].'"'      : '';

        // Create a dummy text field with the project title.
        $html[] = '<input type="text" id="' . $this->id . '_name" value="' . htmlspecialchars($title, ENT_COMPAT, 'UTF-8') . '" disabled="disabled"' . $attr . ' />';

        // Create the project select button.
        if ($this->element['readonly'] != 'true') {
            $html[] = '<a class="modal_' . $this->id . ' btn" title="' . JText::_('COM_PROJECTFORK_SELECT_PROJECT') . '"'
                    . ' href="' . JRoute::_($link) . '" rel="{handler: \'iframe\', size: {x: 800, y: 500}}">';
            $html[] = JText::_('COM_PROJECTFORK_SELECT_PROJECT') . '</a>';
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
    protected function getJavascript($submit = false, $view = NULL)
    {
        $script = array();

        $script[] = 'function pfSelectProject_' . $this->id . '(id, title)';
        $script[] = '{';
        $script[] = '    var old_id = document.getElementById("' . $this->id . '_id").value;';
        $script[] = '     if (old_id != id) {';
        $script[] = '         document.getElementById("' . $this->id . '_id").value = id;';
        $script[] = '         document.getElementById("' . $this->id . '_name").value = title;';
        $script[] = '         ' . ($submit ? 'Joomla.submitbutton("' . $view . '.setProject");' : 'SqueezeBox.close();');
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
