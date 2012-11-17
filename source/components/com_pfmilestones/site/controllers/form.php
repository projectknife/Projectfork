<?php
/**
 * @package      Projectfork
 * @subpackage   Milestones
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


jimport('joomla.application.component.controllerform');


/**
 * Projectfork Milestone Form Controller
 *
 */
class PFmilestonesControllerForm extends JControllerForm
{
    /**
     * The default item view
     *
     * @var    string
     */
    protected $view_item = 'form';

    /**
     * The default list view
     *
     * @var    string
     */
    protected $view_list = 'milestones';

    /**
	 * The prefix to use with controller messages.
	 *
	 * @var    string
	 */
	protected $text_prefix = 'COM_PROJECTFORK_MILESTONE';


    /**
     * Constructor
     *
     */
    public function __construct($config = array())
	{
	    parent::__construct($config);

        // Register additional tasks
		$this->registerTask('save2tasklist', 'save');
		$this->registerTask('save2task', 'save');
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
            return false;
        }

        return true;
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
     * Method to edit an existing record.
     *
     * @param     string     $key        The name of the primary key of the URL variable.
     * @param     string     $url_var    The name of the URL variable if different from the primary key.
     *
     * @return    boolean                True if access level check and checkout passes, false otherwise.
     */
    public function edit($key = null, $url_var = 'id')
    {
        $result = parent::edit($key, $url_var);

        return $result;
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
    public function &getModel($name = 'Form', $prefix = 'PFmilestonesModel', $config = array('ignore_request' => true))
    {
        $model = parent::getModel($name, $prefix, $config);

        return $model;
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
        $user  = JFactory::getUser();
        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);

        $access  = true;
        $project = isset($data['project_id']) ? (int) $data['project_id'] : 0;

        // Check if the user has access to the project
        if (!$user->authorise('core.admin', 'com_pfprojects')) {
            if ($project) {
                $query->select('access')
                      ->from('#__pf_projects')
                      ->where('id = ' . $db->quote((int) $project));

                $db->setQuery($query);
                $access = in_array((int) $db->loadResult(), $user->getAuthorisedViewLevels());
            }
        }

        return ($user->authorise('core.create', 'com_pfmilestones') && $access);
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
        // Initialise variables.
        $id     = (int) isset($data[$key]) ? $data[$key] : 0;
        $uid    = JFactory::getUser()->get('id');
        $access = PFmilestonesHelper::getActions($id);

        // Check general edit permission first.
        if ($access->get('core.edit')) {
            return true;
        }

        // Fallback on edit.own.
        // First test if the permission is available.
        if ($access->get('core.edit.own')) {
            // Now test the owner is the user.
            $owner = (int) isset($data['created_by']) ? $data['created_by'] : 0;

            if (empty($owner) && $id) {
                // Need to do a lookup from the model.
                $record = $this->getModel()->getItem($id);

                if (empty($record)) return false;

                $owner = $record->created_by;
            }

            // If the owner matches 'me' then do the test.
            if ($owner == $uid) return true;
        }

        // Fall back to the component permissions.
        return parent::allowEdit($data, $key);
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
            return JRoute::_('index.php?option=com_pfmilestones&view=' . $this->view_list, false);
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
    protected function postSaveHook(&$model, $data)
    {
        $task = $this->getTask();
        $id   = (int) $model->getState('form.id');

        switch($task)
        {
            case 'save2copy':
            case 'save2new':
                // No redirect because its already set
                break;

            case 'save2tasklist':
                $link = JRoute::_(PFtasksHelperRoute::getTasksRoute() . '&task=tasklistform.add&milestone_id=' . $id);
                $this->setRedirect($link);
                break;

            case 'save2task':
                $link = JRoute::_(PFtasksHelperRoute::getTasksRoute() . '&task=taskform.add&milestone_id=' . $id);
                $this->setRedirect($link);
                break;

            default:
                $this->setRedirect($this->getReturnPage());
                break;
        }
    }
}
