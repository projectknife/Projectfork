<?php
/**
 * @package      pkg_projectfork
 * @subpackage   lib_projectfork
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2013 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;


jimport('joomla.form.formfield');
JHtml::_('pfhtml.script.jQuerySelect2');

JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_pfusers/models', 'PFusersModel');

/**
 * Form Field class for managing the project access rules.
 *
 */
class JFormFieldGroupAccess extends JFormField
{
    /**
     * The form field type
     *
     * @var    string
     */
    public $type = 'GroupAccess';

    /**
     * The asset id of the current item
     *
     * @var    integer
     */
    protected $asset_id;

    /**
     * The access level of the current item
     *
     * @var    integer
     */
     protected $access;

     /**
     * The component rules to load from
     *
     * @var    string
     */
     protected $component;

     /**
     * The rules section to load
     *
     * @var    string
     */
     protected $section;

     /**
     * Indicates whether to inherit access from the parent item or not
     *
     * @var    integer
     */
     protected $inherit;

    /**
     * The user groups.
     *
     * @deprecated since 4.2
     *
     * @var    array
     */
    protected $groups;

    /**
     * The user groups the current user is allowed to see.
     *
     * @deprecated since 4.2
     *
     * @var    array
     */
    protected $auth_groups;

    /**
     * True if the current user is an admin. Otherwise false
     *
     * @deprecated since 4.2
     *
     * @var    boolean
     */
    protected $is_admin;

    /**
     * @deprecated since 4.2
     *
     * @var    object
     */
    protected $rules;

    /**
     * @deprecated since 4.2
     *
     * @var    integer
     */
    protected $access_level;

    /**
     * @deprecated since 4.2
     *
     * @var    array
     */
    protected $selected;

    /**
     * @deprecated since 4.2
     *
     * @var    array
     */
    protected $actions;


