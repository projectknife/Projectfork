<?php
/**
 * @package      pkg_projectfork
 * @subpackage   com_pfprojects
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2013 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


jimport('joomla.application.component.controllerform');


/**
 * Projectfork Project Form Controller
 *
 */
class PFprojectsControllerForm extends JControllerForm
{
    /**
     * Default item view
     *
     * @var    string
     */
    protected $view_item = 'form';

    /**
     * Default list view
     *
     * @var    string
     */
    protected $view_list = 'projects';


    /**
     * Constructor
     *
     */
    public function __construct($config = array())
	{
	    parent::__construct($config);

        // Register additional tasks
        $this->registerTask('save2milestone', 'save');
		$this->registerTask('save2tasklist', 'save');
		$this->registerTask('save2task', 'save');
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
    public function &getModel($name = 'Form', $prefix = '', $config = array('ignore_request' => true))
    {
        $model = parent::getModel($name, $prefix, $config);

        return $model;
    }


    /**
     * Method to add a new record.
     *
     * @return    boolean    True if the article can be added, false if not.
     */
    public function add()
    {
        if (!parent::add()) {
            // Redirect to the return page.
            $this->setRedirect($this->getReturnPage());
        }
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

        // Reset the repo dir when saving as copy
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

            // Store the current project id in session
            $recordId = JRequest::getUInt('id');

            if ($recordId) {
                $context = "$this->option.copy.$this->context.id";
                $app     = JFactory::getApplication();

                $app->setUserState($context, intval($recordId));
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


    /**
     * Method to check if you can add a new record.
     *
     * @param     array      $data    An array of input data.
     *
     * @return    boolean
     */
    protected function allowAdd($data = array())
    {
        return JFactory::getUser()->authorise('core.create', 'com_pfprojects');
    }


    /**
     * Method override to check if you can edit an existing record.
     *
     * @param     array      $data    An array of input data.
     * @param     string     $key     The name of the key for the primary key.
     *
     * @return    boolean
     */
    protected function allowEdit($data = array(), $key = 'id')
    {
        // Get form input
        $id = (int) isset($data[$key]) ? $data[$key] : 0;

        $user  = JFactory::getUser();
        $uid   = $user->get('id');
        $asset = 'com_pfprojects.project.' . $id;

        // Check if the user has viewing access when not a super admin
        if (!$user->authorise('core.admin')) {
            $db    = JFactory::getDbo();
            $query = $db->getQuery(true);

            $query->select('access')
                  ->from('#__pf_projects')
                  ->where('id = ' . $id);

            $db->setQuery($query);
            $lvl = $db->loadResult();

            if (!in_array($lvl, $user->getAuthorisedViewLevels())) {
                return false;
            }
        }

        // Check edit permission first
        if ($user->authorise('core.edit', $asset)) {
            return true;
        }

        // Fall back on edit.own.
        // First test if the permission is available.
        if (!$user->authorise('core.edit.own', $asset)) {
            return false;
        }

        // Now test the owner is the user.
        $owner = (int) isset($data['created_by']) ? (int) $data['created_by'] : 0;

        if (!$owner && $id) {
            // Need to do a lookup from the model.
            $record = $this->getModel()->getItem($id);

            if (empty($record)) return false;

            $owner = $record->created_by;
        }

        // If the owner matches 'me' then do the test.
        return ($owner == $uid && $uid > 0);
    }


    /**
     * Gets the URL arguments to append to an item redirect.
     *
     * @param     int       $id         The primary key id for the item.
     * @param     string    $url_var    The name of the URL variable for the id.
     *
     * @return    string                The arguments to append to the redirect URL.
     */
    protected function getRedirectToItemAppend($id = null, $url_var = 'id')
    {
        // Need to override the parent method completely.
        $tmpl    = JRequest::getCmd('tmpl');
        $layout  = JRequest::getCmd('layout', 'edit');
        $item_id = JRequest::getUInt('Itemid');
        $return  = $this->getReturnPage();
        $append  = '';

        // Setup redirect info.
        if ($tmpl) $append .= '&tmpl=' . $tmpl;

        $append .= '&layout=edit';
        if ($id)      $append .= '&' . $url_var . '=' . $id;
        if ($item_id) $append .= '&Itemid=' . $item_id;
        if ($return)  $append .= '&return='.base64_encode($return);

        return $append;
    }


    /**
     * Get the return URL.
     * If a "return" variable has been passed in the request
     *
     * @return    string    The return URL.
     */
    protected function getReturnPage()
    {
        $return = JRequest::getVar('return', null, 'default', 'base64');

        if (empty($return) || !JUri::isInternal(base64_decode($return))) {
            return JRoute::_(PFprojectsHelperRoute::getProjectsRoute(), false);
        }
        else {
            return base64_decode($return);
        }
    }


    /**
     * Function that allows child controller access to model data after the data has been saved.
     *
     * @param     jmodel    $model    The data model object.
     * @param     array     $data     The validated data.
     *
     * @return    void
     */
    protected function postSaveHook($model, $data = array())
    {
        $task = $this->getTask();

        switch($task)
        {
            case 'save2copy':
            case 'save2new':
                // No redirect because its already set
                break;

            case 'save2milestone':
                $link = JRoute::_(PFmilestonesHelperRoute::getMilestonesRoute() . '&task=form.add');
                $this->setRedirect($link);
                break;

            case 'save2tasklist':
                $link = JRoute::_(PFtasksHelperRoute::getTasksRoute() . '&task=tasklistform.add');
                $this->setRedirect($link);
                break;

            case 'save2task':
                $link = JRoute::_(PFtasksHelperRoute::getTasksRoute() . '&task=taskform.add');
                $this->setRedirect($link);
                break;

            default:
                $this->setRedirect($this->getReturnPage());
                break;
        }
    }
}
