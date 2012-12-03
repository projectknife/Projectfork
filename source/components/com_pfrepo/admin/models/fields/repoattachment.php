<?php
/**
 * @package      Projectfork
 * @subpackage   Repository
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;


jimport('joomla.html.html');
jimport('joomla.form.formfield');


/**
 * Form Field class for selecting or uploading an attachment.
 *
 */
class JFormFieldRepoAttachment extends JFormField
{
    /**
     * The form field type.
     *
     * @var    string
     */
    public $type = 'RepoAttachment';


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

        if (!is_array($this->value)) {
            $this->value = array();
        }

        $project = (int) $this->form->getValue('project_id');
        $hidden  = '<input type="hidden" name="' . $this->name . '[]" value="" />';

        if (!$project) {
            $project = PFApplicationHelper::getActiveProjectId();
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
     * @return    string              The html field markup
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
     * @return    array     $html     The html field markup
     */
    protected function getAdminHTML($project)
    {
        $html = array();
        $link = 'index.php?option=com_pfrepo&amp;view=repository'
              . '&amp;filter_project=' . (int) $project
              . '&amp;layout=modal&amp;tmpl=component'
              . '&amp;function=pfSelectAttachment_' . $this->id;

        $html[] = '<ul id="' . $this->id . '_list" class="unstyled">';

        foreach($this->value AS $item)
        {
            if (!isset($item->repo_data)) {
                continue;
            }

            if (empty($item->repo_data)) {
                continue;
            }

            list($asset, $id) = explode('.', $item->attachment, 2);

            $icon = '<i class="icon-file"></i> ';

            if ($asset == 'directory') {
                $icon = '<i class="icon-folder"></i> ';
            }

            if ($asset == 'note') {
                $icon = '<i class="icon-pencil"></i> ';
            }

            $html[] = '<li>';
            $html[] = '<div class="btn-group pull-left"><a class="btn btn-mini" onclick="pfRemoveAttachment_' . $this->id . '(this);"><i class="icon-remove"></i> </a></div>';
            $html[] = '&nbsp;';
            $html[] = '<span class="label">' . $icon . htmlspecialchars($item->repo_data->title, ENT_COMPAT, 'UTF-8') . '</span>';
            $html[] = '<input type="hidden" name="' . $this->name . '[]" value="' . htmlspecialchars($item->attachment, ENT_COMPAT, 'UTF-8') . '"/>';
            $html[] = '<div class="clearfix clr"></div>';
            $html[] = '</li>';
        }

        $html[] = '</ul>';
        $html[] = '<input type="hidden" name="' . $this->name . '[]" value=""/>';

        // Create the select button.
        if ($this->element['readonly'] != 'true') {
            $html[] = '<a class="modal_' . $this->id . ' btn" title="' . JText::_('COM_PROJECTFORK_SELECT_ATTACHMENT') . '"'
                    . ' href="' . JRoute::_($link) . '" rel="{handler: \'iframe\', size: {x: 720, y: 500}}">';
            $html[] = JText::_('COM_PROJECTFORK_SELECT_ATTACHMENT') . '</a>';
        }

        return $html;
    }


    /**
     * Method to generate the frontend input markup.
     *
     * @return    array     $html     The html field markup
     */
    protected function getSiteHTML($project)
    {
        $html = array();

        if (JFactory::getApplication()->isSite()) {
            $link = PFrepoHelperRoute::getRepositoryRoute($project)
                  . '&amp;layout=modal&amp;tmpl=component'
                  . '&amp;function=pfSelectAttachment_' . $this->id;
        }
        else {
            $link = 'index.php?option=com_pfrepo&amp;view=repository'
                  . '&amp;filter_project=' . (int) $project
                  . '&amp;layout=modal&amp;tmpl=component'
                  . '&amp;function=pfSelectAttachment_' . $this->id;
        }

        $html[] = '<ul id="' . $this->id . '_list" class="unstyled">';

        foreach($this->value AS $item)
        {
            if (!isset($item->repo_data)) {
                continue;
            }

            if (empty($item->repo_data)) {
                continue;
            }

            list($asset, $id) = explode('.', $item->attachment, 2);

            $icon = '<i class="icon-file"></i> ';

            if ($asset == 'directory') {
                $icon = '<i class="icon-folder"></i> ';
            }

            if ($asset == 'note') {
                $icon = '<i class="icon-pencil"></i> ';
            }

            $html[] = '<li>';
            $html[] = '<div class="btn-group pull-left"><a class="btn btn-mini" onclick="pfRemoveAttachment_' . $this->id . '(this);"><i class="icon-remove"></i> </a></div>';
            $html[] = '&nbsp;';
            $html[] = '<span class="label">' . $icon . htmlspecialchars($item->repo_data->title, ENT_COMPAT, 'UTF-8') . '</span>';
            $html[] = '<input type="hidden" name="' . $this->name . '[]" value="' . htmlspecialchars($item->attachment, ENT_COMPAT, 'UTF-8') . '"/>';
            $html[] = '<div class="clearfix clr"></div>';
            $html[] = '</li>';
        }


        $html[] = '</ul>';
        $html[] = '<input type="hidden" name="' . $this->name . '[]" value=""/>';

        // Create the select button.
        if ($this->element['readonly'] != 'true') {
            $html[] = '<a class="modal_' . $this->id . ' btn" title="' . JText::_('COM_PROJECTFORK_SELECT_ATTACHMENT') . '"'
                    . ' href="' . JRoute::_($link) . '" rel="{handler: \'iframe\', size: {x: 720, y: 500}}">';
            $html[] = JText::_('COM_PROJECTFORK_SELECT_ATTACHMENT') . '</a>';
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

        $script[] = 'function pfSelectAttachment_' . $this->id . '(id, title, atype)';
        $script[] = '{';
        $script[] = '    var l = jQuery("#' . $this->id . '_list");';
        $script[] = '    var i = "<i class=\"icon-file\"></i> "';
        $script[] = '    ';
        $script[] = '    if (atype == "directory") i = "<i class=\"icon-folder-close\"></i> ";';
        $script[] = '    if (atype == "note")      i = "<i class=\"icon-pencil\"></i> ";';
        $script[] = '    ';
        $script[] = '    var c = "<li><div class=\"btn-group pull-left\">"';
        $script[] = '          + "<a class=\"btn btn-mini\" onclick=\"pfRemoveAttachment_' . $this->id . '(this);\"><i class=\"icon-remove\"></i> </a>"';
        $script[] = '          + "</div>&nbsp;"';
        $script[] = '          + "<span class=\"label\">"';
        $script[] = '          + i + title';
        $script[] = '          + "</span><div class=\"clearfix\"></div>"';
        $script[] = '          + "<input type=\"hidden\" name=\"' . $this->name . '[]\" value=\"" + atype + "." + id + "\"/>"';
        $script[] = '          + "</li>"';
        $script[] = '    ';
        $script[] = '    l.append(c);';
        $script[] = '    SqueezeBox.close();';
        $script[] = '    ' . $onchange;
        $script[] = '}';
        $script[] = 'function pfRemoveAttachment_' . $this->id . '(el)';
        $script[] = '{';
        $script[] = '    jQuery(el).parent().parent().remove();';
        $script[] = '}';

        return $script;
    }


    /**
     * Method to get the title of the currently selected project
     *
     * @return    string    The project title
     */
    protected function getAttachmentTitle()
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
