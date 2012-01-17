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

defined('JPATH_BASE') or die;

jimport('joomla.html.html');
jimport('joomla.access.access');
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
	 * @var		string
	 */
	public $type = 'Project';


	/**
	 * Method to get the field input markup.
	 *
	 * TODO: Add access check.
	 *
	 * @return	string	The field input markup.
	 * @since	1.6
	 */
	protected function getInput()
	{
		// Initialize variables.
        $app      = JFactory::getApplication();
		$html     = array();
		$groups   = $this->getGroups();
		$excluded = $this->getExcluded();
		$link = 'index.php?option=com_projectfork&amp;view=projects&amp;layout=modal&amp;tmpl=component
                 &amp;function=pfSelectProject_'.$this->id;

		// Initialize some field attributes.
		$attr  = $this->element['class'] ? ' class="'.(string) $this->element['class'].'"' : '';
		$attr .= $this->element['size']  ? ' size="'.(int) $this->element['size'].'"'      : '';

		// Initialize JavaScript field attributes.
		$onchange = (string) $this->element['onchange'];

		// Load the modal behavior script.
		JHtml::_('behavior.modal', 'a.modal_'.$this->id);

		// Build the script.
		$script = array();
		$script[] = '	function pfSelectProject_'.$this->id.'(id, title) {';
		$script[] = '		var old_id = document.getElementById("'.$this->id.'_id").value;';
		$script[] = '		if (old_id != id) {';
		$script[] = '			document.getElementById("'.$this->id.'_id").value = id;';
		$script[] = '			document.getElementById("'.$this->id.'_name").value = title;';
		$script[] = '			'.$onchange;
		$script[] = '		}';
		$script[] = '		SqueezeBox.close();';
		$script[] = '		window.location.reload();';
		$script[] = '	}';

		// Add the script to the document head.
		JFactory::getDocument()->addScriptDeclaration(implode("\n", $script));

		// Load the current project title if available.
		$table = JTable::getInstance('project');
		if ($this->value) {
			$table->load($this->value);
		} else {
		    $active_id = (int) $app->getUserState('com_projectfork.active_project.id', 0);

            if($active_id) {
                $table->load($active_id);
                $this->value = $active_id;
            }
            else {
                $table->title = JText::_('COM_PROJECTFORK_SELECT_A_PROJECT');
            }
		}

		// Create a dummy text field with the project title.
		$html[] = '<div class="fltlft">';
		$html[] = '	<input type="text" id="'.$this->id.'_name"' .
			' value="'.htmlspecialchars($table->title, ENT_COMPAT, 'UTF-8').'"' .
			' disabled="disabled"'.$attr.' />';
		$html[] = '</div>';

		// Create the project select button.
		$html[] = '<div class="button2-left">';
		$html[] = '  <div class="blank">';
		if ($this->element['readonly'] != 'true') {
			$html[] = '		<a class="modal_'.$this->id.'" title="'.JText::_('COM_PROJECTFORK_SELECT_PROJECT').'"' .
				' href="'.$link.'"' .
				' rel="{handler: \'iframe\', size: {x: 800, y: 500}}">';
			$html[] = '			'.JText::_('COM_PROJECTFORK_SELECT_PROJECT').'</a>';
		}
		$html[] = '  </div>';
		$html[] = '</div>';

		// Create the real field, hidden, that stored the project id.
		$html[] = '<input type="hidden" id="'.$this->id.'_id" name="'.$this->name.'" value="'.(int) $this->value.'" />';

		return implode("\n", $html);
	}


	/**
	 * Method to get the filtering groups (null means no filtering)
	 *
	 * @return  mixed  array of filtering groups or null.
	 */
	protected function getGroups()
	{
		return null;
	}


	/**
	 * Method to get the projects to exclude from the list of projects
	 *
	 * @return  mixed  Array of users to exclude or null to to not exclude them
	 */
	protected function getExcluded()
	{
		return null;
	}
}
