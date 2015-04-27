<?php
/**
 * @package      pkg_projectfork
 * @subpackage   com_pfrepo
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2013 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


jimport('joomla.application.component.controllerform');


/**
 * Repository File controller class
 *
 */
class PFrepoControllerFile extends JControllerForm
{
    /**
     * The URL view list variable.
     *
     * @var    string
     */
    protected $view_list = 'repository';


    public function download()
    {
        $id  = JRequest::getUInt('id');
        $rev = JRequest::getUInt('rev');

        $link_base = 'index.php?option=' . $this->option . '&view=';
        $link_list = $link_base . $this->view_list . $this->getRedirectToListAppend();

        $user   = JFactory::getUser();
        $levels = $user->getAuthorisedViewLevels();
        $admin  = $user->authorise('core.admin', 'com_pfrepo');

        $file_model = $this->getModel();
        $file       = $file_model->getItem($id);

        if (empty($id) || !$file || empty($file->id)) {
            $this->setError(JText::_('COM_PROJECTFORK_ERROR_FILE_NOT_FOUND'));
            $this->setMessage($this->getError(), 'error');
            $this->setRedirect(JRoute::_($link_list, false));
            return false;
        }

        // Check file access
        if (!$admin && !in_array($file->access, $levels)) {
            $this->setError(JText::_('JERROR_ALERTNOAUTHOR'));
            $this->setMessage($this->getError(), 'error');
            $this->setRedirect(JRoute::_($link_list, false));
            return false;
        }

        if ($rev) {
            $rev_model = $this->getModel('FileRevision');
            $file_rev  = $rev_model->getItem($rev);

            if (!$file_rev || empty($file_rev->id)) {
                $this->setError(JText::_('COM_PROJECTFORK_ERROR_FILE_NOT_FOUND'));
                $this->setMessage($this->getError(), 'error');
                $this->setRedirect(JRoute::_($link_list, false));
                return false;
            }

            // Check access
            if ($file_rev->parent_id != $file->id) {
                $this->setError(JText::_('JERROR_ALERTNOAUTHOR'));
                $this->setMessage($this->getError(), 'error');
                $this->setRedirect(JRoute::_($link_list, false));
                return false;
            }

            $filepath = PFrepoHelper::getBasePath($file->project_id) . '/_revs/file_' . $file->id;
            $filename = $file_rev->file_name;
        }
        else {
            $filepath = PFrepoHelper::getFilePath($file->file_name, $file->dir_id);
            $filename = $file->file_name;
        }

        // Check if the file exists
        if (empty($filepath) || !JFile::exists($filepath . '/' . $filename)) {
            $this->setError(JText::_('COM_PROJECTFORK_ERROR_FILE_NOT_FOUND'));
            $this->setMessage($this->getError(), 'error');
            $this->setRedirect(JRoute::_($link_list, false));
            return false;
        }

        if (headers_sent($f, $line)) {
            $this->setError(JText::sprintf('COM_PROJECTFORK_WARNING_FILE_DL_ERROR_HEADERS_SENT', $f, $line));
            $this->setMessage($this->getError(), 'error');
            $this->setRedirect(JRoute::_($link_list, false));
            return false;
        }

        while (ob_get_level())
        {
            ob_end_clean();
        }

        header("Content-Type: APPLICATION/OCTET-STREAM");
        header("Content-Length: " . filesize($filepath . '/' . $filename));
        header("Content-Disposition: attachment; filename=\"" . $filename . "\";");
        header("Content-Transfer-Encoding: Binary");

        if (function_exists('readfile')) {
            readfile($filepath . '/' . $filename);
        }
        else {
            echo file_get_contents($filepath . '/' . $filename);
        }

        jexit();
    }



    /**
     * Method to save a record.
     *
     * @param     string     $key       The name of the primary key of the URL variable.
     * @param     string     $urlVar    The name of the URL variable if different from the primary key (sometimes required to avoid router collisions).
     *
     * @return    boolean               True if successful, false otherwise.
     */
    public function save($key = 'id', $urlVar = null)
    {
        // Check for request forgeries.
        JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

        // Initialise variables.
        $model     = $this->getModel();
        $data      = JRequest::getVar('jform', array(), 'post', 'array');
        $file_form = JRequest::getVar('jform', '', 'files', 'array');
        $context   = $this->option . ".edit." . $this->context;
        $layout    = JRequest::getVar('layout');
        $files     = array();

        if (empty($urlVar)) $urlVar = $key;

        // Setup redirect links
        $record_id = JRequest::getInt($urlVar);
        $link_base = 'index.php?option=' . $this->option . '&view=';
        $link_list = $link_base . $this->view_list . $this->getRedirectToListAppend();
        $link_item = $link_base . $this->view_item . $this->getRedirectToItemAppend($record_id, $urlVar);

        // Get project id from directory if missing
        if ((!isset($data['project_id']) || empty($data['project_id'])) && isset($data['dir_id'])) {
            $data['project_id'] = PFrepoHelper::getProjectFromDir($data['dir_id']);
        }

        // Check edit id
        if (!$this->checkEditId($context, $record_id)) {
            // Somehow the person just went to the form and tried to save it. We don't allow that.
            $this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_UNHELD_ID', $record_id));
            $this->setMessage($this->getError(), 'error');

            $this->setRedirect(JRoute::_(($layout != 'modal' ? $link_item : $link_list), false));

            return false;
        }

        // Get file info
        $files = $this->getFormFiles($file_form);

        // Check for upload errors
        if (!$this->checkFileError($files, $record_id)) {
            $this->setRedirect(JRoute::_(($layout != 'modal' ? $link_item : $link_list), false));
            return false;
        }

        // Upload file if we have any
        if (count($files) && !empty($files[0]['tmp_name'])) {
            $file = $files[0];

            if ($record_id) {
                // File extension must be the same as the original
                if (!$this->checkFileExtension($record_id, $file['name'])) {
                    $this->setError(JText::_('COM_PROJECTFORK_WARNING_FILE_UPLOAD_ERROR_10'));
                    $this->setMessage($this->getError(), 'error');

                    $this->setRedirect(JRoute::_(($layout != 'modal' ? $link_item : $link_list), false));

                    return false;
                }
            }

            // Upload the file
            $result = $model->upload($file, $data['dir_id'], false, $record_id);

            if (is_array($result)) {
                $data['file'] = $result;
            }
            else {
                $error = $model->getError();
                $this->setError($error);
                $this->setMessage($error, 'error');

                $this->setRedirect(JRoute::_(($layout != 'modal' ? $link_item : $link_list), false));
                return false;
            }
        }

        // Inject file info into the form post data
        if (version_compare(JVERSION, '3.0.0', 'ge')) {
            $this->input->post->set('jform', $data);
        }
        else {
            JRequest::setVar('jform', $data, 'post');
        }

        // Store data
        return parent::save($key, $urlVar);
    }


