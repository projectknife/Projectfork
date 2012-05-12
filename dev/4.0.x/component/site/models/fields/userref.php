<?php
/**
* @package   Projectfork
* @copyright Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
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

jimport('joomla.form.helper');
JFormHelper::loadFieldClass('user');


/**
 * Field to select a user id from a modal list.
 *
 */
class JFormFieldUserRef extends JFormFieldUser
{
	/**
	 * The form field type.
     *
	 */
	public $type = 'UserRef';

    /**
	 * Method to get the user field input markup.
	 *
	 * @return  string  The field input markup.
	 */
	protected function getInput()
	{
	    if($this->value) {
	        $groups = $this->getGroups();
	        $user   = JFactory::getUser($this->value);

            $user_groups = $user->getAuthorisedGroups();
            $authorize   = false;

            foreach($user_groups AS $group)
            {
                $authorize = in_array($group, $groups);
            }

            if(!$authorize) $this->value = '';
	    }

        // Initialize variables.
		$html = array();
		$groups = $this->getGroups();
		$excluded = $this->getExcluded();
		$link = 'index.php?option=com_projectfork&amp;view=users&amp;layout=modal&amp;tmpl=component&amp;field=' . $this->id
			. (isset($groups) ? ('&amp;groups=' . base64_encode(json_encode($groups))) : '')
			. (isset($excluded) ? ('&amp;excluded=' . base64_encode(json_encode($excluded))) : '');

		// Initialize some field attributes.
		$attr = $this->element['class'] ? ' class="' . (string) $this->element['class'] . '"' : '';
		$attr .= $this->element['size'] ? ' size="' . (int) $this->element['size'] . '"' : '';

		// Initialize JavaScript field attributes.
		$onchange = (string) $this->element['onchange'];

		// Load the modal behavior script.
		JHtml::_('behavior.modal', 'a.modal_' . $this->id);

		// Build the script.
		$script = array();
		$script[] = '	function jSelectUser_' . $this->id . '(id, title) {';
		$script[] = '		var old_id = document.getElementById("' . $this->id . '_id").value;';
		$script[] = '		if (old_id != id) {';
		$script[] = '			document.getElementById("' . $this->id . '_id").value = id;';
		$script[] = '			document.getElementById("' . $this->id . '_name").value = title;';
		$script[] = '			' . $onchange;
		$script[] = '		}';
		$script[] = '		SqueezeBox.close();';
		$script[] = '	}';

		// Add the script to the document head.
		JFactory::getDocument()->addScriptDeclaration(implode("\n", $script));

		// Load the current username if available.
		$table = JTable::getInstance('user');
		if ($this->value)
		{
			$table->load($this->value);
		}
		else
		{
			$table->username = JText::_('JLIB_FORM_SELECT_USER');
		}

		// Create a dummy text field with the user name.
		$html[] = '	<input type="text" id="' . $this->id . '_name"' . ' value="' . htmlspecialchars($table->name, ENT_COMPAT, 'UTF-8') . '"'
			    . ' disabled="disabled"' . $attr . ' />';


		// Create the user select button.
		if ($this->element['readonly'] != 'true')
		{
			$html[] = '		<a class="modal_' . $this->id . ' btn" title="' . JText::_('JLIB_FORM_CHANGE_USER') . '"' . ' href="' . $link . '"'
				. ' rel="{handler: \'iframe\', size: {x: 800, y: 500}}">';
			$html[] = '			' . JText::_('JLIB_FORM_CHANGE_USER') . '</a>';
		}

		// Create the real field, hidden, that stored the user id.
		$html[] = '<input type="hidden" id="' . $this->id . '_id" name="' . $this->name . '" value="' . (int) $this->value . '" />';


        $js_clear = 'document.id(\''.$this->id.'_name\').value = \'\';'
              . 'document.id(\''.$this->id.'_id\').value = \'0\';';

		if ($this->element['readonly'] != 'true')
		{
			$html[] = '		<a onclick="'.$js_clear.'" class="btn">';
			$html[] = '		' . JText::_('COM_PROJECTFORK_FIELD_CLEAR_LABEL') . '</a>';
		}


		return implode("\n", $html);
    }


	/**
	 * Method to get the filtering groups (null means no filtering)
	 *
	 * @return  mixed  array of filtering groups.
	 */
	protected function getGroups()
	{
        static $groups = '';

        if($groups !== '') return $groups;

        $access = (int) $this->form->getValue('access');

        if(!$access) return null;


        $groups     = array();
        $group_list = (array) ProjectforkHelper::getGroupsByAccess($access);

        foreach($group_list AS $group)
        {
            $groups[] = (int) $group->value;
        }

        if(!count($groups)) $groups = null;

		return $groups;
	}
}
