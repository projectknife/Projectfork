<?php
/**
* @package      Projectfork
*
* @author       Tobias Kuhn (eaxs)
* @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
* @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
**/

defined('_JEXEC') or die();


jimport('joomla.application.component.controllerform');


/**
 * JSON Admin Form Controller
 *
 */
class ProjectforkControllerFormJSON extends JControllerForm
{
    /**
     * Method to run batch operations.
     *
     * @param     jmodel     $model    The model of the component being processed.
     *
     * @return    boolean              True if successful, false otherwise and internal error is set.
     */
    public function batch($model)
    {
        // Initialise variables.
        $input = JFactory::getApplication()->input;
        $vars  = $input->post->get('batch', array(), 'array');
        $cid   = $input->post->get('cid', array(), 'array');

        $data = array();
        $data['success']  = "true";
        $data['messages'] = array();
        $data['data']     = array();

        // Build an array of item contexts to check
        $contexts = array();

        foreach ($cid as $id)
        {
            // If we're coming from com_categories, we need to use extension vs. option
            if (isset($this->extension)) {
                $option = $this->extension;
            }
            else {
                $option = $this->option;
            }

            $contexts[$id] = $option . '.' . $this->context . '.' . $id;
        }

        // Attempt to run the batch operation.
        if ($model->batch($vars, $cid, $contexts)) {
            $data['messages'][] = JText::_('JLIB_APPLICATION_SUCCESS_BATCH');

            $this->sendResponse($data);
        }
        else {
            $data['success']    = "false";
            $data['messages'][] = JText::sprintf('JLIB_APPLICATION_ERROR_BATCH_FAILED', $model->getError());

            $this->sendResponse($data);
        }
    }

    /**
     * Method to cancel an edit.
     *
     * @param     string     $key    The name of the primary key of the URL variable.
     *
     * @return    boolean            True if access level checks pass, false otherwise.
     */
    public function cancel($key = null)
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
        $app     = JFactory::getApplication();
        $model   = $this->getModel();
        $table   = $model->getTable();
        $checkin = property_exists($table, 'checked_out');
        $context = "$this->option.edit.$this->context";

        if (empty($key)) {
            $key = $table->getKeyName();
        }

        $recordId = JRequest::getInt($key);

        // Attempt to check-in the current record.
        if ($recordId) {
            // Check we are holding the id in the edit list.
            if (!$this->checkEditId($context, $recordId)) {
                // Somehow the person just went to the form - we don't allow that.
                $data['success']    = "false";
                $data['messages'][] = JText::sprintf('JLIB_APPLICATION_ERROR_UNHELD_ID', $recordId);

                $this->sendResponse($data);
            }

            if ($checkin) {
                if ($model->checkin($recordId) === false) {
                    // Check-in failed, go back to the record and display a notice.
                    $data['success']    = "false";
                    $data['messages'][] = JText::sprintf('JLIB_APPLICATION_ERROR_CHECKIN_FAILED', $model->getError());

                    $this->sendResponse($data);
                }
            }
        }

        // Clean the session data and redirect.
        $this->releaseEditId($context, $recordId);
        $app->setUserState($context . '.data', null);

        $this->sendResponse($data);
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
        $rdata = array();
        $rdata['success']  = "true";
        $rdata['messages'] = array();
        $rdata['data']     = array();

        // Check for request forgeries
        if (!JSession::checkToken()) {
            $rdata['success']    = "false";
            $rdata['messages'][] = JText::_('JINVALID_TOKEN');

            $this->sendResponse($rdata);
        }

        // Initialise variables.
        $app   = JFactory::getApplication();
        $lang  = JFactory::getLanguage();
        $model = $this->getModel();
        $table = $model->getTable();
        $data  = JRequest::getVar('jform', array(), 'post', 'array');
        $checkin = property_exists($table, 'checked_out');
        $context = "$this->option.edit.$this->context";
        $task = $this->getTask();

        // Determine the name of the primary key for the data.
        if (empty($key)) {
            $key = $table->getKeyName();
        }

        // To avoid data collisions the urlVar may be different from the primary key.
        if (empty($urlVar)) {
            $urlVar = $key;
        }

        $recordId = JRequest::getInt($urlVar);

        if (!$this->checkEditId($context, $recordId)) {
            // Somehow the person just went to the form and tried to save it. We don't allow that.
            $rdata['success']    = "false";
            $rdata['messages'][] = JText::sprintf('JLIB_APPLICATION_ERROR_UNHELD_ID', $recordId);

            $this->sendResponse($rdata);
        }

        // Populate the row id from the session.
        $data[$key] = $recordId;

