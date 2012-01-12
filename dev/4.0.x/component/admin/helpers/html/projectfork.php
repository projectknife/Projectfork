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
     * Renders a dropdown select-list to select the active project
     *
	 * @param    int     $value         The state value
     * @param    bool    $can_change
	 */
	static function activeProject($value = 0, $can_change = true)
	{
	    JHtml::_('behavior.modal', 'a.modal');

        $doc = JFactory::getDocument();
        $app = JFactory::getApplication();
        $uri = JFactory::getURI();

        // Get currently active project data
        $active_id    = (int) $app->getUserState('com_projectfork.active_project.id', 0);
        $active_title = $app->getUserState('com_projectfork.active_project.title', '');

        if(!$active_title) $active_title = 'Select';

        // Set the JS function
        $js = "
		function pfSelectActiveProject(id, title) {
			document.getElementById('active_project_id').value = id;
			document.getElementById('active_project_name').innerHTML = title;
			SqueezeBox.close();
            document.activeProjectForm.submit();
		}
        function pfClearActiveProject() {
			document.getElementById('active_project_id').value = 0;
			document.getElementById('active_project_name').innerHTML = '';
            document.activeProjectForm.submit();
		}";
		$doc->addScriptDeclaration($js);

        $btn_clear = '';
        if($active_id) {
            $btn_clear = '<a href="javascript: pfClearActiveProject();">Clear</a>';
        }

        // Set the modal window link
        $link = 'index.php?option=com_projectfork&amp;view=projects&amp;layout=modal&amp;tmpl=component&amp;function=pfSelectActiveProject';
        $return = base64_encode($uri->toString());

        // HTML output
	    $html = '<form method="post" action="'.JRoute::_('index.php?option=com_projectfork').'" name="activeProjectForm" id="activeProjectForm">'
              . JText::_('COM_PROJECTFORK_FIELD_ACTIVE_PROJECT_LABEL').': '
              . '<a href="'.$link.'" rel="{handler: \'iframe\', size: {x: 800, y: 450}}" class="modal" id="active_project_name">'
              . $active_title
              . '</a>'
              . $btn_clear
              . '<input type="hidden" name="id" value="'.$active_id.'" id="active_project_id"/>'
              . '<input type="hidden" name="task" value="activate" />'
              . '<input type="hidden" name="return" value="'.$return.'" />'
              . JHtml::_('form.token')
              . '</form>';

		return $html;
	}
}
