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


class PFrepoControllerNote extends JControllerForm
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
            $model = $this->getModel('Directory', 'PFrepoModel');
            $item  = $model->getItem($dir_id);

            if (!empty($item)) {
                $access = PFrepoHelper::getActions('directory', $item->id);

                if (!$user->authorise('core.admin')) {
                    if (!in_array($item->access, $user->getAuthorisedViewLevels())) {
                        $this->setError(JText::_('COM_PROJECTFORK_WARNING_DIRECTORY_ACCESS_DENIED'));
                        $access = false;
                    }
                    elseif (!$access->get('core.create')) {
                        $this->setError(JText::_('COM_PROJECTFORK_WARNING_DIRECTORY_CREATE_NOTE_DENIED'));
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
            $access = PFrepoHelper::getActions();

            if (!$access->get('core.create')) {
                $this->setError(JText::_('COM_PROJECTFORK_WARNING_CREATE_NOTE_DENIED'));
                $access = false;
            }
        }

        return ($access && ($dir_id > 0));
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

        return $append;
    }
}
