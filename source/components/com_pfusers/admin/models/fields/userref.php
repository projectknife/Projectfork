<?php
/**
 * @package      Projectfork
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

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
     * @return    string    The field input markup.
     */
    protected function getInput()
    {
        // Load the modal behavior script.
        JHtml::_('behavior.modal', 'a.modal_' . $this->id);

        // Validate the currently selected user
        if ($this->value) {
            $groups = $this->getGroups();
            $user   = JFactory::getUser($this->value);

            $user_groups = $user->getAuthorisedGroups();
            $authorize   = false;

            if (is_array($groups)) {
                foreach($user_groups AS $group)
                {
                    $authorize = in_array($group, $groups);
                }
            }

            if (!$authorize) $this->value = '';
        }

        // Initialize JavaScript field attribute
        $onchange = (string) $this->element['onchange'];

        // Add the script to the document head.
        $script = $this->getJavascript($onchange);
        JFactory::getDocument()->addScriptDeclaration(implode("\n", $script));

        // Load the current username if available.
        $table = JTable::getInstance('user');
        if ($this->value) {
            $table->load($this->value);
        }
        else {
            $table->name = JText::_('JLIB_FORM_SELECT_USER');
        }

        $html = $this->getHTML($table->name);
        return implode("\n", $html);
    }


    /**
     * Method to generate the input markup.
     *
     * @param     string    $title    The title of the current value
     *
     * @return    string              The html field markup
     */
    protected function getHTML($uname)
    {
        if (JFactory::getApplication()->isSite()) {
            return $this->getSiteHTML($uname);
        }

        return $this->getAdminHTML($uname);
    }


    /**
     * Method to generate the backend input markup.
     *
     * @param     string    $uname    The name of the currently selected user
     *
     * @return    array     $html     The html field markup
     */
    protected function getAdminHTML($uname)
    {
        $html     = array();
        $groups   = $this->getGroups();
        $excluded = $this->getExcluded();

        $link = 'index.php?option=com_users&amp;view=users&amp;layout=modal&amp;tmpl=component&amp;field=' . $this->id
              . (isset($groups) ?   ('&amp;groups=' . base64_encode(json_encode($groups)))     : '')
              . (isset($excluded) ? ('&amp;excluded=' . base64_encode(json_encode($excluded))) : '');

        // Initialize some field attributes.
        $attr = $this->element['class'] ? ' class="' . (string) $this->element['class'] . '"' : '';
        $attr .= $this->element['size'] ? ' size="' . (int) $this->element['size'] . '"'      : '';

        // Create a dummy text field with the user name.
        $html[] = '<div class="fltlft">';
        $html[] = '    <input type="text" id="' . $this->id . '_name"' . ' value="' . htmlspecialchars($uname, ENT_COMPAT, 'UTF-8') . '"'
                . ' disabled="disabled"' . $attr . ' />';
        $html[] = '</div>';

        // Create the user select button.
        $html[] = '<div class="button2-left">';
        $html[] = '  <div class="blank">';

        if ($this->element['readonly'] != 'true') {
            $html[] = '        <a class="modal_' . $this->id . '" title="' . JText::_('JLIB_FORM_CHANGE_USER') . '"' . ' href="' . $link . '"'
                    . ' rel="{handler: \'iframe\', size: {x: 800, y: 500}}">';
            $html[] = '            ' . JText::_('JLIB_FORM_CHANGE_USER') . '</a>';
        }

        $html[] = '    </div>';
        $html[] = '</div>';

        // Create the real field, hidden, that stores the user id.
        $html[] = '<input type="hidden" id="' . $this->id . '_id" name="' . $this->name . '" value="' . (int) $this->value . '" />';


        $js_clear = 'document.id(\'' . $this->id.'_name\').value = \'\';'
                  . 'document.id(\'' . $this->id.'_id\').value = \'0\';';

        $html[] = '<div class="button2-left">';
        $html[] = '  <div class="blank">';

        if ($this->element['readonly'] != 'true') {
            $html[] = '        <a onclick="' . $js_clear . '">';
            $html[] = '        ' . JText::_('COM_PROJECTFORK_FIELD_CLEAR_LABEL') . '</a>';
        }

        $html[] = '  </div>';
        $html[] = '</div>';

        return $html;
    }


    /**
     * Method to generate the frontend input markup.
     *
     * @param     string    $uname    The name of the currently selected user
     *
     * @return    array     $html     The html field markup
     */
    protected function getSiteHTML($uname)
    {
        $html     = array();
        $groups   = $this->getGroups();
        $excluded = $this->getExcluded();

        $link = PFusersHelperRoute::getUsersRoute() . '&amp;layout=modal&amp;tmpl=component&amp;field=' . $this->id
              . (isset($groups) ? ('&amp;groups=' . base64_encode(json_encode($groups)))       : '')
              . (isset($excluded) ? ('&amp;excluded=' . base64_encode(json_encode($excluded))) : '');

        // Initialize some field attributes.
        $attr = $this->element['class'] ? ' class="' . (string) $this->element['class'] . '"' : '';
        $attr .= $this->element['size'] ? ' size="' . (int) $this->element['size'] . '"'      : '';

        // Create a dummy text field with the user name.
        $html[] = '    <input type="text" id="' . $this->id . '_name"' . ' value="' . htmlspecialchars($uname, ENT_COMPAT, 'UTF-8') . '"'
                . ' disabled="disabled"' . $attr . ' />';

        // Create the user select button.
        if ($this->element['readonly'] != 'true') {
            $html[] = '        <a class="modal_' . $this->id . ' btn" title="' . JText::_('JLIB_FORM_CHANGE_USER') . '"' . ' href="' . $link . '"'
                    . ' rel="{handler: \'iframe\', size: {x: 800, y: 500}}">';
            $html[] = '            ' . JText::_('JLIB_FORM_CHANGE_USER') . '</a>';
        }

        // Create the real field, hidden, that stored the user id.
        $html[] = '<input type="hidden" id="' . $this->id . '_id" name="' . $this->name . '" value="' . (int) $this->value . '" />';


        $js_clear = 'document.id(\'' . $this->id.'_name\').value = \'\';'
                  . 'document.id(\'' . $this->id.'_id\').value = \'0\';';

        if ($this->element['readonly'] != 'true') {
            $html[] = '        <a onclick="' . $js_clear.'" class="btn">';
            $html[] = '        ' . JText::_('COM_PROJECTFORK_FIELD_CLEAR_LABEL') . '</a>';
        }

        return $html;
    }


    /**
     * Method to get the filtering groups (null means no filtering)
     *
     * @return    mixed    $groups    Array of filtering groups
     */
    protected function getGroups()
    {
        static $groups = '';

        if ($groups !== '') return $groups;

        $access = (int) $this->form->getValue('access');

        if (!$access) return '';

        $groups     = array();
        $group_list = (array) PFAccessHelper::getGroupsByAccessLevel($access);

        foreach($group_list AS $group)
        {
            $groups[] = (int) $group;
        }

        if (!count($groups)) $groups = '';

        return $groups;
    }


    /**
     * Generates the javascript needed for this field
     *
     * @param     string    $onchange    Onchange event javascript
     *
     * @return    array     $script      The generated javascript
     */
    protected function getJavascript($onchange = '')
    {
        $script = array();

        $script[] = 'function jSelectUser_' . $this->id . '(id, title)';
        $script[] = '{';
        $script[] = '    var old_id = document.getElementById("' . $this->id . '_id").value;';
        $script[] = '    if (old_id != id) {';
        $script[] = '        document.getElementById("' . $this->id . '_id").value = id;';
        $script[] = '        document.getElementById("' . $this->id . '_name").value = title;';
        $script[] = '        ' . $onchange;
        $script[] = '    }';
        $script[] = '    SqueezeBox.close();';
        $script[] = '}';

        return $script;
    }
}