    /**
     * Method to get the field input markup.
     *
     * @return    string    The html field markup
     */
    protected function getInput()
    {
        static $count;

        $v = $this->element['version'] ? (string) $this->element['version'] : '4.0';

        // Version 4.0 and 4.1 code for backwards compat
        if ($v == '4.0') {
            JHtml::_('behavior.tooltip');

            // Initialise some field attributes.
            $section    = $this->element['section']     ? (string) $this->element['section']     : 'component';
            $component  = $this->element['component']   ? (string) $this->element['component']   : 'com_projectfork';
            $asset      = $this->element['asset_field'] ? (string) $this->element['asset_field'] : 'asset_id';
            $inherit    = $this->element['inheritonly'] ? strval($this->element['inheritonly'])  : "true";
            $inherit    = (trim(strtolower($inherit)) == 'true' ? true : false);

            if ($inherit) {
                // Get possible parent field values
                // Note that the order of the array elements matter!
                $parents = array();
                $parents['project']   = (int) $this->form->getValue('project_id');
                $parents['milestone'] = (int) $this->form->getValue('milestone_id');
                $parents['tasklist']  = (int) $this->form->getValue('list_id');
                $parents['task']      = (int) $this->form->getValue('task_id');
                $parents['topic']     = (int) $this->form->getValue('topic_id');

                $parent_el = 'project';
                $parent_id = $parents['project'];

                foreach($parents AS $key => $value)
                {
                    if ($value > 0) {
                        $parent_el = $key;
                        $parent_id = $value;
                        break;
                    }
                }

                if ($parent_id) {
                    JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_projectfork/tables');

                    $table = JTable::getInstance($parent_el, 'PFTable');
                    if (!$table) {
                        $this->access_level  = (int) $this->form->getValue('access');
                    }
                    else {
                        // Load the parent item
                        if (!$table->load($parent_id)) {
                            $this->access_level  = (int) $this->form->getValue('access');
                        }
                        else {
                            $this->access_level = $table->access;
                        }
                    }
                }
            }
            else {
                $this->access_level  = (int) $this->form->getValue('access');
            }

            // Initialize variables
            $this->is_admin      = JFactory::getUser()->authorise('core.admin', $component);
            $this->auth_groups   = JFactory::getUser()->getAuthorisedGroups();
            $this->groups        = $this->getUserGroups($inherit);
            $this->actions       = $this->getActions($component, $section);
            $this->selected      = $this->getAccessRules((int) $this->form->getValue('access'));

            $this->getAccessRules($this->access_level);

            $this->getAssetRules($component, $section, $asset);
        }


        // New code since 4.2
        if ($v == '4.2') {
            JHtml::_('behavior.tooltip');

            // Initialise some field attributes.
            $this->section   = $this->element['section']     ? (string) $this->element['section']     : 'component';
            $this->component = $this->element['component']   ? (string) $this->element['component']   : 'com_projectfork';
            $asset   = $this->element['asset_field'] ? (string) $this->element['asset_field'] : 'asset_id';
            $inherit = $this->element['inheritonly'] ? strval($this->element['inheritonly'])  : "true";

            $this->inherit = (trim(strtolower($inherit)) == 'true' ? true : false);

            // Get the current access level
            if ($this->inherit) {
                // Get possible parent field values
                // Note that the order of the array elements matter!
                $parents = array();

                if ($this->component == 'com_pfdesigns') {
                    $parents['design'] = (int) $this->form->getValue('parent_id');
                    $parents['album']  = (int) $this->form->getValue('album_id');
                }

                $parents['project']   = (int) $this->form->getValue('project_id');
                $parents['milestone'] = (int) $this->form->getValue('milestone_id');
                $parents['tasklist']  = (int) $this->form->getValue('list_id');
                $parents['task']      = (int) $this->form->getValue('task_id');
                $parents['topic']     = (int) $this->form->getValue('topic_id');

                $parent_el = 'project';
                $parent_id = $parents['project'];

                foreach($parents AS $key => $value)
                {
                    if ($value > 0) {
                        $parent_el = $key;
                        $parent_id = $value;
                        break;
                    }
                }

                if ($parent_id) {
                    if ($this->component == 'com_pfdesigns') {
                        JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_pfdesigns/tables');
                    }

                    JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_projectfork/tables');

                    $table = JTable::getInstance($parent_el, 'PFTable');

                    if (!$table) {
                        $this->access = (int) $this->form->getValue('access');
                    }
                    else {
                        // Load the parent item
                        if (!$table->load($parent_id)) {
                            $this->access = (int) $this->form->getValue('access');
                        }
                        else {
                            $this->access = $table->access;
                        }
                    }
                }
            }
            else {
                $this->access = (int) $this->form->getValue('access');
            }

            if (!$this->access) {
                $this->access = (int) JFactory::getConfig()->get('access');
            }


            if ($this->inherit) {
                $this->project_id = (int) $this->form->getValue('project_id');
            }
            else {
                $this->project_id = (int) $this->form->getValue('id');

                if (!$this->project_id) {
                    $this->project_id = (int) JRequest::getVar('id');
                }
            }

            $this->asset_id   = (int) $this->form->getValue($asset);

            $add_opts = array();
            $add_opts['inherit']    = $this->inherit;
            $add_opts['component']  = $this->component;
            $add_opts['section']    = $this->section;
            $add_opts['asset_id']   = $this->asset_id;
            $add_opts['project_id'] = (int) $this->project_id;

            $opt_url = '';

            foreach ($add_opts AS $opt => $val)
            {
                $opt_url .= '&' . $opt . '=' . $val;
            }

            $html   = array();
            $html[] = '<div>';
            $html[] = '    <div class="form-horizontal">';
            $html[] = '        <select id="aclm-add-group" autocomplete="off" class="inputbox input-large">';
            $html[] = '            <option value="">' . JText::_('JOPTION_SELECT_GROUP') . '</option>';
            $html[] = implode("\n", $this->getGroupOptions());
            $html[] = '        </select>';
            $html[] = '        <button type="button" class="btn" id="alcm-add-group" onclick="PFform.alcmAddSelectedGroup(\'' . $opt_url . '\');">' . JText::_('JACTION_ADD_GROUP') . '</button>';
            $html[] = '    </div>';
            $html[] = '    <p style="clear: both;"></p>';
            $html[] = '    <div class="form-horizontal" id="aclm-groups">';
            $html[] = '        ' . $this->getGroupsHTML();
            $html[] = '    </div>';
            $html[] = '</div>';

            return implode("\n", $html);
        }
    }


