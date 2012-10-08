<?php
/**
 * @package      Projectfork
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


jimport('joomla.application.component.controlleradmin');


/**
 * Projectfork Task List Controller
 *
 */
class ProjectforkControllerTasks extends JControllerAdmin
{
    /**
     * The default list view
     *
     * @var    string
     */
    protected $view_list = 'tasks';

    /**
	 * The prefix to use with controller messages.
	 *
	 * @var    string
	 */
	protected $text_prefix = 'COM_PROJECTFORK_TASKS';


    /**
     * Method to get a model object, loading it if required.
     *
     * @param     string    $name      The model name. Optional.
     * @param     string    $prefix    The class prefix. Optional.
     * @param     array     $config    Configuration array for model. Optional.
     *
     * @return    object               The model.
     */
    public function &getModel($name = 'TaskForm', $prefix = 'ProjectforkModel', $config = array('ignore_request' => true))
    {
        $model = parent::getModel($name, $prefix, $config);

        return $model;
    }


    /**
     * Method to save the priority of one or more tasks
     *
     * @return    boolean    True on success, otherwise false
     */
    public function savePriority()
    {
        // Check for request forgeries.
        JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

        // Initialise variables.
        $ids  = JRequest::getVar('cid', null, 'post', 'array');
        $pids = JRequest::getVar('priority', null, 'post', 'array');

        $model  = $this->getModel();
        $return = $model->savePriority($ids, $pids);

        if ($return === false) {
            // Storage failed.
            $message = JText::sprintf('COM_PROJECTFORK_ERROR_SAVEPRIORITY_FAILED', $model->getError());
            $this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_list, false), $message, 'error');
            return false;
        }
        else {
            // Storage succeeded.
            $message = JText::_('COM_PROJECTFORK_SUCCESS_TASK_SAVEPRIORITY');
            $this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_list, false), $message);
            return true;
        }
    }


    /**
     * Method to assign a user to one or more tasks
     *
     * @return    boolean    True on success, otherwise false
     */
    public function addUsers()
    {
        // Check for request forgeries.
        JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

        // Initialise variables.
        $ids  = JRequest::getVar('cid', null, 'post', 'array');
        $uids = JRequest::getVar('assigned', null, 'post', 'array');

        $model  = $this->getModel();
        $return = $model->addUsers($ids, $uids);

        if ($return === false) {
            // Assigning failed.
            $message = JText::sprintf('COM_PROJECTFORK_ERROR_ADDUSER_FAILED', $model->getError());
            $this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_list, false), $message, 'error');
            return false;
        }
        else {
            // Assigning succeeded.
            $message = JText::_('COM_PROJECTFORK_SUCCESS_TASK_ADDUSER');
            $this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_list, false), $message);
            return true;
        }
    }


    /**
     * Method to remove a user from one or more tasks
     *
     * @return    boolean    True on success, otherwise false
     */
    public function deleteUsers()
    {
        // Check for request forgeries.
        JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

        // Initialise variables.
        $ids  = JRequest::getVar('cid', null, 'post', 'array');
        $uids = JRequest::getVar('assigned', null, 'post', 'array');

        $model  = $this->getModel();
        $return = $model->deleteUsers($ids, $uids);

        if ($return === false) {
            // Deletion failed.
            $message = JText::sprintf('COM_PROJECTFORK_ERROR_ADDUSER_FAILED', $model->getError());
            $this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_list, false), $message, 'error');
            return false;
        }
        else {
            // Deletion succeeded.
            $message = JText::_('COM_PROJECTFORK_SUCCESS_TASK_ADDUSER');
            $this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_list, false), $message);
            return true;
        }
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
        $layout  = JRequest::getCmd('layout');
        $item_id = JRequest::getUInt('Itemid');
        $return  = $this->getReturnPage();
        $append  = '';

        // Setup redirect info.
        if ($tmpl)    $append .= '&tmpl=' . $tmpl;
        if ($layout)  $append .= '&layout=' . $layout;
        if ($id)      $append .= '&' . $url_var . '=' . $id;
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
            return JURI::base();
        }

        return base64_decode($return);
    }
}
