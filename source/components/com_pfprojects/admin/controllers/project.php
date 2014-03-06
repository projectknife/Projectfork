<?php
/**
 * @package      Projectfork
 * @subpackage   Projects
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


jimport('joomla.application.component.controllerform');


class PFprojectsControllerProject extends JControllerForm
{
    /**
     * The prefix to use with controller messages.
     *
     * @var    string
     */
    protected $text_prefix = "COM_PROJECTFORK_PROJECT";


    /**
     * Class constructor.
     *
     * @param    array    $config    A named array of configuration variables
     */
    public function __construct($config = array())
    {
        parent::__construct($config);
    }


    /**
     * Method to get a model object, loading it if required.
     *
     * @param     string    $name      The model name. Optional.
     * @param     string    $prefix    The class prefix. Optional.
     * @param     array     $config    Configuration array for model. Optional.
     *
     * @return    object               The model.
     */
    public function getModel($name = 'Project', $prefix = 'PFprojectsModel', $config = array('ignore_request' => true))
    {
        return parent::getModel($name, $prefix, $config);
    }


    /**
     * Method to save a record.
     *
     * @param     string     $key       The name of the primary key of the URL variable.
     * @param     string     $urlVar    The name of the URL variable if different from the primary key.
     *
     * @return    boolean               True if successful, false otherwise.
     */
    public function save($key = null, $urlVar = null)
    {
        $data = JRequest::getVar('jform', array(), 'post', 'array');
        $task = $this->getTask();

        // Separate the different component rules before passing on the data
        if (isset($data['rules'])) {
            $rules = $data['rules'];

            if (isset($data['rules']['com_pfprojects'])) {
                $data['rules'] = $data['rules']['com_pfprojects'];

                unset($rules['com_pfprojects']);
            }

            $data['component_rules'] = $rules;
        }


        if ($task == 'save2copy') {
            // Reset the repo dir when saving as copy
            if (isset($data['attribs']['repo_dir'])) {
                $dir = (int) $data['attribs']['repo_dir'];

                if ($dir) {
                    $data['attribs']['repo_dir'] = 0;
                }
            }

            // Reset label id's
            if (isset($data['labels']) && is_array($data['labels'])) {
                foreach($data['labels'] AS $a => $g)
                {
                    if (isset($g['id'])) {
                        foreach($g['id'] AS $k => $i)
                        {
                            $data['labels'][$a]['id'][$k] = 0;
                        }
                    }
                }
            }

            $recordId = JRequest::getUInt('id');

            if ($recordId) {
                // Store the current project id in session
                $context = "$this->option.copy.$this->context.id";
                $app     = JFactory::getApplication();

                $app->setUserState($context, intval($recordId));

                $cfg = JComponentHelper::getParams('com_pfprojects');
                $create_group   = (int) $cfg->get('create_group');

                if ($create_group) {
                    // Get the project attribs
                    $db = JFactory::getDbo();
                    $query = $db->getQuery(true);

                    $query->select('attribs')
                          ->from('#__pf_projects')
                          ->where('id = ' . (int) $recordId);

                    $db->setQuery($query, 0, 1);
                    $attribs = $db->loadResult();

                    // Turn to JRegistry object
                    $params = new JRegistry();
                    $params->loadString($attribs);

                    // Get custom user group
                    $group_id = (int) $params->get('usergroup');

                    // Replicate existing custom group settings
                    if ($group_id) {
                        // Copy component rules
                        if (isset($data['component_rules'])) {
                            $user     = JFactory::getUser();
                            $is_admin = $user->authorise('core.admin');

                            foreach ($data['component_rules'] AS $component => $rules)
                            {
                                foreach ($rules AS $action => $groups)
                                {
                                    if (!is_numeric($action) && is_array($groups)) {
                                        foreach ($groups AS $gid => $v)
                                        {
                                            if ($gid == $group_id) {
                                                if (!$is_admin && $action == 'core.admin') {
                                                    // Dont allow non-admins to inject core admin permission
                                                    unset($data['component_rules'][$component][$action]);
                                                }
                                                else {
                                                    unset($data['component_rules'][$component][$action][$gid]);
                                                    $data['component_rules'][$component][$action][0] = $v;
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }

                        // Copy item rules
                        if (isset($data['rules'])) {
                            foreach ($data['rules'] AS $action => $value)
                            {
                                if (is_numeric($action)) {
                                    if ($value == $group_id) {
                                        $data['rules'][$action] = 0;
                                    }
                                }
                                else {
                                    foreach ($value AS $k => $v)
                                    {
                                        if ($k == $group_id) {
                                            unset($data['rules'][$action][$k]);
                                            $data['rules'][$action][0] = $v;
                                        }
                                    }
                                }
                            }
                        }

                        // Copy group members
                        $query->clear();
                        $query->select('user_id')
                              ->from('#__user_usergroup_map')
                              ->where('group_id = ' . (int) $group_id);

                        $db->setQuery($query);
                        $add_users = (array) $db->loadColumn();

                        $add_append = "";

                        if (!isset($data['add_groupuser'])) {
                            $data['add_groupuser'] = array();
                        }

                        if (isset($data['add_groupuser'][$group_id])) {
                            $add_append = $data['add_groupuser'][$group_id];

                            unset($data['add_groupuser'][$group_id]);
                        }

                        $data['add_groupuser'][0] = implode(',', $add_users) . ($add_append == '' ? '' : ',' . $add_append);
                    }
                }
            }
        }

        if (version_compare(JVERSION, '3.0.0', 'ge')) {
            $this->input->post->set('jform', $data);
        }
        else {
            JRequest::setVar('jform', $data, 'post');
        }

        return parent::save($key, $urlVar);
    }
}
