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


class ProjectforkControllerTime extends JControllerForm
{
    /**
     * The URL view list variable.
     *
     * @var    string    
     */
    protected $view_list = 'timesheet';


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
     * Sets the project id value of the current form
     *
     * @return    void    
     */
    public function setProject()
    {
        // Initialise variables.
        $app  = JFactory::getApplication();
        $data = JRequest::getVar('jform', array(), 'post', 'array');
        $id   = JRequest::getInt('id');

        $project = (int) $data['project_id'];

        // Set the project as active
        ProjectforkHelper::setActiveProject($project);

        //Save the data in the session.
        $app->setUserState('com_projectfork.edit.time.project', $project);
        $app->setUserState('com_projectfork.edit.time.data', $data);

        $this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view='  . $this->view_item . $this->getRedirectToItemAppend($id), false));
    }


    /**
     * Sets the task id value of the current form
     *
     * @return    void    
     */
    public function setTask()
    {
        $id = JRequest::getInt('id');

        $this->setFormData();
        $this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view='  . $this->view_item . $this->getRedirectToItemAppend($id), false));
    }


    /**
     * Sets the selected access level of the form
     *
     * @return    void    
     */
    public function setAccess()
    {
        $id = JRequest::getInt('id');

        $this->setFormData();
        $this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view='  . $this->view_item . $this->getRedirectToItemAppend($id), false));
    }


    /**
     * Stores the form data
     *
     * @return    void    
     */
    protected function setFormData()
    {
        // Initialise variables.
        $app  = JFactory::getApplication();
        $data = JRequest::getVar('jform', array(), 'post', 'array');

        $app->setUserState('com_projectfork.edit.time.data', $data);
    }


}
