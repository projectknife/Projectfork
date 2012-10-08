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
class ProjectforkControllerTasks extends ProjectforkControllerAdminJSON
{
    /**
     * The default view
     *
     */
    protected $view_list = 'tasks';


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
     * Override for json return response
     *
     * @see       controlleradmin.php
     *
     * @return    string                 JSON encoded response
     */
    public function complete()
    {
        $data = array();
        $data['success']  = "true";
        $data['messages'] = array();
        $data['data']     = array();

        // Check for request forgeries
        if (!JSession::checkToken()) {
            $data['success']    = "false";
            $data['messages'][] = JText::_('JINVALID_TOKEN');

            $this->sendResponse($data);
        }

        // Get the input
        $pks = JRequest::getVar('cid', null, 'post', 'array');

        if (empty($pks)) {
            $data['success']    = "false";
            $data['messages'][] = JText::_($this->text_prefix . '_NO_ITEM_SELECTED');
        }
        else {
            // Get the model.
            $model = $this->getModel();

            // Make sure the item ids are integers
            JArrayHelper::toInteger($pks);

            // Publish the items.
            if (!$model->complete($pks)) {
                 $data['success']    = "false";
                 $data['messages'][] = $model->getError();
            }
            else {
                $ntext = $this->text_prefix . '_N_ITEMS_UPDATED';

                $data['success']    = "true";
                $data['messages'][] = JText::plural($ntext, count($pks));
            }
        }

        $this->sendResponse($data);
    }
}
