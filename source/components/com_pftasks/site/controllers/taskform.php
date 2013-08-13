<?php
/**
 * @package      Projectfork
 * @subpackage   Tasks
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


jimport('joomla.application.component.controllerform');


/**
 * Projectfork Task Form Controller
 *
 */
class PFtasksControllerTaskForm extends JControllerForm
{
    /**
     * The default item view
     *
     * @var    string
     */
    protected $view_item = 'taskform';

    /**
     * The default list view
     *
     * @var    string
     */
    protected $view_list = 'tasks';


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
    public function &getModel($name = 'TaskForm', $prefix = 'PFtasksModel', $config = array('ignore_request' => true))
    {
        $model = parent::getModel($name, $prefix, $config);

        return $model;
    }


    /**
     * Method to add a new record.
     *
     * @return    boolean    True if the item can be added, false if not.
     */
    public function add()
    {
        if (!parent::add()) {
            // Redirect to the return page.
            $this->setRedirect($this->getReturnPage());
        }
    }


    /**
     * Method to cancel an edit.
     *
     * @param     string     $key    The name of the primary key of the URL variable.
     *
     * @return    boolean            True if access level checks pass, false otherwise.
     */
    public function cancel($key = 'id')
    {
        $result = parent::cancel($key);

        // Redirect to the return page.
        $this->setRedirect($this->getReturnPage());

        return $result;
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
        // Get form input
        $project = isset($data['project_id'])   ? (int) $data['project_id']   : PFApplicationHelper::getActiveProjectId();
        $ms      = isset($data['milestone_id']) ? (int) $data['milestone_id'] : 0;
        $list    = isset($data['list_id'])      ? (int) $data['list_id']      : 0;

        $user   = JFactory::getUser();
        $db     = JFactory::getDbo();
        $is_sa  = $user->authorise('core.admin');
        $levels = $user->getAuthorisedViewLevels();
        $query  = $db->getQuery(true);
        $asset  = 'com_pftasks';
        $access = true;

        // Check if the user has access to the project
        if ($project) {
            // Check if in allowed projects when not a super admin
            if (!$is_sa) {
                $access = in_array($project, PFUserHelper::getAuthorisedProjects());
            }

            // Change the asset name
            $asset  .= '.project.' . $project;
        }

        // Check if the user can access the selected milestone when not a super admin
        if (!$is_sa && $ms && $access) {
            $query->select('access')
                  ->from('#__pf_milestones')
                  ->where('id = ' . $db->quote((int) $ms));

            $db->setQuery($query);
            $lvl = $db->loadResult();

            $access = in_array($lvl, $levels);
        }

        // Check if the user can access the selected task list when not a super admin
        if (!$is_sa && $list && $access) {
            $query->clear()
                  ->select('access')
                  ->from('#__pf_task_lists')
                  ->where('id = ' . $list);

            $db->setQuery($query);
            $lvl = $db->loadResult();

            $access = in_array($lvl, $levels);

            // Change asset to list
            $asset = 'com_pftasks.tasklist.' . $list;
        }

        return ($user->authorise('core.create', $asset) && $access);
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
        $asset = 'com_pftasks.task.' . $id;

        // Check if the user has viewing access when not a super admin
        if (!$user->authorise('core.admin')) {
            $db    = JFactory::getDbo();
            $query = $db->getQuery(true);

            $query->select('access')
                  ->from('#__pf_tasks')
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

        // Fallback on edit.own.
        // First test if the permission is available.
        if (!$user->authorise('core.edit.own', $asset)) {
            return false;
        }

        // Load the item
        $record = $this->getModel()->getItem($id);

        // Abort if not found
        if (empty($record)) return false;

        // Now test the owner is the user.
        $owner = (int) isset($data['created_by']) ? (int) $data['created_by'] : $record->created_by;

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
        $ms_id   = JRequest::getUInt('milestone_id');
        $list_id = JRequest::getUInt('list_id');
        $return  = $this->getReturnPage();
        $append  = '';

        // Setup redirect info.
        if ($tmpl) $append .= '&tmpl=' . $tmpl;

        $append .= '&layout=edit';
        if ($id)      $append .= '&' . $url_var . '=' . $id;
        if ($ms_id)   $append .= '&milestone_id=' . $ms_id;
        if ($list_id) $append .= '&list_id=' . $list_id;
        if ($item_id) $append .= '&Itemid=' . $item_id;
        if ($return)  $append .= '&return=' . base64_encode($return);

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
            $app       = JFactory::getApplication();
            $project   = PFApplicationHelper::getActiveProjectId();
            $milestone = (int) $app->getUserStateFromRequest('com_pftasks.tasks.filter.milestone', 'milestone_id', '');
            $list      = (int) $app->getUserStateFromRequest('com_pftasks.tasks.filter.tasklist', 'list_id', '');

            return JRoute::_(PFtasksHelperRoute::getTasksRoute($project, $milestone, $list), false);
        }
        else {
            return base64_decode($return);
        }
    }


    /**
     * Function that allows child controller access to model data after the data has been saved.
     *
     * @param     jmodel    $model        The data model object.
     * @param     array     $validData    The validated data.
     *
     * @return    void
     */
    protected function postSaveHook(&$model, $validData)
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

            default:
                $this->setRedirect($this->getReturnPage());
                break;
        }
    }
}
