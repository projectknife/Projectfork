<?php
/**
* @package   Projectfork
* @copyright Copyright (C) 2006-2011 Tobias Kuhn. All rights reserved.
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL, see license.txt
*
* This file is part of Projectfork.
*
* Projectfork is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
*
* Projectfork is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with Projectfork. If not, see <http://www.gnu.org/licenses/gpl.html>.
**/

// no direct access
defined('_JEXEC') or die;


/**
 * Abstract class for Projectfork HTML elements
 *
 */
abstract class JHtmlProjectfork
{
    /**
     * Renders an input field with a select button for choosing a project
     *
	 * @param    int     $value         The state value
     * @param    bool    $can_change
	 */
    static function filterProject($value = 0, $can_change = true)
    {
        JHtml::_('behavior.modal', 'a.modal');

        $doc = JFactory::getDocument();
        $app = JFactory::getApplication();


        // Get currently active project data
        $active_id    = (int) $app->getUserState('com_projectfork.project.active.id', 0);
        $active_title = $app->getUserState('com_projectfork.project.active.title', '');


        // Set the JS functions
        $link = 'index.php?option=com_projectfork&amp;view=projects&amp;layout=modal&amp;tmpl=component&amp;function=pfSelectActiveProject';
        $rel  = "{handler: 'iframe', size: {x: 800, y: 450}}";

        $js_clear = 'document.id(\'filter_project_title\').value = \'\';'
                  . 'document.id(\'filter_project\').value = \'0\';'
                  . 'this.form.submit();';

        $js_select = 'SqueezeBox.open(\''.$link.'\', '.$rel.');';

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
        if($active_id && $can_change) {
            $btn_clear = '<button type="button" class="btn" onclick="'.$js_clear.'">'.JText::_('JSEARCH_FILTER_CLEAR').'</button>';
        }

        $btn_select = '';
        if($can_change) {
            $btn_select = '<button type="button" class="btn modal" onclick="'.$js_select.'">'.JText::_('JSELECT').'</button>';
        }


        // HTML output
	    $html = '<label class="filter-project-lbl" for="filter_project_title">'.JText::_('COM_PROJECTFORK_FIELD_PROJECT_LABEL').':</label>'
              . '<input type="text" name="filter_project_title" id="filter_project_title" readonly="readonly" value="'.$active_title.'" />'
              . '<input type="hidden" name="filter_project" id="filter_project" value="'.$active_id.'" />'
              . $btn_select
              . $btn_clear;

		return $html;
    }
}