    /**
     * Method the get the file info coming from a form
     *
     * @param     array    $data     The form data
     *
     * @return    array    $files    The file data
     */
    protected function getFormFiles($data)
    {
        $files = array();

        if (!is_array($data)) return $files;

        foreach($data AS $attr => $field)
        {
            $count = count($field);
            $i     = 0;

            while($count > $i)
            {
                foreach($field AS $name => $value)
                {
                    $files[$i][$attr] = $value;
                }

                $i++;
            }
        }

        return $files;
    }


    /**
     * Method to check for upload errors
     *
     * @param     array      $files    The files to check
     *
     * @return    boolean              True if no error
     */
    protected function checkFileError(&$files, $record_id = 0)
    {
        foreach ($files AS &$file)
        {
            // Uploading a file is not required when updating an existing record
            if ($file['error'] == 4 && $record_id > 0) {
                $file['error'] = 0;
            }

            if ($file['error']) {
                $error = PFrepoHelper::getFileErrorMsg($file['error'], $file['name']);
                $this->setError($error);
                $this->setMessage($error, 'error');

                return false;
            }
        }

        return true;
    }


    /**
     * Method to check if the file extension is the same the original
     *
     * @param integer $id The file id
     * @param string $file The name of the file to upload
     *
     * @return boolean True if they are the same
     */
    protected function checkFileExtension($id, $file)
    {
        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->select('file_extension')
              ->from('#__pf_repo_files')
              ->where('id = ' . (int) $id);

        $db->setQuery($query);
        $original_ext = $db->loadResult();

        return (JFile::getExt($file) == $original_ext);
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
        $project = JArrayHelper::getValue($data, 'project_id', JRequest::getUInt('filter_project'), 'int');
        $dir_id  = JArrayHelper::getValue($data, 'dir_id', JRequest::getUInt('filter_parent_id'), 'int');

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
        $user  = JFactory::getUser();
        $uid   = $user->get('id');
        $id    = (int) isset($data[$key]) ? $data[$key] : 0;
        $owner = (int) isset($data['created_by']) ? $data['created_by'] : 0;

        // Check general edit permission first.
        if ($user->authorise('core.edit', 'com_pfrepo.file.' . $id)) {
            return true;
        }

        // Fallback on edit.own.
        if ($user->authorise('core.edit.own', 'com_pfrepo.file.' . $id)) {
            // Now test the owner is the user.
            if (!$owner && $id) {
                $record = $this->getModel()->getItem($id);

                if (empty($record)) return false;

                $owner = $record->created_by;
            }

            if ($owner == $uid) return true;
        }

        // Fall back to the component permissions.
        return parent::allowEdit($data, $key);
    }


    /**
     * Gets the URL arguments to append to an item redirect.
     *
     * @param     integer    $id         The primary key id for the item.
     * @param     string     $url_var    The name of the URL variable for the id.
     *
     * @return    string                 The arguments to append to the redirect URL.
     */
    protected function getRedirectToItemAppend($id = null, $url_var = 'id')
    {
        $tmpl    = JRequest::getCmd('tmpl');
        $layout  = JRequest::getCmd('layout', 'edit');
        $project = JRequest::getUint('filter_project', 0);
        $parent  = JRequest::getUint('filter_parent_id', 0);
        $append  = '';

        // Setup redirect info.
        if ($project) $append .= '&filter_project=' . $project;
        if ($parent)  $append .= '&filter_parent_id=' . $parent;
        if ($id)      $append .= '&' . $url_var . '=' . $id;
        if ($layout)  $append .= '&layout=' . $layout;
        if ($tmpl)    $append .= '&tmpl=' . $tmpl;

        return $append;
    }


    /**
     * Gets the URL arguments to append to a list redirect.
     *
     * @return    string    The arguments to append to the redirect URL.
     */
    protected function getRedirectToListAppend()
    {
        $tmpl    = JRequest::getCmd('tmpl');
        $project = JRequest::getUint('filter_project');
        $parent  = JRequest::getUint('filter_parent_id');
        $layout  = JRequest::getCmd('layout');
        $func    = JRequest::getCmd('function');
        $append  = '';

        // Setup redirect info.
        if ($project) $append .= '&filter_project=' . $project;
        if ($parent)  $append .= '&filter_parent_id=' . $parent;
        if ($tmpl)    $append .= '&tmpl=' . $tmpl;
        if ($layout)  $append .= '&layout=' . $layout;
        if ($func)    $append .= '&function=' . $func;

        return $append;
    }
}
