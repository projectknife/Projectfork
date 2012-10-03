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


class ProjectforkControllerFile extends JControllerForm
{
    /**
     * The URL view list variable.
     *
     * @var    string
     */
    protected $view_list = 'repository';


    /**
     * Class constructor.
     *
     * @param    array    $config    A named array of configuration variables
     */
    public function __construct($config = array())
    {
        parent::__construct($config);
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

        if (count($files) == 1) {
            $result = $model->upload(array_pop($files), $data['project_id']);

            if (is_array($result)) {
                $keys = array_keys($result);

                foreach($keys AS $k)
                {
                    $data[$k] = $result[$k];
                }
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

        JRequest::setVar('jform', $data, 'post');

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
        $user = JFactory::getUser();

        $dir_id = (int) JRequest::getUInt('filter_parent_id', 0);
        $access = true;

        if (isset($data['dir_id'])) {
            $dir_id = (int) $data['dir_id'];
        }

        // Verify directory access
        if ($dir_id) {
            $model = $this->getModel('Directory', 'ProjectforkModel');
            $item  = $model->getItem($dir_id);

            if (!empty($item)) {
                $access = ProjectforkHelperAccess::getActions('directory', $item->id);

                if (!$user->authorise('core.admin')) {
                    if (!in_array($item->access, $user->getAuthorisedViewLevels())) {
                        $this->setError(JText::_('COM_PROJECTFORK_WARNING_DIRECTORY_ACCESS_DENIED'));
                        $access = false;
                    }
                    elseif (!$access->get('create.file')) {
                        $this->setError(JText::_('COM_PROJECTFORK_WARNING_DIRECTORY_CREATE_FILE_DENIED'));
                        $access = false;
                    }
                }
            }
            else {
                $this->setError(JText::_('COM_PROJECTFORK_WARNING_DIRECTORY_NOT_FOUND'));
                $access = false;
            }
        }
        else {
            $access = ProjectforkHelperAccess::getActions('file');

            if (!$access->get('create.file')) {
                $this->setError(JText::_('COM_PROJECTFORK_WARNING_CREATE_FILE_DENIED'));
                $access = false;
            }
        }

        return ($access && ($dir_id > 1));
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
        if ($tmpl) {
            $append .= '&tmpl=' . $tmpl;
        }

        if ($layout) {
            $append .= '&layout=' . $layout;
        }

        if ($id) {
            $append .= '&' . $url_var . '=' . $id;
        }

        if ($project) {
            $append .= '&filter_project=' . $project;
        }

        if ($parent) {
            $append .= '&filter_parent_id=' . $parent;
        }

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
        if ($project) {
            $append .= '&filter_project=' . $project;
        }

        if ($parent) {
            $append .= '&filter_parent_id=' . $parent;
        }

        if ($tmpl) {
            $append .= '&tmpl=' . $tmpl;
        }

        if ($layout) {
            $append .= '&layout=' . $layout;
        }

        if ($func) {
            $append .= '&function=' . $func;
        }

        return $append;
    }
}
