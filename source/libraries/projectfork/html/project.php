<?php
/**
* @package      Projectfork.Library
* @subpackage   html
*
* @author       Tobias Kuhn (eaxs)
* @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
* @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
**/

defined('_JEXEC') or die();


abstract class PFhtmlProject
{
    /**
     * Renders an input field with a select button for choosing a project
     *
     * @param     int       $value         The state value
     * @param     bool      $can_change
     *
     * @return    string                   The input field html
     */
    public static function filter($value = 0, $can_change = true)
    {
        JHtml::_('behavior.modal', 'a.modal');

        $doc = JFactory::getDocument();
        $app = JFactory::getApplication();

        // Get currently active project data
        $active_id    = (int) PFapplicationHelper::getActiveProjectId();
        $active_title = PFapplicationHelper::getActiveProjectTitle();

        if (!$active_title) $active_title = JText::_('COM_PROJECTFORK_SELECT_PROJECT');

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
            $btn_select = '<button type="button" class="btn" onclick="' . $js_select.'" title="'.JText::_('JSELECT').'"><i class="icon-briefcase"></i> ' . $active_title.'</button>';
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
}