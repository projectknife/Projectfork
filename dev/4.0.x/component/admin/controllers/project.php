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


class ProjectforkControllerProject extends JControllerForm
{
    /**
     * Class constructor.
     *
     * @param     array              $config    A named array of configuration variables
     * @return    jcontrollerform               
     */
    public function __construct($config = array())
    {
        parent::__construct($config);
    }


    /**
     * Sets the currently active project for the user
     *
     **/
    public function setActive()
    {
        $data = array();
        $data['id'] = JRequest::GetInt('id');

        $model = $this->getModel('project');
        $app   = JFactory::getApplication();

        $model->setActive($data);

        $return = base64_decode(JRequest::getVar('return'));
        $app->redirect($return);

        return $this;
    }
}
