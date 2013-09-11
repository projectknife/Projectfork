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

        // Initialize JavaScript field attribute
        $onchange = (string) $this->element['onchange'];

        // Allow multiple?
        $multiple = (isset($this->element['multiple']) ? (bool) $this->element['multiple'] : false);

        if ($multiple) {
            if (!is_array($this->value)) {
                $this->value = (empty($this->value) ? array() : array($this->value));
            }
        }

        if (is_array($this->value)) {
            if (count($this->value)) {
                JArrayHelper::toInteger($this->value);

                $db    = JFactory::getDbo();
                $query = $db->getQuery(true);

                $query->select('id, name, username')
                      ->from('#__users')
                      ->where('id IN (' . implode(', ', $this->value) . ')')
                      ->order('name ASC');

                $db->setQuery($query);
                $value = $db->loadObjectList();

                if (empty($value)) {
                    $value = array();
                }
            }
            else {
                $value = array();
            }
        }
        else {
            $value = null;

            if ($this->value) {
                $user = JFactory::getUser($this->value);

                $value->id       = $user->id;
                $value->name     = $user->name;
                $value->username = $user->username;
            }
        }

        // Add the script to the document head.
        $script = $this->getJavascript($onchange, $multiple);
        JFactory::getDocument()->addScriptDeclaration(implode("\n", $script));

        $html = $this->getHTML($value, $multiple);

        return implode("\n", $html);
    }


    /**
     * Method to generate the input markup.
     *
     * @param     string    $title    The title of the current value
     *
     * @return    string              The html field markup
     */
    protected function getHTML($value, $multiple = false)
    {
        if (!$multiple) {
            if (JFactory::getApplication()->isAdmin()) {
                return $this->getAdminHTML($value, $multiple);
            }

            return $this->getSiteHTML($value, $multiple);
        }

        if (version_compare(JVERSION, '3', 'lt') && JFactory::getApplication()->isAdmin()) {
            return $this->getAdminHTML($value, $multiple);
        }

        return $this->getSelect2HTML($value, $multiple);
    }


    protected function getSelect2HTML($value, $multiple = false)
    {
        $access = (int) $this->form->getValue('access');
        $pks    = JArrayHelper::getColumn($value, 'id');
        $html   = array();

        if (!$access) {
            $access = (int) JFactory::getConfig()->get('access');
        }

        $url = "index.php?option=com_pfusers&view=userref&filter_access=" . $access . "&tmpl=component&layout=select2&format=json";

        $html[] = '<input type="hidden" id="' . $this->id . '" name="' . $this->name . '" value="' . implode(',', $pks) . '" class="inputbox input-large"/>';
        $html[] = '<script type="text/javascript">';
        $html[] = 'jQuery("#' . $this->id . '").select2({';
        $html[] = '    allowClear: true,';
        $html[] = '    minimumInputLength: 0,';
        $html[] = '    multiple: true,';
        $html[] = '    ajax:';
        $html[] = '    {';
        $html[] = '        url: "' . $url . '",';
        $html[] = '        dataType: "json",';
        $html[] = '        quietMillis: 200,';
        $html[] = '        data: function (term, page)';
        $html[] = '        {';
        $html[] = '            return {filter_search: term, limit: 10, limitstart: ((page - 1) * 10)};';
        $html[] = '        },';
        $html[] = '        results: function (data, page)';
        $html[] = '        {';
        $html[] = '            var more = (page * 10) < data.total;';
        $html[] = '            return {results: data.items, more: more};';
        $html[] = '        }';
        $html[] = '    }';

        if (count($pks)) {
            $html[] = '    ,initSelection: function(element, callback)';
            $html[] = '    {';
            $html[] = '        callback(' . $this->getJsonUsers($pks) . ');';
            $html[] = '    }';
        }

        $html[] = '});';
        $html[] = '</script>';

        return $html;
    }


    protected function getJsonUsers($pks)
    {
        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->select('id, name, username')
              ->from('#__users')
              ->where("id IN(" . implode(',', $pks) . ")")
              ->order('username ASC');

        $db->setQuery($query);
        $items = $db->loadObjectList();

        if (empty($items)) $items = array();

        $users = array();

        foreach ($items AS $item)
        {
            $row = new stdClass();

            $row->id   = $item->id;
            $row->text = htmlspecialchars('[' . $item->username . '] ' . $item->name, ENT_COMPAT, 'UTF-8');

            $users[] = $row;
        }

        return json_encode($users);
    }


    /**
     * Method to generate the backend input markup.
     *
     * @param     string    $uname    The name of the currently selected user
     *
     * @return    array     $html     The html field markup
     */
    protected function getAdminHTML($value, $multiple = false)
    {
        $html     = array();
        $groups   = $this->getGroups();
        $excluded = $this->getExcluded();

        $link = 'index.php?option=com_users&amp;view=users&amp;layout=modal&amp;tmpl=component&amp;field=' . $this->id
              . (isset($groups)   ? ('&amp;groups=' . base64_encode(json_encode($groups)))     : '')
              . (isset($excluded) ? ('&amp;excluded=' . base64_encode(json_encode($excluded))) : '');

        // Initialize some field attributes.
        $attr = $this->element['class'] ? ' class="' . (string) $this->element['class'] . '"' : '';
        $attr .= $this->element['size'] ? ' size="' . (int) $this->element['size'] . '"'      : '';

        if ($multiple) {
            $html[] = '<ul id="' . $this->id . '_list" class="unstyled">';

            foreach ($value AS $i => $user)
            {
                $html[] = '<li>';

                // Create a dummy text field with the user name.
                $html[] = '<div class="fltlft">';
                $html[] = '<input type="text" id="' . $this->id . '_' . $i . '_name"' . ' value="' . htmlspecialchars($user->name, ENT_COMPAT, 'UTF-8') . '"'
                        . ' disabled="disabled"' . $attr . ' />';
                $html[] = '</div>';

                // Create the user select button.
                if ($this->element['readonly'] != 'true') {
                    $html[] = '<div class="button2-left">';
                    $html[] = '  <div class="blank">';
                    $html[] = '        <a class="modal_' . $this->id . '" title="' . JText::_('JLIB_FORM_CHANGE_USER') . '"' . ' href="' . $link . '"'
                            . ' rel="{handler: \'iframe\', size: {x: 800, y: 500}}" onclick="jQuery(\'#' . $this->id . '_i\').val(' . $i . ');">';
                    $html[] = '            ' . JText::_('JLIB_FORM_CHANGE_USER') . '</a>';
                    $html[] = '    </div>';
                    $html[] = '</div>';
                }

                // Create the real field, hidden, that stored the user id.
                $html[] = '<input type="hidden" id="' . $this->id . '_' . $i . '_id" name="' . $this->name . '" value="' . (int) $user->id . '" />';

                if ($this->element['readonly'] != 'true') {
                    $html[] = '<div class="button2-left">';
                    $html[] = '  <div class="blank">';
                    $html[] = '        <a onclick="jRemoveUserField_' . $this->id . '(this)">';
                    $html[] = '        ' . JText::_('COM_PROJECTFORK_FIELD_CLEAR_LABEL') . '</a>';
                    $html[] = '  </div>';
                    $html[] = '</div>';
                }

                $html[] = '<div class="clr"></div></li>';
            }

            $html[] = '</ul>';

            // Create the "add" button.
            if ($this->element['readonly'] != 'true') {
                $html[] = '<div class="button2-left">';
                $html[] = '  <div class="blank">';
                $html[] = '<a class="btn" href="javascript:void(0);" onclick="jAddUserField_' . $this->id . '();">';
                $html[] = JText::_('JACTION_ADD_USER');
                $html[] = '</a>';
                $html[] = '  </div>';
                $html[] = '</div>';
            }

            // Create a hidden iterator field
            if ($this->element['readonly'] != 'true') {
                $html[] = '<input type="hidden" id="' . $this->id . '_i" value="0"/>';
                $html[] = '<input type="hidden" name="' . $this->name . '" value=""/>';
            }
        }
        else {
            // Create a dummy text field with the user name.
            $html[] = '<div class="fltlft">';
            $html[] = '    <input type="text" id="' . $this->id . '_name"' . ' value="' . htmlspecialchars($uname, ENT_COMPAT, 'UTF-8') . '"'
                    . ' disabled="disabled"' . $attr . ' />';
            $html[] = '</div>';

            // Create the user select button.
            if ($this->element['readonly'] != 'true') {
                $html[] = '<div class="button2-left">';
                $html[] = '  <div class="blank">';
                $html[] = '        <a class="modal_' . $this->id . '" title="' . JText::_('JLIB_FORM_CHANGE_USER') . '"' . ' href="' . $link . '"'
                        . ' rel="{handler: \'iframe\', size: {x: 800, y: 500}}">';
                $html[] = '            ' . JText::_('JLIB_FORM_CHANGE_USER') . '</a>';
                $html[] = '    </div>';
                $html[] = '</div>';
            }

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
        }

        return $html;
    }


    /**
     * Method to generate the frontend input markup.
     *
     * @param     string    $uname    The name of the currently selected user
     *
     * @return    array     $html     The html field markup
     */
    protected function getSiteHTML($value, $multiple = false)
    {
        $html     = array();
        $groups   = $this->getGroups();
        $excluded = $this->getExcluded();

        // Initialize some field attributes.
        $attr = $this->element['class'] ? ' class="' . (string) $this->element['class'] . '"' : '';
        $attr .= $this->element['size'] ? ' size="' . (int) $this->element['size'] . '"'      : '';

        $link = PFusersHelperRoute::getUsersRoute() . '&amp;layout=modal&amp;tmpl=component&amp;field=' . $this->id
              . (isset($groups) ? ('&amp;groups=' . base64_encode(json_encode($groups)))       : '')
              . (isset($excluded) ? ('&amp;excluded=' . base64_encode(json_encode($excluded))) : '');

        if ($multiple) {
            $html[] = '<ul id="' . $this->id . '_list" class="unstyled">';

            foreach ($value AS $i => $user)
            {
                $html[] = '<li>';

                // Create a dummy text field with the user name.
                $html[] = '<input type="text" id="' . $this->id . '_' . $i . '_name" value="' . htmlspecialchars($user->name, ENT_COMPAT, 'UTF-8') . '"'
                        . ' disabled="disabled"' . $attr . ' />';

                // Create the user select button.
                if ($this->element['readonly'] != 'true') {
                    $html[] = '<a class="modal_' . $this->id . ' btn" title="' . JText::_('JLIB_FORM_CHANGE_USER') . '"' . ' href="' . $link . '"'
                            . ' rel="{handler: \'iframe\', size: {x: 800, y: 500}}" onclick="jQuery(\'#' . $this->id . '_i\').val(' . $i . ');">'
                            . JText::_('JLIB_FORM_CHANGE_USER')
                            . '</a>';
                }

                // Create the real field, hidden, that stored the user id.
                $html[] = '<input type="hidden" id="' . $this->id . '_' . $i . '_id" name="' . $this->name . '" value="' . (int) $user->id . '" />';

                if ($this->element['readonly'] != 'true') {
                    $html[] = '<a onclick="jRemoveUserField_' . $this->id . '(this)" class="btn">'
                            . JText::_('COM_PROJECTFORK_FIELD_CLEAR_LABEL')
                            . '</a>';
                }

                $html[] = '</li>';
            }

            $html[] = '</ul>';

            // Create the "add" button.
            if ($this->element['readonly'] != 'true') {
                $html[] = '<a class="btn" href="javascript:void(0);" onclick="jAddUserField_' . $this->id . '();">';
                $html[] = JText::_('JACTION_ADD_USER');
                $html[] = '</a>';
            }

            // Create a hidden iterator field
            if ($this->element['readonly'] != 'true') {
                $html[] = '<input type="hidden" id="' . $this->id . '_i" value="0"/>';
                $html[] = '<input type="hidden" name="' . $this->name . '" value=""/>';
            }
        }
        else {
            $link = PFusersHelperRoute::getUsersRoute() . '&amp;layout=modal&amp;tmpl=component&amp;field=' . $this->id
                  . (isset($groups) ? ('&amp;groups=' . base64_encode(json_encode($groups)))       : '')
                  . (isset($excluded) ? ('&amp;excluded=' . base64_encode(json_encode($excluded))) : '');

            // Create a dummy text field with the user name.
            $html[] = '<input type="text" id="' . $this->id . '_name" value="' . htmlspecialchars($value->name, ENT_COMPAT, 'UTF-8') . '"'
                    . ' disabled="disabled"' . $attr . ' />';

            // Create the user select button.
            if ($this->element['readonly'] != 'true') {
                $html[] = '<a class="modal_' . $this->id . ' btn" title="' . JText::_('JLIB_FORM_CHANGE_USER') . '"' . ' href="' . $link . '"'
                        . ' rel="{handler: \'iframe\', size: {x: 800, y: 500}}">'
                        . JText::_('JLIB_FORM_CHANGE_USER')
                        . '</a>';
            }

            // Create the real field, hidden, that stored the user id.
            $html[] = '<input type="hidden" id="' . $this->id . '_id" name="' . $this->name . '" value="' . (int) $value->id . '" />';

            $js_clear = 'document.id(\'' . $this->id .'_name\').value = \'\';'
                      . 'document.id(\'' . $this->id .'_id\').value = \'\';';

            if ($this->element['readonly'] != 'true') {
                $html[] = '<a onclick="' . $js_clear . '" class="btn">'
                        . JText::_('COM_PROJECTFORK_FIELD_CLEAR_LABEL')
                        . '</a>';
            }
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
     * @param     boolean   $multiple    True if multiple users are allowed to be selected
     *
     * @return    array     $script      The generated javascript
     */
    protected function getJavascript($onchange = '', $multiple = false)
    {
        $attr = $this->element['class'] ? ' class=\"' . (string) $this->element['class'] . '\"' : '';
        $attr .= $this->element['size'] ? ' size=\"' . (int) $this->element['size'] . '\"'      : '';

        $groups   = $this->getGroups();
        $excluded = $this->getExcluded();

        if (JFactory::getApplication()->isSite()) {
            $link = PFusersHelperRoute::getUsersRoute() . '&amp;layout=modal&amp;tmpl=component&amp;field=' . $this->id
                  . (isset($groups) ? ('&amp;groups=' . base64_encode(json_encode($groups)))       : '')
                  . (isset($excluded) ? ('&amp;excluded=' . base64_encode(json_encode($excluded))) : '');
        }
        else {
            $link = 'index.php?option=com_users&amp;view=users&amp;layout=modal&amp;tmpl=component&amp;field=' . $this->id
                  . (isset($groups)   ? ('&amp;groups=' . base64_encode(json_encode($groups)))     : '')
                  . (isset($excluded) ? ('&amp;excluded=' . base64_encode(json_encode($excluded))) : '');
        }


        $script = array();

        $script[] = 'function jSelectUser_' . $this->id . '(id, title)';
        $script[] = '{';
        $script[] = '    var ifld = jQuery("#' . $this->id . '_i");';
        $script[] = '    if (ifld.length) {var i = ifld.val();}';
        $script[] = '    if (typeof i != "undefined") {';
        $script[] = '        var old_id = jQuery("#' . $this->id . '_id_" + i).val();';
        $script[] = '        if (old_id != id) {';
        $script[] = '            jQuery("#' . $this->id . '_" + i + "_id").val(id);';
        $script[] = '            jQuery("#' . $this->id . '_" + i + "_name").val(title);';
        $script[] = '            ' . $onchange;
        $script[] = '        }';
        $script[] = '    }';
        $script[] = '    else {';
        $script[] = '        var old_id = document.getElementById("' . $this->id . '_id").value;';
        $script[] = '        if (old_id != id) {';
        $script[] = '            document.getElementById("' . $this->id . '_id").value = id;';
        $script[] = '            document.getElementById("' . $this->id . '_name").value = title;';
        $script[] = '            ' . $onchange;
        $script[] = '        }';
        $script[] = '    }';
        $script[] = '    SqueezeBox.close();';
        $script[] = '}';

        if ($multiple) {
            if (JFactory::getApplication()->isSite()) {
                $script[] = 'function jAddUserField_' . $this->id . '()';
                $script[] = '{';
                $script[] = '    var l = jQuery("#' . $this->id . '_list");';
                $script[] = '    var i = l.children("li").length + 1;';

                $script[] = '    var txt = "<input type=\"text\" id=\"' . $this->id . '_" + i + "_name\" value=\"\" disabled=\"disabled\"' . $attr . ' />";';

                $script[] = '    var btn = "<a class=\"modal_' . $this->id . '_" + i + " btn\" title=\"' . JText::_('JLIB_FORM_CHANGE_USER') . '\"' . ' href=\"' . $link . '\""';
                $script[] = '            + " rel=\"{handler: \'iframe\', size: {x: 800, y: 500}}\" onclick=\"jQuery(\'#' . $this->id . '_i\').val(" + i + ");\">"';
                $script[] = '            + "' . JText::_('JLIB_FORM_CHANGE_USER') . '"';
                $script[] = '            + "</a>"';

                $script[] = '    var clr = "<a onclick=\"jRemoveUserField_' . $this->id . '(this)\" class=\"btn\">"';
                $script[] = '            + "' . JText::_('COM_PROJECTFORK_FIELD_CLEAR_LABEL') . '"';
                $script[] = '            + "</a>"';

                $script[] = '    var hdn = "<input type=\"hidden\" id=\"' . $this->id . '_" + i + "_id\" value=\"\" name=\"' . $this->name . '\" />";';

                $script[] = '    l.append("<li>" + txt + btn + clr + hdn + "</li>");';
                $script[] = '    SqueezeBox.initialize({});SqueezeBox.assign($$("a.modal_' . $this->id . '_" + i), {parse: "rel"});';
                $script[] = '}';
                $script[] = 'function jRemoveUserField_' . $this->id . '(el)';
                $script[] = '{';
                $script[] = '    jQuery(el).closest("li").remove();';
                $script[] = '}';
            }
            else {
                $script[] = 'function jAddUserField_' . $this->id . '()';
                $script[] = '{';
                $script[] = '    var l = jQuery("#' . $this->id . '_list");';
                $script[] = '    var i = l.children("li").length + 1;';

                $script[] = '    var txt = "<input type=\"text\" id=\"' . $this->id . '_" + i + "_name\" value=\"\" disabled=\"disabled\"' . $attr . ' />";';

                $script[] = '    var btn = "<div class=\"button2-left\"><div class=\"blank\">"';
                $script[] = '            + "<a class=\"modal_' . $this->id . '_" + i + " btn\" title=\"' . JText::_('JLIB_FORM_CHANGE_USER') . '\"' . ' href=\"' . $link . '\""';
                $script[] = '            + " rel=\"{handler: \'iframe\', size: {x: 800, y: 500}}\" onclick=\"jQuery(\'#' . $this->id . '_i\').val(" + i + ");\">"';
                $script[] = '            + "' . JText::_('JLIB_FORM_CHANGE_USER') . '"';
                $script[] = '            + "</a>"';
                $script[] = '            + "</div></div>"';

                $script[] = '    var clr = "<div class=\"button2-left\"><div class=\"blank\"><a onclick=\"jRemoveUserField_' . $this->id . '(this)\" class=\"btn\">"';
                $script[] = '            + "' . JText::_('COM_PROJECTFORK_FIELD_CLEAR_LABEL') . '"';
                $script[] = '            + "</a></div></div><div class=\"clr\"></div>"';

                $script[] = '    var hdn = "<input type=\"hidden\" id=\"' . $this->id . '_" + i + "_id\" value=\"\" name=\"' . $this->name . '\" />";';

                $script[] = '    l.append("<li>" + txt + btn + clr + hdn + "</li>");';
                $script[] = '    SqueezeBox.initialize({});SqueezeBox.assign($$("a.modal_' . $this->id . '_" + i), {parse: "rel"});';
                $script[] = '}';
                $script[] = 'function jRemoveUserField_' . $this->id . '(el)';
                $script[] = '{';
                $script[] = '    jQuery(el).closest("li").remove();';
                $script[] = '}';
            }
        }

        return $script;
    }
}
