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
 * Form Field class for selecting a task.
 *
 */
class JFormFieldTask extends JFormFieldList
{
    /**
     * The form field type.
     *
     * @var    string
     */
    public $type = 'Task';


    protected $project;

    protected $milestone;

    protected $list;


    /**
     * Method to get the field input markup.
     *
     * @return    string    The field input markup.
     */
    protected function getInput()
    {
        // Initialize variables.
        $attr   = '';
        $hidden = '<input type="hidden" id="' . $this->id . '_id" name="' . $this->name . '" value="" />';

        // Initialize some field attributes.
        $attr .= $this->element['class']                         ? ' class="'.(string) $this->element['class'].'"'          : '';
        $attr .= ((string) $this->element['disabled'] == 'true') ? ' disabled="disabled"'                                   : '';
        $attr .= $this->element['size']                          ? ' size="'.(int) $this->element['size'].'"'               : '';
        $attr .= $this->multiple                                 ? ' multiple="multiple"'                                   : '';
        $attr .= $this->element['onchange']                      ? ' onchange="' .(string) $this->element['onchange'] . '"' : '';

        // Get parent item field values.
        $project   = (int) $this->form->getValue('project_id');
        $milestone = (int) $this->form->getValue('milestone_id');
        $list      = (int) $this->form->getValue('list_id');

        $this->project   = $project;
        $this->milestone = $milestone;
        $this->list      = $list;

        if (!$project) {
            // Cant get list without at least a project id.
            $this->form->setValue($this->element['name'], null, '');
            return '<span class="readonly">' . JText::_('COM_PROJECTFORK_FIELD_PROJECT_REQ') . '</span>' . $hidden;
        }

        // Get the field options.
        $options = $this->getOptions();

        // Return if no options are available.
        if (count($options) == 0) {
            $this->form->setValue($this->element['name'], null, '');
            return '<span class="readonly">' . JText::_('COM_PROJECTFORK_FIELD_TASK_EMPTY') . '</span>' . $hidden;
        }

        return JHtml::_('select.genericlist', $options, $this->name, trim($attr), 'value', 'text', $this->value, $this->id);
    }


    /**
     * Method to get the field list options markup.
     *
     * @return    array      $options      The list options markup.
     */
    protected function getOptions()
    {
        $options = array();
        $user    = JFactory::getUser();
        $db      = JFactory::getDbo();
        $query   = $db->getQuery(true);

        $project   = $this->project;
        $milestone = $this->milestone;
        $list      = $this->list;

        // Get field attributes for the database query
        $state = ($this->element['state']) ? (int) $this->element['state'] : NULL;

        // Build the query
        $query->select('a.id AS value, a.title AS text')
              ->from('#__pf_tasks AS a')
              ->where('a.project_id = '. (int) $project);

        // Implement View Level Access.
        if (!$user->authorise('core.admin')) {
            $groups = implode(',', $user->getAuthorisedViewLevels());
            $query->where('a.access IN (' . $groups . ')');
        }

        // Filter by milestone.
        if ($milestone) $query->where('a.milestone_id = ' . (int) $milestone);

        // Filter by list.
        if ($list) $query->where('a.list_id = ' . (int) $list);

        // Filter by state
        if (!is_null($state)) $query->where('a.state = ' . $db->quote($state));

        $query->order('a.title');

        $db->setQuery((string) $query);
        $items = (array) $db->loadObjectList();

        // Generate the options
        if (count($items) > 0) {
            $options[] = JHtml::_('select.option', '',
                JText::alt('COM_PROJECTFORK_OPTION_SELECT_TASK',
                preg_replace('/[^a-zA-Z0-9_\-]/', '_', $this->fieldname)),
                'value',
                'text'
            );
        }

        foreach($items AS $item)
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

        reset($options);

        return $options;
    }
}
