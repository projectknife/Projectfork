<?php
/**
 * @package      Projectfork
 * @subpackage   Tasks
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2013 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


jimport('joomla.application.component.controlleradmin');


/**
 * Projectfork Tasks (list) controller class.
 *
 */
class PFtasksControllerTasks extends JControllerAdmin
{
    /**
     * The prefix to use with controller messages.
     *
     * @var    string
     */
    protected $text_prefix = "COM_PROJECTFORK_TASKS";


    /**
     * Proxy for getModel.
     *
     * @param     string    $name      The name of the model.
     * @param     string    $prefix    The prefix for the class name.
     * @param     array     $config    Configuration array for model. Optional.
     *
     * @return    object
     */
    public function getModel($name = 'Task', $prefix = 'PFtasksModel', $config = array('ignore_request' => true))
    {
        return parent::getModel($name, $prefix, $config);
    }


    /**
     * Method to save the submitted ordering values for records via AJAX.
     *
     * @return    void
     */
    public function saveOrderAjax()
    {
        $pks   = $this->input->post->get('cid', array(), 'array');
        $order = $this->input->post->get('order', array(), 'array');

        // Sanitize the input
        JArrayHelper::toInteger($pks);
        JArrayHelper::toInteger($order);

        if ($this->getModel()->saveorder($pks, $order)) echo "1";

        // Close the application
        JFactory::getApplication()->close();
    }
}