    protected function getGroupOptions()
    {
        $html = array();
        $user = JFactory::getUser();

        $is_sa          = $user->authorise('core.admin');
        $allowed_groups = $user->getAuthorisedGroups();

        if ($this->inherit) {
            $pks   = $this->getViewingAccessGroups();
            $db    = JFactory::getDBO();
            $query = $db->getQuery(true);

            if (!is_array($pks) || count($pks) == 0) {
                $groups = array();
            }
            else {
                $query->select('a.id AS value, a.title AS text, a.parent_id')
                      ->from($db->quoteName('#__usergroups') . ' AS a, ' . $db->quoteName('#__usergroups') . ' AS b')
                      ->where('a.lft BETWEEN b.lft AND b.rgt')
                      ->where('b.id IN(' . implode(', ', $pks) . ')')
                      ->group('a.id, a.title, a.lft, a.rgt')
                      ->order('a.lft ASC');

                $db->setQuery($query);
                $groups = $db->loadObjectList();
            }

            if (!is_array($groups)) $groups = array();
            $count = count($groups);

            for ($i = 0; $i < $count; $i++)
            {
                $query->clear();
                $query->select('COUNT(distinct a.id)')
                      ->from($db->quoteName('#__usergroups') . ' AS a, ' . $db->quoteName('#__usergroups') . ' AS b')
                      ->where('b.lft > a.lft AND b.rgt < a.rgt')
                      ->where('b.id = ' . (int) $groups[$i]->value);

                $db->setQuery((string) $query);
                $groups[$i]->level = (int) $db->loadResult();

                $groups[$i]->text = str_repeat('- ', $groups[$i]->level) . $groups[$i]->text;
            }
        }
        else {
            $groups = JHtml::_('user.groups', true);
        }

        foreach ($groups AS $group)
        {
            // Skip groups we are not allowed to access when not a super admin
            if (!$is_sa) {
                if (!in_array($group->value, $allowed_groups)) continue;
            }

            $html[] = '<option value="' . $group->value . '">' . $group->text . '</option>';
        }

        return $html;
    }


    protected function getGroupsHTML()
    {
        // Include the view class
        if (JFactory::getApplication()->isSite()) {
            require_once JPATH_SITE . '/components/com_pfusers/views/grouprules/view.raw.php';
        }
        else {
            require_once JPATH_ADMINISTRATOR . '/components/com_pfusers/views/grouprules/view.raw.php';
        }

        $db     = JFactory::getDBO();
        $query  = $db->getQuery(true);
        $access = (int) $this->access;
        $model  = JModelLegacy::getInstance('GroupRules', 'PFusersModel', array('ignore_request' => true));

        $config = array();
        $config['name']      = 'GroupRules';
        $config['base_path'] = JPATH_ADMINISTRATOR . '/components/com_pfusers';

        $view = new PFusersViewGroupRules($config);

        // Get the groups assigned to the item access level
        if (!$this->inherit && !$this->project_id) {
            $cfg = JComponentHelper::getParams('com_pfprojects');

            if ($cfg->get('create_group')) {
                $groups = array('0');
            }
            else {
                $groups = $this->getViewingAccessGroups();
            }
        }
        else {
            $groups = $this->getViewingAccessGroups();
        }

        // Start - Experimental ajax loading of groups
        $json_groups = json_encode($groups);

        $js   = array();
        $task = JRequest::getVar('task');

        if ($task != 'reload') {
            $js[] = "jQuery(document).ready(function()";
            $js[] = "{";
        }
        $js[] = "PFform.alcmAddPredefinedGroups(";
        $js[] = "" . $json_groups . ", ";
        $js[] = "'" . $this->component . "', ";
        $js[] = "'" . $this->section . "', ";
        $js[] = "'" . $this->inherit . "', ";
        $js[] = $this->asset_id . ", ";
        $js[] = $this->project_id;
        $js[] = ");";

        if ($task != 'reload') {
            $js[] = "});";
        }

        if ($task == 'reload') {
            return '<script type="text/javascript">' . implode("\n", $js) . '</script>';
        }
        else {
            JFactory::getDocument()->addScriptDeclaration(implode("\n", $js));
            return '';
        }
        // End - Experimental ajax loading of groups

        $html = array();

        foreach ($groups AS $group)
        {
            $id = (int) $group;

            $model->setState('grouprules.id', $id);
            $model->setState('grouprules.component', $this->component);
            $model->setState('grouprules.section',   $this->section);
            $model->setState('grouprules.inherit',   $this->inherit);
            $model->setState('grouprules.asset_id',  $this->asset_id);
            $model->setState('grouprules.project_id',  $this->project_id);

            $view->setModel($model, true);

            if ($model->getItem() == false) continue;

            ob_start();
            $view->display();
            $output = ob_get_contents();
            ob_end_clean();

            $html[] = $output;
        }

        return implode("\n", $html);
    }


    protected function getViewingAccessGroups()
    {
        static $groups = null;

        if (!is_null($groups)) return $groups;

        $db    = JFactory::getDBO();
        $query = $db->getQuery(true);

        $query->select('rules')
              ->from('#__viewlevels')
              ->where('id = ' . (int) $this->access);

        $db->setQuery((string) $query);
        $groups = json_decode($db->loadResult(), true);

        if (!is_array($groups)) $groups = array();

        return $groups;
    }


