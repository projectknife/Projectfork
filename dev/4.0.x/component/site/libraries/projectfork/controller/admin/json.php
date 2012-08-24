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
 * JSON Admin List Controller
 *
 */
class ProjectforkControllerAdminJSON extends JControllerAdmin
{
    /**
     * Method to publish a list of items
     *
     * @return    void
     */
    public function publish()
    {
        $data = array();
        $data['success']  = "true";
        $data['messages'] = array();
        $data['data']     = array();

        // Check for request forgeries
        if (!JSession::checkToken()) {
            $data['success']    = false;
            $data['messages'][] = JText::_('JINVALID_TOKEN');

            $this->sendResponse($data);
        }

        // Get items to publish from the request.
        $cid   = JRequest::getVar('cid', array(), '', 'array');
        $data  = array('publish' => 1, 'unpublish' => 0, 'archive' => 2, 'trash' => -2, 'report' => -3);
        $task  = $this->getTask();
        $value = JArrayHelper::getValue($data, $task, 0, 'int');

        if (empty($cid)) {
            $data['success']    = "false";
            $data['messages'][] = JText::_($this->text_prefix . '_NO_ITEM_SELECTED');
        }
        else {
            // Get the model.
            $model = $this->getModel();

            // Make sure the item ids are integers
            JArrayHelper::toInteger($cid);

            // Publish the items.
            if (!$model->publish($cid, $value)) {
                 $data['success']    = "false";
                 $data['messages'][] = $model->getError();
            }
            else {
                if ($value == 1) {
                    $ntext = $this->text_prefix . '_N_ITEMS_PUBLISHED';
                }
                elseif ($value == 0) {
                    $ntext = $this->text_prefix . '_N_ITEMS_UNPUBLISHED';
                }
                elseif ($value == 2) {
                    $ntext = $this->text_prefix . '_N_ITEMS_ARCHIVED';
                }
                else {
                    $ntext = $this->text_prefix . '_N_ITEMS_TRASHED';
                }

                $data['success']    = "true";
                $data['messages'][] = JText::plural($ntext, count($cid));
            }
        }

        $this->sendResponse($data);
    }


    /**
     * Removes an item.
     *
     * @return    void
     */
    public function delete()
    {
        $data = array();
        $data['success'] = "true";
        $data['messages'] = array();
        $data['data'] = array();

        // Check for request forgeries
        if (!JSession::checkToken()) {
            $data['success']    = false;
            $data['messages'][] = JText::_('JINVALID_TOKEN');

            $this->sendResponse($data);
        }

        // Get items to remove from the request.
        $cid = JRequest::getVar('cid', array(), '', 'array');

        if (!is_array($cid) || count($cid) < 1) {
            $data['success']    = "false";
            $data['messages'][] = JText::_($this->text_prefix . '_NO_ITEM_SELECTED');
        }
        else {
            // Get the model.
            $model = $this->getModel();

            // Make sure the item ids are integers
            jimport('joomla.utilities.arrayhelper');
            JArrayHelper::toInteger($cid);

            // Remove the items.
            if ($model->delete($cid)) {
                $data['success']    = "true";
                $data['messages'][] = JText::plural($this->text_prefix . '_N_ITEMS_DELETED', count($cid));
            }
            else {
                $data['success']    = "false";
                $data['messages'][] = $model->getError();
            }
        }

        $this->sendResponse($data);
    }


    /**
     * Check in of one or more records.
     *
     * @return    boolean    True on success
     */
    public function checkin()
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

        // Initialise variables.
        $ids = JRequest::getVar('cid', null, 'post', 'array');

        $model  = $this->getModel();
        $return = $model->checkin($ids);

        if ($return === false) {
            // Checkin failed.
            $data['success']    = "false";
            $data['messages'][] = JText::sprintf('JLIB_APPLICATION_ERROR_CHECKIN_FAILED', $model->getError());
        }
        else {
            // Checkin succeeded.
            $data['success']    = "true";
            $data['messages'][] = JText::plural($this->text_prefix . '_N_ITEMS_CHECKED_IN', count($ids));
        }

        $this->sendResponse($data);
    }


    /**
     * Sends a JSON response to the browser
     *
     * @param     string    $data    The data to send
     *
     * @return    void
     **/
    protected function sendResponse($data)
    {
        // Set the MIME type for JSON output.
        JFactory::getDocument()->setMimeEncoding('application/json');

        // Change the suggested filename.
        JResponse::setHeader('Content-Disposition', 'attachment;filename="' . $this->view_list . '.json"');

        // Output the JSON data.
        echo json_encode($data);

        jexit();
    }
}
