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
jimport('joomla.form.helper');
JFormHelper::loadFieldClass('list');


/**
 * Form Field class for selecting a milestone.
 *
 */
class JFormFieldMilestone extends JFormFieldList
{
    /**
     * The form field type.
     *
     * @var    string    
     */
    public $type = 'Milestone';


    /**
     * Method to get the field input markup.
     *
     * @return    string    The field input markup.
     */
    protected function getInput()
    {
        $attr   = '';
        $hidden = '<input type="hidden" id="' . $this->id . '_id" name="' . $this->name . '" value="0" />';

        // Initialize some field attributes.
        $attr .= $this->element['class']                         ? ' class="'.(string) $this->element['class'].'"' : '';
        $attr .= ((string) $this->element['disabled'] == 'true') ? ' disabled="disabled"'                          : '';
        $attr .= $this->element['size']                          ? ' size="'.(int) $this->element['size'].'"'      : '';
        $attr .= $this->multiple                                 ? ' multiple="multiple"'                          : '';

        // Handle onchange event attribute.
        if ((string) $this->element['submit'] == 'true') {
            $view = JRequest::getCmd('view');
            $attr = ' onchange="';
            if ($this->element['onchange']) $attr .= (string) $this->element['onchange'] . ';';
            $attr .= " Joomla.submitbutton('" . $view . ".setMilestone');";
            $attr .= '"';
        }
        else {
            $attr .= $this->element['onchange'] ? ' onchange="' . (string) $this->element['onchange'] . '"' : '';
        }

        // Get parent item field values.
        $project_id = (int) $this->form->getValue('project_id');
        $list_id    = (int) $this->form->getValue('list_id');

        if (!$project_id) {
            // Cant get milestone list without a project id.
            return '<span class="readonly">' . JText::_('COM_PROJECTFORK_FIELD_PROJECT_REQ') . '</span>' . $hidden;
        }

        // Get the field options.
        $options = $this->getOptions($project_id, $list_id);

        // Return if no options are available.
        if (count($options) == 0) {
            return '<span class="readonly">' . JText::_('COM_PROJECTFORK_FIELD_MILESTONE_EMPTY') . '</span>' . $hidden;
        }

        // Generate the list.
        return JHtml::_('select.genericlist', $options, $this->name, trim($attr), 'value', 'text', $this->value, $this->id);
    }


    /**
     * Method to get the field list options markup.
     *
     * @param     integer    $project    The currently selected project
     * @param     integer    $list       The currently selected task list
     * @return    array      $options    The list options markup.
     */
    protected function getOptions($project = 0, $list = 0)
    {
        $options = array();
        $user    = JFactory::getUser();
        $db      = JFactory::getDbo();
        $query   = $db->getQuery(true);

        // Get field attributes for the database query
        $query_state = ($this->element['state']) ? (int) $this->element['state'] : NULL;

        // Build the query
        $query->select('a.id AS value, a.title AS text')
              ->from('#__pf_milestones AS a')
              ->where('a.project_id = '. (int) $project);

        // Implement View Level Access
        if (!$user->authorise('core.admin', 'com_projectfork')) {
            $groups = implode(',', $user->getAuthorisedViewLevels());
            $query->where('a.access IN (' . $groups . ')');
        }

        // Filter state
        if (!is_null($query_state)) $query->where('a.state = ' . $query_state);

        // Filter list
        if ($list) {
            $query->join('INNER', '#__pf_task_lists AS l ON(l.id = ' . $list . ' AND l.milestone_id = a.id)');
        }

        $query->group('a.id')
              ->order('a.title');

        $db->setQuery((string) $query);
        $list = (array) $db->loadObjectList();

        // Generate the options
        if (count($list) > 0) {
            $options[] = JHtml::_('select.option', '',
                JText::alt('COM_PROJECTFORK_OPTION_SELECT_MILESTONE',
                preg_replace('/[^a-zA-Z0-9_\-]/', '_', $this->fieldname)),
                'value',
                'text'
            );
        }

        foreach($list AS $item)
        {
            // Create a new option object based on the <option /> element.
            $opt = JHtml::_('select.option', (string) $item->value,
                JText::alt(trim((string) $item->text),
                preg_replace('/[^a-zA-Z0-9_\-]/', '_', $this->fieldname)),
                'value',
                'text'
            );

            // Add the option object to the result set.
            $options[] = $opt;
        }

        return $options;
    }
}