    /**
     * Gets the path of a user group upwards to its root
     *
     * @deprecated since 4.2
     *
     * @param     integer    $id      The user group for which to get the path
     *
     * @return    array      $path    All parent groups
     */
    protected function getGroupPath($id)
    {
        $db    = JFactory::getDBO();
        $query = $db->getQuery(true);

        $query->select('p.id')
              ->from('#__usergroups AS n, #__usergroups AS p')
              ->where('n.lft BETWEEN p.lft AND p.rgt')
              ->where('n.id = ' . $id)
              ->order('p.lft');

        $db->setQuery((string) $query);
        $path = (array) $db->loadColumn();

        return $path;
    }


    /**
     * Method to get the field markup.
     *
     * @deprecated since 4.2
     *
     * @param     string    $component    The component of which to show the actions
     * @param     string    $section      The action section to show
     * @param     int       $asset        The asset id

     * @return    array                   The html array
     */
    protected function getHTML($component, $section, $asset = '')
    {
        if (JFactory::getApplication()->isSite() || version_compare(JVERSION, '3.0.0', 'ge')) {
            return $this->getSiteHTML($component, $section, $asset);
        }

        return $this->getAdminHTML($component, $section, $asset);
    }


    /**
     * Method to get the admin field markup.
     *
     * @deprecated since 4.2
     *
     * @param     string    $component    The component of which to show the actions
     * @param     string    $section      The action section to show
     * @param     int       $asset        The asset id

     * @return    array                   The html array
     */
    protected function getAdminHTML($component, $section, $asset = '')
    {
        $html   = array();
        $html[] = '<div id="permissions-sliders">';
        $html[] = '<ul id="rules" class="unstyled">';

        foreach($this->groups AS $i => $item)
        {
            $gid = $item->value;

            if (!$this->is_admin) {
                if (!in_array($gid, $this->auth_groups)) {
                    continue;
                }
            }

            $html[] = '<li class="usergroup clearfix">';
            $html[] = '     <div style="float:left">';
            $html[] = '         <button type="button" class="btn" onclick="jQuery(\'#group-rules-' . $gid . '\').toggle();">Permissions</button>';
            $html[] = '     </div>';
            $html[] = '     <div style="float:left">';
            $html[] = '         ';
            $html[] = '         <label for="">';

            // Add a checkbox
            $onclick  = 'onclick="PFform.accessGroupToggle(this);"';
            $path     = $this->getGroupPath($gid);
            $classes  = array();
            $checked  = '';
            $disabled = '';

            foreach($path AS $p)
            {
                if (in_array($p, $this->selected)) {
                    $checked = ' checked="checked"';
                }

                if ($p == $gid) continue;

                if ($checked) {
                    $disabled = ' disabled="disabled"';
                }

                $classes[] = 'childof-' . $p;
            }

            $classes = implode(' ', $classes);

            $html[] = '         <input type="checkbox" name="' . $this->name . '[]" value="' . $gid . '" class="inputbox ' . $classes . '" ' . $onclick . $checked . $disabled. '/>';

            $html[] = '             ' . str_repeat('<span class="gi">|&mdash;</span>', $item->level) . $item->text;
            $html[] = '         </label>';
            $html[] = '     </div>';
            $html[] = '     <div class="clr"></div>';
            $html[] = '     <div class="mypanel" id="group-rules-' . $gid . '" style="display:none;">';
            $html[] = '         <table class="group-rules table table-condensed table-striped small">';
            $html[] = '             <thead>';
            $html[] = '                 <tr>';
            $html[] = '                     <th class="actions" id="actions-th' . $gid . '">';
            $html[] = '                         <span class="acl-action">'.JText::_('JLIB_RULES_ACTION').'</span>';
            $html[] = '                     </th>';
            $html[] = '                     <th class="settings" id="settings-th' . $gid . '">';
            $html[] = '                         <span class="acl-action">' . JText::_('JLIB_RULES_SELECT_SETTING') . '</span>';
            $html[] = '                     </th>';

            // Do not show rule calculation for public or root group
            if ($item->parent_id > 0) {
                $html[] = '                 <th id="aclactionth' . $gid . '">';
                $html[] = '                     <span class="acl-action">' . JText::_('JLIB_RULES_CALCULATED_SETTING') . '</span>';
                $html[] = '                 </th>';
            }

            $html[] = '                 </tr>';
            $html[] = '             </thead>';
            $html[] = '             <tbody>';

            // Start rendering the list of actions
            $current_group = '';
            foreach ($this->actions as $action)
            {
                if (strpos(JText::_($action->title), '-') !== false) {
                    list ($action_group, $action_name) = explode('-', JText::_($action->title), 2);

                    if ($action_group != $current_group) {
                        $current_group = $action_group;

                        $html[] = '                 <tr>';
                        $html[] = '                    <th colspan="3" class="actions"><span class="acl-action">' . trim($action_group) . '</span></th>';
                        $html[] = '                 </tr>';
                    }
                }
                else {
                    $action_name = JText::_($action->title);
                }

                // Get the actual setting for the action for this group.
                $inherited = JAccess::checkGroup($gid, $action->name, $this->asset_id);
                $rule      = $this->rules->allow($action->name, $item->value);

                $field_id    = $this->id . '_' . $action->name . '_' . $gid;
                $field_name  = $this->name . '[' . $action->name . '][' . $gid . ']';
                $field_desc  = htmlspecialchars($action_name . '::' . JText::_($action->description), ENT_COMPAT, 'UTF-8');
                $field_title = JText::sprintf('JLIB_RULES_SELECT_ALLOW_DENY_GROUP', JText::_($action->title), trim($item->text));

                $opt_text    = JText::_(empty($item->parent_id) && empty($component) ? 'JLIB_RULES_NOT_SET' : 'JLIB_RULES_INHERITED');
                $opt_select  = ($rule === null ? ' selected="selected"' : '');

                $html[] = '                 <tr>';
                $html[] = '                     <td headers="actions-th' . $gid . '">';
                $html[] = '                         <label class="hasTip" for="' . $field_id . '" title="' . $field_desc . '">';
                $html[] = '                             <span class="gi">|&mdash;</span>' . trim($action_name);
                $html[] = '                         </label>';
                $html[] = '                     </td>';
                $html[] = '                     <td headers="settings-th' . $gid . '">';
                $html[] = '                         <select name="' . $field_name. '" id="' . $field_id . '" title="' . $field_title . '">';
                $html[] = '                             <option value=""' . $opt_select . '>' . $opt_text . '</option>';
                $html[] = '                             <option value="1"'.($rule === true ? ' selected="selected"' : '') . '>' . JText::_('JLIB_RULES_ALLOWED') . '</option>';
                $html[] = '                             <option value="0"' . ($rule === false ? ' selected="selected"' : '') . '>' . JText::_('JLIB_RULES_DENIED') . '</option>';
                $html[] = '                         </select>&#160; ';

                // If this asset's rule is allowed, but the inherited rule is deny, we have a conflict.
                if (($rule === true) && ($inherited === false)) $html[] = JText::_('JLIB_RULES_CONFLICT');

                $html[] = '                     </td>';

                // Build the Calculated Settings column.
                // The inherited settings column is not displayed for the root group in global configuration.
                if ($item->parent_id > 0) {
                    $html[] = '                     <td headers="aclactionth' . $item->value . '">';

                    // This is where we show the current effective settings considering currrent group, path and cascade.
                    // Check whether this is a component or global. Change the text slightly.
                    if (JAccess::checkGroup($gid, 'core.admin') !== true) {
                        if ($inherited === null) {
                            $html[] = '<span class="icon-16-unset">' . JText::_('JLIB_RULES_NOT_ALLOWED') . '</span>';
                        }
                        elseif ($inherited === true) {
                            $html[] = '<span class="icon-16-allowed">' . JText::_('JLIB_RULES_ALLOWED') . '</span>';
                        }
                        elseif ($inherited === false) {
                            if ($rule === false) {
                                $html[] = '<span class="icon-16-denied">' . JText::_('JLIB_RULES_NOT_ALLOWED') . '</span>';
                            }
                            else {
                                $html[] = '<span class="icon-16-denied"><span class="icon-16-locked">' . JText::_('JLIB_RULES_NOT_ALLOWED_LOCKED') . '</span></span>';
                            }
                        }
                    }
                    elseif (!empty($component)) {
                        $html[] = '<span class="icon-16-allowed"><span class="icon-16-locked">' . JText::_('JLIB_RULES_ALLOWED_ADMIN') . '</span></span>';
                    }
                    else {
                        // Special handling for  groups that have global admin because they can't be denied.
                        // The admin rights can be changed.
                        if ($action->name === 'core.admin') {
                            $html[] = '<span class="icon-16-allowed">' . JText::_('JLIB_RULES_ALLOWED') . '</span>';
                        }
                        elseif ($inherited === false) {
                            // Other actions cannot be changed.
                            $html[] = '<span class="icon-16-denied"><span class="icon-16-locked">' . JText::_('JLIB_RULES_NOT_ALLOWED_ADMIN_CONFLICT') . '</span></span>';
                        }
                        else {
                            $html[] = '<span class="icon-16-allowed"><span class="icon-16-locked">' . JText::_('JLIB_RULES_ALLOWED_ADMIN') . '</span></span>';
                        }
                    }

                    $html[] = '</td>';
                }

                $html[] = '</tr>';
            }
            // End rendering the list of actions

            $html[] = '             </tbody>';
            $html[] = '         </table>';
            $html[] = '     </div>';
            $html[] = '</li>';
        }

        $html[] = '</ul>';
        $html[] = '</div>';

        return $html;
    }


