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
     * Method to get the field input markup.
     *
     * @return    string    The html field markup
     */
    protected function getInput()
    {
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


    protected function getGroupOptions()
    {
        $html = array();

        if ($this->inherit) {
            $pks   = $this->getViewingAccessGroups();
            $db    = JFactory::getDBO();
            $query = $db->getQuery(true);

            $query->select('a.id AS value, a.title AS text, a.parent_id')
                  ->from($db->quoteName('#__usergroups') . ' AS a, ' . $db->quoteName('#__usergroups') . ' AS b')
                  ->where('a.lft BETWEEN b.lft AND b.rgt')
                  ->where('b.id IN(' . implode(', ', $pks) . ')')
                  ->group('a.id, a.title, a.lft, a.rgt')
                  ->order('a.lft ASC');

            $db->setQuery($query);
            $groups = $db->loadObjectList();

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
            $html[] = '<option value="' . $group->value . '">' . $group->text . '</option>';
        }

        return $html;
    }


    protected function getGroupsHTML()
    {
        // Include the view class
        require_once JPATH_ADMINISTRATOR . '/components/com_pfusers/views/grouprules/view.raw.php';

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
}
