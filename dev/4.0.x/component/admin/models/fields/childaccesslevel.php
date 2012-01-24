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

defined('JPATH_PLATFORM') or die;


jimport('joomla.html.html');
jimport('joomla.form.formfield');
jimport('joomla.form.helper');
JFormHelper::loadFieldClass('list');

require_once(JPATH_ADMINISTRATOR.'/components/com_projectfork/helpers/projectfork.php');


/**
 * Form Field class for the Joomla Platform.
 * Provides a list of access levels. Access levels control what users in specific
 * groups can see.
 *
 * @package     Joomla.Platform
 * @subpackage  Form
 * @since       11.1
 * @see         JAccess
 */
class JFormFieldChildAccessLevel extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 **/
	public $type = 'ChildAccessLevel';


	/**
	 * Method to get the field input markup.
	 *
	 * @return  string   The field input markup.
	 **/
	protected function getInput()
	{
		// Initialize variables.
		$attr = '';


		// Initialize some field attributes.
		$attr .= $this->element['class'] ? ' class="'.(string) $this->element['class'].'"' : '';
		$attr .= ((string) $this->element['disabled'] == 'true') ? ' disabled="disabled"' : '';
		$attr .= $this->element['size'] ? ' size="'.(int) $this->element['size'].'"' : '';
		$attr .= $this->multiple ? ' multiple="multiple"' : '';


		// Initialize JavaScript field attributes.
		$attr .= $this->element['onchange'] ? ' onchange="'.(string) $this->element['onchange'].'"' : '';


		// Get the field options and generate the list
		$options = $this->getOptions();
		$html    = JHtml::_('select.genericlist', $options, $this->name, trim($attr), 'value', 'text', $this->value, $this->id);


        return $html;
	}



    protected function getOptions()
	{
	    // Get custom options
        $options = parent::getOptions();


        // Find the access level of the parent item
	    $parent_field  = $this->element['parent_field']     ? (string) $this->element['parent_field']     : 'project_id';
	    $parent_type   = $this->element['parent_type']      ? (string) $this->element['parent_type']      : 'project';
	    $parent_prefix = $this->element['parent_prefix']    ? (string) $this->element['parent_prefix']    : 'JTable';
	    $parent_com    = $this->element['parent_component'] ? (string) $this->element['parent_component'] : 'com_projectfork';
	    $view          = $this->element['view']             ? (string) $this->element['view'] : (string) JRequest::getCmd('view');

        $parent_id = (int) $this->form->getValue($parent_field);
        $data = JFactory::getApplication()->getUserState($parent_com.'.edit'.$view.'.data', array());

        if(!$parent_id) return $options;


        // Get the table of the parent item
        JTable::addIncludePath(JPATH_ADMINISTRATOR.DS.'components'.DS.$parent_com.DS.'tables');

        $table = JTable::getInstance($parent_type, $parent_prefix);

        if(!$table) return $options;


        // Load the item
        if(!$table->load($parent_id)) return $options;


        // Find access level children
        $levels = ProjectforkHelper::getChildrenOfAccess($table->access);


        foreach($levels AS $level)
        {
            // Create a new option object based on the <option /> element.
			$tmp = JHtml::_('select.option', (string) $level->value, JText::alt(trim((string) $level->text), preg_replace('/[^a-zA-Z0-9_\-]/', '_', $this->fieldname)), 'value', 'text');

			// Add the option object to the result set.
			$options[] = $tmp;
        }

		reset($options);

		return $options;
	}
}