    /**
     * Method to get the site field markup.
     *
     * @deprecated since 4.2
     *
     * @param     string    $component    The component of which to show the actions
     * @param     string    $section      The action section to show
     * @param     int       $asset        The asset id

     * @return    array                   The html array
     */
    protected function getSiteHTML($component, $section, $asset = '')
    {
        $html   = array();
        $html[] = '<div id="permissions-sliders">';
        $html[] = '<ul id="rules" class="unstyled">';

        foreach($this->groups AS $i => $item)
        {
            $gid = $item->value;

            if (!$this->is_admin) {
                if (!in_array($gid, $this->auth_groups)) {
                    continue;
                }
            }

            $html[] = '<li class="well well-small">';
            $html[] = '    <button type="button" class="btn btn-mini" onclick="jQuery(\'#group-rules-' . $gid . '\').toggle();"><i class="icon-wrench"></i></button>';
            $html[] = '    <label for="">';

            // Add a checkbox
            $onclick  = 'onclick="PFform.accessGroupToggle(this);"';
            $path     = $this->getGroupPath($gid);
            $classes  = array();
            $checked  = '';
            $disabled = '';

            foreach($path AS $p)
            {
                if (in_array($p, $this->selected)) {
                    $checked = ' checked="checked"';
                }

                if ($p == $gid) continue;

                if ($checked) {
                    $disabled = ' disabled="disabled"';
                }

                $classes[] = 'childof-' . $p;
            }

            $classes = implode(' ', $classes);

            $html[] = '     <input type="checkbox" name="' . $this->name . '[]" value="' . $gid . '" class="inputbox ' . $classes . '" ' . $onclick . $checked . $disabled . '/>';

            //$html[] = '             ' . str_repeat('<i class="icon-chevron-right"></i> ', $item->level) . ' <strong>' . $item->text . '</strong>';
            $html[] = '         ' . str_repeat('<strong>|&mdash;</strong>', $item->level) . ' <strong>' . $item->text . '</strong>';
            $html[] = '     </label>';
            $html[] = '     <div class="clearfix"></div>';
            $html[] = '     <div class="mypanel" id="group-rules-' . $gid . '" style="display:none;">';
            $html[] = '         <hr/>';
            $html[] = '         <table class="table table-striped table-condensed">';
            $html[] = '             <thead>';
            $html[] = '                 <tr>';
            $html[] = '                     <th class="actions" id="actions-th' . $gid . '">';
            $html[] = '                         <span class="acl-action">'.JText::_('JLIB_RULES_ACTION').'</span>';
            $html[] = '                     </th>';
            $html[] = '                     <th class="settings" id="settings-th' . $gid . '">';
            $html[] = '                         <span class="acl-action">' . JText::_('JLIB_RULES_SELECT_SETTING') . '</span>';
            $html[] = '                     </th>';

            // Do not show rule calculation for public or root group
            if ($item->parent_id > 0) {
                $html[] = '                 <th id="aclactionth' . $gid . '">';
                $html[] = '                     <span class="acl-action">' . JText::_('JLIB_RULES_CALCULATED_SETTING') . '</span>';
                $html[] = '                 </th>';
            }

            $html[] = '                 </tr>';
            $html[] = '             </thead>';
            $html[] = '             <tbody>';

            // Start rendering the list of actions
            foreach ($this->actions as $action)
            {
                // Get the actual setting for the action for this group.
                $inherited = JAccess::checkGroup($gid, $action->name, $this->asset_id);
                $rule      = $this->rules->allow($action->name, $item->value);

                $field_id    = $this->id . '_' . $action->name . '_' . $gid;
                $field_name  = $this->name . '[' . $action->name . '][' . $gid . ']';
                $field_desc  = htmlspecialchars(JText::_($action->title) . '::' . JText::_($action->description), ENT_COMPAT, 'UTF-8');
                $field_title = JText::sprintf('JLIB_RULES_SELECT_ALLOW_DENY_GROUP', JText::_($action->title), trim($item->text));

                $opt_text    = JText::_(empty($item->parent_id) && empty($component) ? 'JLIB_RULES_NOT_SET' : 'JLIB_RULES_INHERITED');
                $opt_select  = ($rule === null ? ' selected="selected"' : '');

                $html[] = '                 <tr>';
                $html[] = '                     <td headers="actions-th' . $gid . '">';
                $html[] = '                         <label class="hasTip" for="' . $field_id . '" title="' . $field_desc . '">';
                $html[] = '                             ' . JText::_($action->title);
                $html[] = '                         </label>';
                $html[] = '                     </td>';
                $html[] = '                     <td headers="settings-th' . $gid . '">';
                $html[] = '                         <select name="' . $field_name. '" id="' . $field_id . '" title="' . $field_title . '">';
                $html[] = '                             <option value=""' . $opt_select . '>' . $opt_text . '</option>';
                $html[] = '                             <option value="1"'.($rule === true ? ' selected="selected"' : '') . '>' . JText::_('JLIB_RULES_ALLOWED') . '</option>';
                $html[] = '                             <option value="0"' . ($rule === false ? ' selected="selected"' : '') . '>' . JText::_('JLIB_RULES_DENIED') . '</option>';
                $html[] = '                         </select>&#160; ';

                // If this asset's rule is allowed, but the inherited rule is deny, we have a conflict.
                if (($rule === true) && ($inherited === false)) $html[] = JText::_('JLIB_RULES_CONFLICT');

                $html[] = '                     </td>';

                // Build the Calculated Settings column.
                // The inherited settings column is not displayed for the root group in global configuration.
                if ($item->parent_id > 0) {
                    $html[] = '                     <td headers="aclactionth' . $item->value . '">';

                    // This is where we show the current effective settings considering currrent group, path and cascade.
                    // Check whether this is a component or global. Change the text slightly.
                    if (JAccess::checkGroup($gid, 'core.admin') !== true) {
                        if ($inherited === null) {
                            $html[] = '<span class="label label-warning"><i class="icon-white icon-remove"></i> ' . JText::_('JLIB_RULES_NOT_ALLOWED') . '</span>';
                        }
                        elseif ($inherited === true) {
                            $html[] = '<span class="label label-success"><i class="icon-white icon-ok"></i> ' . JText::_('JLIB_RULES_ALLOWED') . '</span>';
                        }
                        elseif ($inherited === false) {
                            if ($rule === false) {
                                $html[] = '<span class="label label-warning"><i class="icon-white icon-remove"></i> ' . JText::_('JLIB_RULES_NOT_ALLOWED') . '</span>';
                            }
                            else {
                                $html[] = '<span class="label label-important"><i class="icon-white icon-lock"></i> ' . JText::_('JLIB_RULES_NOT_ALLOWED_LOCKED') . '</span>';
                            }
                        }
                    }
                    elseif (!empty($component)) {
                        $html[] = '<span class="label label-success"><i class="icon-white icon-ok"></i> ' . JText::_('JLIB_RULES_ALLOWED_ADMIN') . '</span>';
                    }
                    else {
                        // Special handling for  groups that have global admin because they can't be denied.
                        // The admin rights can be changed.
                        if ($action->name === 'core.admin') {
                            $html[] = '<span class="label label-success"><i class="icon-white icon-ok"></i> ' . JText::_('JLIB_RULES_ALLOWED') . '</span>';
                        }
                        elseif ($inherited === false) {
                            // Other actions cannot be changed.
                            $html[] = '<span class="label label-important"><i class="icon-white icon-remove"></i> <i class="icon-white icon-lock"></i> ' . JText::_('JLIB_RULES_NOT_ALLOWED_ADMIN_CONFLICT') . '</span>';
                        }
                        else {
                            $html[] = '<span class="label label-success"><i class="icon-white icon-ok"></i> <i class="icon-white icon-lock"></i> ' . JText::_('JLIB_RULES_ALLOWED_ADMIN') . '</span>';
                        }
                    }

                    $html[] = '</td>';
                }

                $html[] = '</tr>';
            }
            // End rendering the list of actions

            $html[] = '             </tbody>';
            $html[] = '         </table>';
            $html[] = '     </div>';
            $html[] = '</li>';
        }

        $html[] = '</ul>';
        $html[] = '</div>';

        return $html;
    }


