<?php
/**
 * @package      Projectfork.Library
 * @subpackage   Fields
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;


jimport('joomla.html.html');
jimport('joomla.form.formfield');


/**
 * Form Field class for selecting labels.
 *
 */
class JFormFieldLabels extends JFormField
{
    /**
     * The form field type.
     *
     * @var    string
     */
    public $type = 'Labels';


    /**
     * The selected labels
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
        if (!is_array($this->value)) {
            $this->value = array();
        }

        $this->items = array();

        foreach ($this->value AS $item)
        {
            if (isset($item->label_id)) {
                $this->items[] = $item->label_id;
            }
        }

        $html = $this->getHTML();

        return implode("\n", $html);
    }


    /**
     * Method to generate the input markup.
     *
     * @return    string              The html field markup
     */
    protected function getHTML()
    {
        if (JFactory::getApplication()->isSite() || version_compare(JVERSION, '3.0.0', 'ge')) {
            return $this->getSiteHTML();
        }

        return $this->getAdminHTML();
    }


    /**
     * Method to generate the backend input markup.
     *
     * @return    array     $html     The html field markup
     */
    protected function getAdminHTML()
    {
        $html  = array();
        $items = $this->getLabels();

        $html[] = '<fieldset class="checkboxes">';
        $html[] = '<ul>';

        foreach ($items AS $item)
        {
            $checked = (in_array($item->id, $this->items) ? ' checked="checked"' : '');

            $html[] = '<li>';
            $html[] = '<label>';
            $html[] = '<input type="checkbox" class="inputbox" name="' . $this->name . '[]" value="' . (int) $item->id . '"' . $checked . '/>';
            $html[] = htmlspecialchars($item->title, ENT_COMPAT, 'UTF-8');
            $html[] = '</label>';
            $html[] = '</li>';
        }

        $html[] = '</ul>';
        $html[] = '</fieldset>';
        $html[] = '<input type="hidden" name="' . $this->name . '[]" id="' . $this->id . '" value=""/>';

        return $html;
    }


    /**
     * Method to generate the frontend input markup.
     *
     * @return    array     $html     The html field markup
     */
    protected function getSiteHTML()
    {
        $html  = array();
        $items = $this->getLabels();

        $html[] = '<fieldset class="checkboxes">';
        $html[] = '<ul class="unstyled">';

        foreach ($items AS $item)
        {
            $checked = (in_array($item->id, $this->items) ? ' checked="checked"' : '');
            $class   = ($item->style != '' ? ' ' . $item->style : '');

            $html[] = '<li class="pull-left btn-group">';
            $html[] = '<label class="checkbox">';
            $html[] = '<input type="checkbox" class="inputbox" name="' . $this->name . '[]" value="' . (int) $item->id . '"' . $checked . '/>';
            $html[] = '<span class="label' . $class . '">' . htmlspecialchars($item->title, ENT_COMPAT, 'UTF-8') . '</span>';
            $html[] = '</label>';
            $html[] = '</li>';
        }

        $html[] = '</ul>';
        $html[] = '<div class="clearfix clr"></div>';
        $html[] = '</fieldset>';
        $html[] = '<input type="hidden" name="' . $this->name . '[]" id="' . $this->id . '" value=""/>';

        return $html;
    }


    /**
     * Method to get the the available labels
     *
     * @return    array      $script    The generated javascript
     */
    protected function getLabels()
    {
        $asset   = $this->element['asset'] ? $this->element['asset'] : 'com_pfprojects.project';
        $project = (int) $this->form->getValue('project_id');

        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);

        if (!$project) {
            return array();
        }

        $query->select('a.id, a.title, a.style')
              ->from('#__pf_labels AS a')
              ->where('a.project_id = ' . $db->quote((int) $project));

        if ($asset) {
            $query->where('(a.asset_group = ' . $db->quote($db->escape($asset)) . ' OR a.asset_group = ' . $db->quote('com_pfprojects.project') . ')');
        }
        else {
            $query->where('a.asset_group = ' . $db->quote('com_pfprojects.project'));
        }

        $query->order('a.asset_group, a.title ASC');

        $db->setQuery($query);
        $items = (array) $db->loadObjectList();

        return $items;
    }
}
