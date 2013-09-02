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

jimport('projectfork.framework');
jimport('projectfork.controller.form.json');


/**
 * Projectfork File Form JSON Controller
 *
 */
class PFrepoControllerFileForm extends PFControllerFormJson
{
    public function save($key = null, $urlVar = null)
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
        if (!$this->allowSave($d = array()) || defined('PFDEMO')) {
            $rdata['success'] = false;
            $rdata['messages'][] = JText::_('JLIB_APPLICATION_ERROR_SAVE_NOT_PERMITTED');

            $this->sendResponse($rdata);
        }

        // Check for upload error
        if ($file['error']) {
            $error = PFrepoHelper::getFileErrorMsg($file['error'], $file['name']);

            $rdata['success'] = false;
            $rdata['messages'][] = $error;

            $this->sendResponse($rdata);
        }

        // Find file with the same name in the same dir
        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);
        $name  = JFile::makeSafe($file['name']);

        $query->select('id')
              ->from('#__pf_repo_files')
              ->where('dir_id = ' . (int) $dir)
              ->where('file_name = ' . $db->quote($name));

        $db->setQuery($query, 0, 1);
        $parent_id = (int) $db->loadResult();

        $model  = $this->getModel();
        $result = $model->upload($file, $dir, ($method == 'xhr' ? true : false), $parent_id);

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

        if ($parent_id) {
            $data['id'] = $parent_id;
        }

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

        // Demo mode check
        if (defined('PFDEMO')) {
            $this->setError(JText::_('COM_PROJECTFORK_WARNING_CREATE_FILE_DENIED'));
            return false;
        }

        // Make sure the directory exists
        $model    = $this->getModel('Directory', 'PFrepoModel');
        $item_dir = $model->getItem($dir_id);

        if (empty($item_dir) || !$dir_id) {
            $this->setError(JText::_('COM_PROJECTFORK_WARNING_DIRECTORY_NOT_FOUND'));
        }

        // Check super admin permission
        if ($user->authorise('core.admin')) {
            return true;
        }

        // Check if the user has viewing access when not a super admin
        if (!in_array($item_dir->access, $user->getAuthorisedViewLevels())) {
            $this->setError(JText::_('COM_PROJECTFORK_WARNING_DIRECTORY_ACCESS_DENIED'));
            return false;
        }

        // Check create permission
        if (!$user->authorise('core.create', 'com_pfrepo.directory.' . $dir_id)) {
            $this->setError(JText::_('COM_PROJECTFORK_WARNING_DIRECTORY_CREATE_FILE_DENIED'));
            return false;
        }

        return true;
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
        // Get form input
        $id = (int) (isset($data[$key]) ? $data[$key] : 0);

        $user   = JFactory::getUser();
        $uid    = JFactory::getUser()->get('id');
        $asset  = 'com_pfrepo.file.' . $id;
        $access = true;

        // Check if the user has viewing access when not a super admin
        if (!$user->authorise('core.admin')) {
            $db    = JFactory::getDbo();
            $query = $db->getQuery(true);

            $query->select('access')
                  ->from('#__pf_repo_files')
                  ->where('id = ' . $id);

            $db->setQuery($query);
            $lvl = $db->loadResult();

            if (!in_array($lvl, $user->getAuthorisedViewLevels())) {
                return false;
            }
        }

        // Check general edit permission first.
        if ($user->authorise('core.edit', $asset)) {
            return true;
        }

        // Fallback on edit.own.
        // First test if the permission is available.
        if (!$user->authorise('core.edit.own', $asset)) {
            return false;
        }

        // Load the item
        $record = $this->getModel()->getItem($id);

        // Abort if not found
        if (empty($record)) return false;

        // Now test the owner is the user.
        $owner = (int) isset($data['created_by']) ? (int) $data['created_by'] : $record->created_by;

        // If the owner matches 'me' then do the test.
        return ($owner == $uid && $uid > 0);
    }
}