    /**
     * Returns the available actions
     *
     * @deprecated since 4.2
     *
     * @param     string    $component    The component name
     * @param     string    $section      The access section of the component
     *
     * @return    array     $actions      The available actions
     */
    protected function getActions($component, $section)
    {
        // Get the actions for the asset.
        $actions = JAccess::getActions($component, $section);

        // Iterate over the children and add to the actions.
        foreach ($this->element->children() as $el)
        {
            if ($el->getName() == 'action') {
                $actions[] = (object) array(
                    'name'        => (string) $el['name'],
                    'title'       => (string) $el['title'],
                    'description' => (string) $el['description']
                );
            }
        }

        return $actions;
    }


    /**
     * Method the get the rules of an access level
     *
     * @deprecated since 4.2
     *
     * @param     integer    $id       The access level id
     *
     * @return    array      $rules    The rules
     */
    protected function getAccessRules($id)
    {
        $db    = JFactory::getDBO();
        $query = $db->getQuery(true);

        if (!$id) {
            return array();
        }

        $query->select('rules')
              ->from('#__viewlevels')
              ->where('id = ' . $id);

        $db->setQuery((string) $query);
        $rules = (array) json_decode($db->loadResult(), true);

        return $rules;
    }


    /**
     * Method to get the asset rules and id.
     *
     * @deprecated since 4.2
     *
     * @param     string    $component    The component of which to show the actions
     * @param     string    $section      The action section to show
     * @param     int       $asset        The asset id

     * @return    void
     */
    protected function getAssetRules($component, $section, $asset = '')
    {
        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);

