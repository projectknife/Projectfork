<?php
/**
* @package      pkg_projectfork
* @subpackage   com_pfrepo
*
* @author       Tobias Kuhn (eaxs)
* @copyright    Copyright (C) 2006-2013 Tobias Kuhn. All rights reserved.
* @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
**/

defined('_JEXEC') or die();


jimport('projectfork.controller.form.json');


/**
 * Projectfork File Form JSON Controller
 *
 */
class PFrepoControllerFileForm extends PFControllerFormJson
{
    public function save()
    {
        $rdata = array();
        $rdata['success']  = true;
        $rdata['messages'] = array();
        $rdata['data']     = array();
        $rdata['file']     = '';

        $files_data = JRequest::getVar('qqfile', null, 'files');
        $get_data   = JRequest::getVar('qqfile', null, 'get');
        $dir        = JRequest::getUInt('filter_parent_id', JRequest::getUInt('dir_id'));
        $project    = JRequest::getUInt('filter_project', PFApplicationHelper::getActiveProjectId());
        $method     = null;

        // Determine the upload method
        if ($files_data) {
            $method = 'form';
            $file   = $files_data;
        }
        elseif ($get_data) {
            $method = 'xhr';
            $file   = array('name' => $get_data, 'tmp_name' => $get_data, 'error' => 0);
        }
        else {
            $rdata['success'] = false;
            $rdata['messages'][] = JText::_('COM_PROJECTFORK_WARNING_FILE_UPLOAD_ERROR_4');

            $this->sendResponse($rdata);
        }

        // Access check.
        if (!$this->allowSave($d = array())) {
            $rdata['success'] = false;
            $rdata['messages'][] = JText::_('JLIB_APPLICATION_ERROR_SAVE_NOT_PERMITTED');

            $this->sendResponse($rdata);
        }

        // Check for upload error
        if ($files_data['error']) {
            $error = PFrepoHelper::getFileErrorMsg($files_data['error'], $files_data['name']);

            $rdata['success'] = false;
            $rdata['messages'][] = $error;

            $this->sendResponse($rdata);
        }

        $model  = $this->getModel();
        $result = $model->upload($file, $project, ($method == 'xhr' ? true : false));

        if (!$result) {
            $rdata['success'] = false;
            $rdata['messages'][] = $model->getError();

            $this->sendResponse($rdata);
        }

        // Prepare data for saving
        $data = array();
        $data['project_id'] = $project;
        $data['dir_id']     = $dir;
        $data['file']       = $result;
        $data['title']      = $result['name'];

        if (!$model->save($data)) {
            $rdata['success'] = false;
            $rdata['messages'][] = $model->getError();

            $this->sendResponse($rdata);
        }

        $this->sendResponse($rdata);
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
        $user    = JFactory::getUser();
        $project = JArrayHelper::getValue($data, 'project_id', JRequest::getInt('filter_project'), 'int');
        $dir_id  = JArrayHelper::getValue($data, 'dir_id', JRequest::getInt('filter_parent_id'), 'int');

        // Check general access
        if (!$user->authorise('core.create', 'com_pfrepo')) {
            $this->setError(JText::_('COM_PROJECTFORK_WARNING_CREATE_FILE_DENIED'));
            return false;
        }

        // Validate directory access
        $model = $this->getModel('Directory', 'PFrepoModel');
        $item  = $model->getItem($dir_id);

        if ($item == false || empty($item->id) || $dir_id <= 1) {
            $this->setError(JText::_('COM_PROJECTFORK_WARNING_DIRECTORY_NOT_FOUND'));
            return false;
        }

        $access = PFrepoHelper::getActions('directory', $item->id);

        if (!$user->authorise('core.admin')) {
            if (!in_array($item->access, $user->getAuthorisedViewLevels())) {
                $this->setError(JText::_('COM_PROJECTFORK_WARNING_DIRECTORY_ACCESS_DENIED'));
                return false;
            }
            elseif (!$access->get('core.create')) {
                $this->setError(JText::_('COM_PROJECTFORK_WARNING_DIRECTORY_CREATE_FILE_DENIED'));
                return false;
            }
        }

        return true;
    }
}