        // The save2copy task needs to be handled slightly differently.
        if ($task == 'save2copy')
        {
            // Check-in the original row.
            if ($checkin && $model->checkin($data[$key]) === false)
            {
                // Check-in failed. Go back to the item and display a notice.
                $rdata['success']    = "false";
                $rdata['messages'][] = JText::sprintf('JLIB_APPLICATION_ERROR_CHECKIN_FAILED', $model->getError());

                $this->sendResponse($rdata);
            }

            // Reset the ID and then treat the request as for Apply.
            $data[$key] = 0;
            $task = 'apply';
        }

        // Access check.
        if (!$this->allowSave($data, $key))
        {
            $rdata['success']    = "false";
            $rdata['messages'][] = JText::_('JLIB_APPLICATION_ERROR_SAVE_NOT_PERMITTED');

            $this->sendResponse($rdata);
        }

        // Validate the posted data.
        // Sometimes the form needs some posted data, such as for plugins and modules.
        $form = $model->getForm($data, false);

        if (!$form)
        {
            $rdata['success']    = "false";
            $rdata['messages'][] = $model->getError();

            $this->sendResponse($rdata);
        }

        // Test whether the data is valid.
        $validData = $model->validate($form, $data);

        // Check for validation errors.
        if ($validData === false)
        {
            // Get the validation messages.
            $errors = $model->getErrors();
            $rdata['success'] = "false";

            // Push up to three validation messages out to the user.
            for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i++)
            {
                $rdata['messages'][] = $errors[$i]->getMessage();
            }

            // Save the data in the session.
            $app->setUserState($context . '.data', $data);

            $this->sendResponse($rdata);
        }

        // Attempt to save the data.
        if (!$model->save($validData))
        {
            $rdata['success'] = "false";

            // Save the data in the session.
            $app->setUserState($context . '.data', $validData);

            // Redirect back to the edit screen.
            $rdata['messages'][] = JText::sprintf('JLIB_APPLICATION_ERROR_SAVE_FAILED', $model->getError());

            $this->sendResponse($rdata);
        }

        // Save succeeded, so check-in the record.
        if ($checkin && $model->checkin($validData[$key]) === false)
        {
            // Save the data in the session.
            $app->setUserState($context . '.data', $validData);

            // Check-in failed, so go back to the record and display a notice.
            $rdata['success']    = "false";
            $rdata['messages'][] = JText::sprintf('JLIB_APPLICATION_ERROR_CHECKIN_FAILED', $model->getError());

            $this->sendResponse($rdata);
        }

       $rdata['messages'][] = JText::_(
                ($lang->hasKey($this->text_prefix . ($recordId == 0 && $app->isSite() ? '_SUBMIT' : '') . '_SAVE_SUCCESS')
                    ? $this->text_prefix
                    : 'JLIB_APPLICATION') . ($recordId == 0 && $app->isSite() ? '_SUBMIT' : '') . '_SAVE_SUCCESS'
            );

        // Redirect the user and adjust session state based on the chosen task.
        switch ($task)
        {
            case 'apply':
                // Set the record data in the session.
                $recordId = $model->getState($this->context . '.id');
                $this->holdEditId($context, $recordId);
                $app->setUserState($context . '.data', null);
                $model->checkout($recordId);

                // Redirect back to the edit screen.
                $this->setRedirect(
                    JRoute::_(
                        'index.php?option=' . $this->option . '&view=' . $this->view_item
                        . $this->getRedirectToItemAppend($recordId, $key), false
                    )
                );
                break;

            case 'save2new':
                // Clear the record id and data from the session.
                $this->releaseEditId($context, $recordId);
                $app->setUserState($context . '.data', null);

                // Redirect back to the edit screen.
                $this->setRedirect(
                    JRoute::_(
                        'index.php?option=' . $this->option . '&view=' . $this->view_item
                        . $this->getRedirectToItemAppend(null, $key), false
                    )
                );
                break;

            default:
                // Clear the record id and data from the session.
                $this->releaseEditId($context, $recordId);
                $app->setUserState($context . '.data', null);

                // Redirect to the list screen.
                $this->setRedirect(
                    JRoute::_(
                        'index.php?option=' . $this->option . '&view=' . $this->view_list
                        . $this->getRedirectToListAppend(), false
                    )
                );
                break;
        }

        // Invoke the postSave method to allow for the child class to access the model.
        $this->postSaveHook($model, $validData);

        $this->sendResponse($rdata);
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

        foreach($data AS $key => $value)
        {
            if (is_array($value)) {
                if(count($value) == 0) {
                    unset($data[$key]);
                }
            }
        }

        // Output the JSON data.
        echo json_encode($data);

        jexit();
    }
}
