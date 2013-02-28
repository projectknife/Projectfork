<?php
/**
 * @package      Projectfork
 * @subpackage   Timetracking
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2013 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


jimport('joomla.application.component.controlleradmin');


/**
 * Projectfork Time Recorder JSON Controller
 *
 */
class PFtimeControllerRecorder extends PFControllerAdminJson
{
    /**
     * The default view
     *
     * @var    string
     */
    protected $view_list = 'recorder';


    /**
     * Method to get a model object, loading it if required.
     *
     * @param     string    $name      The model name. Optional.
     * @param     string    $prefix    The class prefix. Optional.
     * @param     array     $config    Configuration array for model. Optional.
     *
     * @return    object               The model.
     */
    public function &getModel($name = 'Recorder', $prefix = 'PFtimeModel', $config = array('ignore_request' => true))
    {
        $model = parent::getModel($name, $prefix, $config);

        return $model;
    }


    /**
     * Method to toggle the pause state of one or more items in the recorder
     *
     * @return    void
     */
    public function pause()
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

            // Pause the items.
            if (!$model->pause($pks)) {
                 $data['success'] = "false";
                 $data['messages'][] = $model->getError();
            }
            else {
                $data['success'] = "true";
            }
        }

        $this->sendResponse($data);
    }


    /**
     * Method to punch-in items in the recorder
     *
     * @return    void
     */
    public function punch()
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

        // Get the model.
        $model = $this->getModel();

        // Punch-in items.
        if (!$model->punch()) {
             $data['success'] = "false";
             $data['messages'][] = $model->getError();
             $this->sendResponse($data);
        }

        $data['success'] = "true";

        $app   = JFactory::getApplication();
        $items = $app->getUserState('com_pftime.recorder.data');

        // Make sure we have items
        if (!is_array($items) || count($items) == 0) {
            $this->sendResponse($data);
        }

        foreach ($items AS $rec)
        {
            $id   = $rec['id'];
            $time = $rec['time'];

            $data['data'][$id] = JHtml::_('time.format', $time);
        }

        $this->sendResponse($data);
    }


    /**
     * Method to update records details in the recorder
     *
     * @return    void
     */
    public function save()
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
            if (!$model->save($pks)) {
                 $data['success'] = "false";
                 $data['messages'][] = $model->getError();
            }
            else {
                $data['success'] = "true";
            }
        }

        $this->sendResponse($data);
    }


    /**
     * Method to remove items from the recorder
     *
     * @return    void
     */
    public function delete()
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
        $c   = JRequest::getUint('complete');

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
            if (!$model->delete($pks, $c)) {
                 $data['success'] = "false";
                 $data['messages'][] = $model->getError();
            }
            else {
                $data['success'] = "true";
            }
        }

        $this->sendResponse($data);
    }
}