        // Get the explicit rules for this asset.
        if ($section == 'component') {
            // Need to find the asset id by the name of the component.
            $query->select($db->quoteName('id'))
                  ->from($db->quoteName('#__assets'))
                  ->where($db->quoteName('name') . ' = ' . $db->quote($component));

            $db->setQuery($query);
            $asset_id = (int) $db->loadResult();

            if ($error = $db->getErrorMsg()) JError::raiseNotice(500, $error);
        }
        else {
            // Find the asset id of the item.
            $asset_id = (int) $this->form->getValue($asset);

            if (!$asset_id) {
                // This is a new item, get the asset id of the component
                $query->select($db->quoteName('id'))
                      ->from($db->quoteName('#__assets'))
                      ->where($db->quoteName('name') . ' = ' . $db->quote($component));

                $db->setQuery($query);
                $result = $db->loadResult();

                if ($result) {
                    $asset_id = (int) $result;
                }
            }
        }

        // Get the rules for just this asset (non-recursive).
        $this->rules    = JAccess::getAssetRules($asset_id);
        $this->asset_id = $asset_id;
    }


    /**
     * Get a list of the user groups.
     *
     * @deprecated since 4.2
     *
     * @return    array    $groups
     */
    protected function getUserGroups($inherit = true)
    {
        $user  = JFactory::getUser();
        $db    = JFactory::getDBO();
        $query = $db->getQuery(true);

        $rules  = array();
        $groups = array();
        $access = (int) $this->access_level;

        if (!$access) {
            return array();
        }

        // Only load the groups based on the selected access level
        if ($inherit) {
            $query->select('rules')
                  ->from('#__viewlevels')
                  ->where('id = ' . $access);

            $db->setQuery((string) $query);
            $rules = (array) json_decode($db->loadResult(), true);
        }
        else {
            $query->select('id')
                  ->from('#__usergroups');

            $db->setQuery((string) $query);
            $rules = (array) $db->loadColumn();
        }

        // Filter out groups we are not allowed to access
        if (!$this->is_admin) {
            $my_groups = $user->getAuthorisedGroups();

            foreach($rules AS $i => $rule)
            {
                $group = (int) $rule;
                if (!in_array($group, $my_groups)) {
                    unset($rules[$i]);
                }
            }
        }

        // If the user is not an admin and if there are no groups for filtering,
        // return empty array
        if (count($rules) == 0 && !$this->is_admin) {
            $rules = $user->getAuthorisedGroups();

            if (count($rules) == 0) return array();
        }

        // Return empty array if the selected access level has no groups
        if (count($rules) == 0 && $access > 1) {
            return array();
        }

        $filter_groups = implode(', ', $rules);

        // Build the query
        $query->clear();
        $query->select('a.id AS value, a.title AS text, a.parent_id')
              ->from($db->quoteName('#__usergroups') . ' AS a, ' . $db->quoteName('#__usergroups') . ' AS b')
              ->where('a.lft BETWEEN b.lft AND b.rgt');

        if ($filter_groups) {
            $query->where('b.id IN(' . $filter_groups . ')');
        }

        $query->group('a.id')
              ->order('a.lft ASC');

        $db->setQuery((string) $query);
        $groups = (array) $db->loadObjectList();
        $count  = count($groups);

        // Find the level depth of each group
        $query->clear();

        for($i = 0; $i < $count; $i++)
        {
            $query->clear();
            $query->select('COUNT(distinct a.id)')
                  ->from($db->quoteName('#__usergroups') . ' AS a, ' . $db->quoteName('#__usergroups') . ' AS b')
                  ->where('b.lft > a.lft AND b.rgt < a.rgt')
                  ->where('b.id = ' . (int) $groups[$i]->value);

            $db->setQuery((string) $query);
            $groups[$i]->level = (int) $db->loadResult();
        }

        return $groups;
    }
}
