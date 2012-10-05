<?php
/**
 * @package      Projectfork
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


jimport('joomla.application.component.controllerform');


/**
 * Projectfork File Form Controller
 *
 */
class ProjectforkControllerFileForm extends JControllerForm
{
    /**
     * The default item view
     *
     * @var    string
     */
    protected $view_item = 'fileform';

    /**
     * The default list view
     *
     * @var    string
     */
    protected $view_list = 'repository';


    /**
     * Constructor.
     *
     * @param    array          $config    An optional associative array of configuration settings.
     *
     * @see      jcontroller
     */
    public function __construct($config = array())
    {
        parent::__construct($config);
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
    public function &getModel($name = 'FileForm', $prefix = 'ProjectforkModel', $config = array('ignore_request' => true))
    {
        $model = parent::getModel($name, $prefix, $config);

        return $model;
    }


    /**
     * Method to save a record.
     *
     * @param     string     $key       The name of the primary key of the URL variable.
     * @param     string     $urlVar    The name of the URL variable if different from the primary key (sometimes required to avoid router collisions).
     *
     * @return    boolean               True if successful, false otherwise.
     */
    public function save($key = null, $urlVar = null)
    {
        // Check for request forgeries.
        JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

        // Initialise variables.
        $model     = $this->getModel();
        $data      = JRequest::getVar('jform', array(), 'post', 'array');
        $file_form = JRequest::getVar('jform', '', 'files', 'array');
        $context   = $this->option . ".edit." . $this->context;
        $layout    = JRequest::getVar('layout');
        $files     = array();

        if (empty($urlVar)) $urlVar = $key;

        $record_id = JRequest::getInt($urlVar);

        if (!$this->checkEditId($context, $record_id)) {
			// Somehow the person just went to the form and tried to save it. We don't allow that.
			$this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_UNHELD_ID', $record_id));
			$this->setMessage($this->getError(), 'error');

			$this->setRedirect(
				JRoute::_(
					'index.php?option=' . $this->option . '&view=' . $this->view_list
					. $this->getRedirectToListAppend(), false
				)
			);

			return false;
		}

        if (is_array($file_form)) {
            foreach($file_form AS $attr => $field)
            {
                $count = count($field);
                $i     = 0;

                while($count > $i)
                {
                    foreach($field AS $name => $value)
                    {
                        $files[$i][$attr] = $value;
                    }
                    $i++;
                }
            }
        }

        if (count($files) == 1) {
            $result = $model->upload(array_pop($files), $data['project_id']);

            if (is_array($result)) {
                $keys = array_keys($result);

                foreach($keys AS $k)
                {
                    $data[$k] = $result[$k];
                }
            }
            else {
                $error = $model->getError();
                $this->setError($error);
                $this->setMessage($error, 'error');

                if ($layout != 'modal') {
                    $this->setRedirect(
                        JRoute::_(
                            'index.php?option=' . $this->option . '&view=' . $this->view_item
                            . $this->getRedirectToItemAppend($record_id, $urlVar), false
                        )
                    );
                }
                else {
                    $this->setRedirect(
        				JRoute::_(
        					'index.php?option=' . $this->option . '&view=' . $this->view_list
        					. $this->getRedirectToListAppend(), false
        				)
        			);
                }

                return false;
            }
        }

        JRequest::setVar('jform', $data, 'post');

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
        $acl    = ProjectforkHelperAccess::getActions(null, 0, true);
        $user   = JFactory::getUser();
        $parent  = JRequest::getUint('filter_parent_id', 0);

        $can_create = $acl->get('file.create');
        $access     = true;

        if (isset($data['dir_id'])) {
            $parent = (int) $data['dir_id'];
        }

        // Verify topic access
        if ($parent) {
            $model = $this->getModel('DirectoryForm', 'ProjectforkModel', array('ignore_request' => true));
            $item  = $model->getItem($parent);

            if (!empty($item)) {
                if (!$user->authorise('core.admin')) {
                    if (!in_array($item->access, $user->getAuthorisedViewLevels())) {
                        $this->setError(JText::_('COM_PROJECTFORK_WARNING_DIRECTORY_ACCESS_DENIED'));
                        $access = false;
                    }
                }
            }
            else {
                $this->setError(JText::_('COM_PROJECTFORK_WARNING_DIRECTORY_NOT_FOUND'));
                $access = false;
            }
        }
        else {
            $access = false;
        }

        return ($access && $can_create);
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
        $access = ProjectforkHelperAccess::getActions('file', $id);

        // Check general edit permission first.
        if ($access->get('file.edit')) {
            return true;
        }

        // Fallback on edit.own.
        // First test if the permission is available.
        if ($access->get('file.edit.own')) {
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

        // Since there is no asset tracking, revert to the component permissions.
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
        $project = JRequest::getUint('filter_project', 0);
        $parent  = JRequest::getUint('filter_parent_id', 0);
        $return  = $this->getReturnPage($parent, $project);
        $append  = '';


        // Setup redirect info.
        if ($tmpl) $append .= '&tmpl=' . $tmpl;

        $append .= '&layout=edit';
        if ($project) $append .= '&filter_project=' . $project;
        if ($parent)  $append .= '&filter_parent_id=' . $parent;
        if ($id)      $append .= '&' . $url_var . '=' . $id;
        if ($item_id) $append .= '&Itemid=' . $item_id;
        if ($return)  $append .= '&return='.base64_encode($return);

        return $append;
    }


    /**
     * Gets the URL arguments to append to a list redirect.
     *
     * @return    string    The arguments to append to the redirect URL.
     */
    protected function getRedirectToListAppend()
    {
        $tmpl    = JRequest::getCmd('tmpl');
        $project = JRequest::getUint('filter_project');
        $parent  = JRequest::getUint('filter_parent_id');
        $layout  = JRequest::getCmd('layout');
        $func    = JRequest::getCmd('function');
        $append  = '';

        // Setup redirect info.
        if ($project) {
            $append .= '&filter_project=' . $project;
        }

        if ($parent) {
            $append .= '&filter_parent_id=' . $parent;
        }

        if ($tmpl) {
            $append .= '&tmpl=' . $tmpl;
        }

        if ($layout) {
            $append .= '&layout=' . $layout;
        }

        if ($func) {
            $append .= '&function=' . $func;
        }

        return $append;
    }


    /**
     * Get the return URL.
     * If a "return" variable has been passed in the request
     *
     * @return    string    The return URL.
     */
    protected function getReturnPage($parent, $project = 0)
    {
        $return = JRequest::getVar('return', null, 'default', 'base64');
        $append = '';

        if ($project) $append .= '&filter_project=' . $project;
        if ($parent)   $append .= '&filter_parent_id=' . $parent;

        if (empty($return) || !JUri::isInternal(base64_decode($return))) {
            return JRoute::_('index.php?option=com_projectfork&view=' . $this->view_list . $append, false);
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

        /*if ($task == 'save') {
            $this->setRedirect(JRoute::_('index.php?option=com_projectfork&view=' . $this->view_list, false));
        }*/
    }
}
