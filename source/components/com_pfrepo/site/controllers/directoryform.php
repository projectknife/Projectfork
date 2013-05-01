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
 * Projectfork Directory Form Controller
 *
 */
class PFrepoControllerDirectoryForm extends JControllerForm
{
    /**
     * The default item view
     *
     * @var    string
     */
    protected $view_item = 'directoryform';

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
    public function &getModel($name = 'DirectoryForm', $prefix = '', $config = array('ignore_request' => true))
    {
        $model = parent::getModel($name, $prefix, $config);

        return $model;
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
        $user  = JFactory::getUser();
        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);

        $access  = true;
        $levels  = $user->getAuthorisedViewLevels();
        $dir     = isset($data['parent_id'])  ? (int) $data['parent_id'] : 0;
        $project = isset($data['project_id']) ? (int) $data['project_id'] : 0;

        if (empty($data)) {
            $dir     = JRequest::getUint('filter_parent_id');
            $project = JRequest::getUint('filter_project');

            // Do not allow if no dir or project is given
            if ($dir == 0 || $project == 0) {
                return false;
            }
        }

        // Check if the user has access to the parent directory
        if (!$user->authorise('core.admin', 'com_pfrepo')) {
            if ($dir) {
                $query->select('access')
                      ->from('#__pf_repo_dirs')
                      ->where('id = ' . $dir);

                $db->setQuery($query);
                $access = (in_array((int) $db->loadResult(), $levels) && $user->authorise('core.create', 'com_pfrepo.directory.' . $dir));
            }
        }

        // Check if the user has access to the project
        if (!$user->authorise('core.admin', 'com_pfprojects')) {
            if ($project && $access) {
                $query->clear();
                $query->select('access')
                      ->from('#__pf_projects')
                      ->where('id = ' . $db->quote((int) $project));

                $db->setQuery($query);
                $access = in_array((int) $db->loadResult(), $levels);
            }
        }

        return ($user->authorise('core.create', 'com_pfrepo') && $access);
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
        // Initialise variables.
        $id     = (int) isset($data[$key]) ? $data[$key] : 0;
        $uid    = JFactory::getUser()->get('id');
        $access = PFrepoHelper::getActions('directory', $id);

        // Check general edit permission first.
        if ($access->get('core.edit')) {
            return true;
        }

        // Fallback on edit.own.
        // First test if the permission is available.
        if ($access->get('core.edit.own')) {
            // Now test the owner is the user.
            $owner = (int) isset($data['created_by']) ? $data['created_by'] : 0;

            if (empty($owner) && $id) {
                // Need to do a lookup from the model.
                $record = $this->getModel()->getItem($id);

                if (empty($record)) return false;

                $owner = $record->created_by;
            }

            // If the owner matches 'me' then do the test.
            if ($owner == $uid) return true;
        }

        // Since there is no asset tracking, revert to the component permissions.
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
        $append  = '&layout=edit';

        // Setup redirect info.
        if ($project) $append .= '&filter_project=' . $project;
        if ($parent)  $append .= '&filter_parent_id=' . $parent;
        if ($id)      $append .= '&' . $url_var . '=' . $id;
        if ($item_id) $append .= '&Itemid=' . $item_id;
        if ($tmpl)    $append .= '&tmpl=' . $tmpl;
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
        // Need to override the parent method completely.
        $tmpl    = JRequest::getCmd('tmpl');
        $project = JRequest::getUint('filter_project', 0);
        $parent  = JRequest::getUint('filter_parent_id', 0);
        $return  = $this->getReturnPage();
        $append  = '';

        // Setup redirect info.
        if ($project) $append .= '&filter_project=' . $project;
        if ($parent)  $append .= '&filter_parent_id=' . $parent;
        if ($tmpl)    $append .= '&tmpl=' . $tmpl;
        if ($return)  $append .= '&return=' . $return;

        return $append;
    }


    /**
     * Get the return URL.
     * If a "return" variable has been passed in the request
     *
     * @return    string    The return URL.
     */
    protected function getReturnPage()
    {
        $return  = JRequest::getVar('return', null, 'default', 'base64');
        $parent  = JRequest::getUint('filter_parent_id');
        $project = JRequest::getUint('filter_project');
        $append  = '';

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
