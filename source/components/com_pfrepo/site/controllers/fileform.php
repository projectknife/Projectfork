<?php
/**
 * @package      Projectfork
 * @subpackage   Repository
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


jimport('joomla.application.component.controllerform');


/**
 * Projectfork File Form Controller
 *
 */
class PFrepoControllerFileForm extends JControllerForm
{
    /**
     * The default item view
     *
     * @var    string
     */
    protected $view_item = 'fileform';

    /**
     * The default list view
     *
     * @var    string
     */
    protected $view_list = 'repository';


    /**
     * Method to get a model object, loading it if required.
     *
     * @param     string    $name      The model name. Optional.
     * @param     string    $prefix    The class prefix. Optional.
     * @param     array     $config    Configuration array for model. Optional.
     *
     * @return    object               The model.
     */
    public function &getModel($name = 'FileForm', $prefix = 'PFrepoModel', $config = array('ignore_request' => true))
    {
        $model = parent::getModel($name, $prefix, $config);

        return $model;
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

        $record_id = JRequest::getInt($urlVar);

        if (!$this->checkEditId($context, $record_id)) {
			// Somehow the person just went to the form and tried to save it. We don't allow that.
			$this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_UNHELD_ID', $record_id));
			$this->setMessage($this->getError(), 'error');

			$this->setRedirect(
				JRoute::_(
					'index.php?option=' . $this->option . '&view=' . $this->view_list
					. $this->getRedirectToListAppend(), false
				)
			);

			return false;
		}

        if (is_array($file_form)) {
            foreach($file_form AS $attr => $field)
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
        }

        // Check for upload errors
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

                if ($layout != 'modal') {
                    $this->setRedirect(
                        JRoute::_(
                            'index.php?option=' . $this->option . '&view=' . $this->view_item
                            . $this->getRedirectToItemAppend($record_id, $urlVar), false
                        )
                    );
                }
                else {
                    $this->setRedirect(
                        JRoute::_(
                            'index.php?option=' . $this->option . '&view=' . $this->view_list
                            . $this->getRedirectToListAppend(), false
                        )
                    );
                }

                return false;
            }
        }

        if (count($files) == 1 && !empty($files[0]['tmp_name'])) {
            $result = $model->upload(array_pop($files), $data['project_id']);

            if (is_array($result)) {
                $data['file'] = $result;
            }
            else {
                $error = $model->getError();
                $this->setError($error);
                $this->setMessage($error, 'error');

                if ($layout != 'modal') {
                    $this->setRedirect(
                        JRoute::_(
                            'index.php?option=' . $this->option . '&view=' . $this->view_item
                            . $this->getRedirectToItemAppend($record_id, $urlVar), false
                        )
                    );
                }
                else {
                    $this->setRedirect(
        				JRoute::_(
        					'index.php?option=' . $this->option . '&view=' . $this->view_list
        					. $this->getRedirectToListAppend(), false
        				)
        			);
                }

                return false;
            }
        }

        if (version_compare(JVERSION, '3.0.0', 'ge')) {
            $this->input->post->set('jform', $data);
        }
        else {
            JRequest::setVar('jform', $data, 'post');
        }

        return parent::save($key, $urlVar);
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
     * @param     int       $id         The primary key id for the item.
     * @param     string    $url_var    The name of the URL variable for the id.
     *
     * @return    string                The arguments to append to the redirect URL.
     */
    protected function getRedirectToItemAppend($id = null, $url_var = 'id')
    {
        // Need to override the parent method completely.
        $tmpl    = JRequest::getCmd('tmpl');
        $layout  = JRequest::getCmd('layout', 'edit');
        $item_id = JRequest::getUInt('Itemid');
        $project = JRequest::getUint('filter_project', 0);
        $parent  = JRequest::getUint('filter_parent_id', 0);
        $return  = $this->getReturnPage($parent, $project);
        $append  = '';

        // Setup redirect info.
        if ($project) $append .= '&filter_project=' . $project;
        if ($parent)  $append .= '&filter_parent_id=' . $parent;
        if ($id)      $append .= '&' . $url_var . '=' . $id;
        if ($item_id) $append .= '&Itemid=' . $item_id;
        if ($layout)  $append .= '&layout=' . $layout;
        if ($tmpl) $append .= '&tmpl=' . $tmpl;
        if ($return)  $append .= '&return='.base64_encode($return);

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
        $func    = JRequest::getCmd('function');
        $append  = '';

        // Setup redirect info.
        if ($project) $append .= '&filter_project=' . $project;
        if ($parent)  $append .= '&filter_parent_id=' . $parent;
        if ($tmpl)    $append .= '&tmpl=' . $tmpl;
        if ($func)    $append .= '&function=' . $func;

        return $append;
    }


    /**
     * Get the return URL.
     * If a "return" variable has been passed in the request
     *
     * @return    string    The return URL.
     */
    protected function getReturnPage($parent, $project = 0)
    {
        $return = JRequest::getVar('return', null, 'default', 'base64');
        $append = '';

        if ($project) $append .= '&filter_project=' . $project;
        if ($parent)  $append .= '&filter_parent_id=' . $parent;

        if (empty($return) || !JUri::isInternal(base64_decode($return))) {
            return JRoute::_('index.php?option=com_pfrepo&view=' . $this->view_list . $append, false);
        }
        else {
            return base64_decode($return);
        }
    }
}
