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
 * Projectfork Note Form Controller
 *
 */
class PFrepoControllerNoteForm extends JControllerForm
{
    /**
     * The default item view
     *
     * @var    string
     */
    protected $view_item = 'noteform';

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
    public function &getModel($name = 'NoteForm', $prefix = 'PFrepoModel', $config = array('ignore_request' => true))
    {
        $model = parent::getModel($name, $prefix, $config);

        return $model;
    }


    /**
     * Method to add a new record.
     *
     * @return    boolean    True if the item can be added, false if not.
     */
    public function add()
    {
        if (!parent::add()) {
            // Redirect to the return page.
            $this->setRedirect($this->getReturnPage());
            return false;
        }

        return true;
    }


    /**
     * Method to cancel an edit.
     *
     * @param     string     $key    The name of the primary key of the URL variable.
     *
     * @return    boolean            True if access level checks pass, false otherwise.
     */
    public function cancel($key = 'id')
    {
        $result = parent::cancel($key);

        // Redirect to the return page.
        $this->setRedirect($this->getReturnPage());

        return $result;
    }


    /**
     * Method to save a record.
     *
     * @param     string     $key        The name of the primary key of the URL variable.
     * @param     string     $url_var    The name of the URL variable if different from the primary key.
     *
     * @return    boolean                True if successful, false otherwise.
     */
    public function save($key = null, $url_var = 'id')
    {
        $result  = parent::save($key, $url_var);
        $project = JRequest::getUint('filter_project', 0);
        $parent  = JRequest::getUint('filter_parent_id', 0);

        // If ok, redirect to the return page.
        if ($result) $this->setRedirect($this->getReturnPage($parent, $project));

        return $result;
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
        // Get form input
        $dir = isset($data['parent_id'])  ? (int) $data['parent_id']  : JRequest::getUint('filter_parent_id');

        $user   = JFactory::getUser();
        $asset  = 'com_pfrepo.directory.' . $dir;
        $access = true;

        // Deny if no parent directory is given
        if (!$dir) {
            $this->setError(JText::_('COM_PROJECTFORK_WARNING_DIRECTORY_NOT_FOUND'));
            return false;
        }

        // Check if the user has viewing access when not a super admin
        if (!$user->authorise('core.admin')) {
            $db    = JFactory::getDbo();
            $query = $db->getQuery(true);

            $query->select('access')
                  ->from('#__pf_repo_dirs')
                  ->where('id = ' . $dir);

            $db->setQuery($query);
            $lvl = $db->loadResult();

            $access = in_array($lvl, $user->getAuthorisedViewLevels());
        }

        return ($user->authorise('core.create', $asset) && $access);
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
                  ->from('#__pf_repo_notes')
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
        if ($tmpl) $append .= '&tmpl=' . $tmpl;

        $append .= '&layout=edit';
        if ($project) $append .= '&filter_project=' . $project;
        if ($parent)  $append .= '&filter_parent_id=' . $parent;
        if ($id)      $append .= '&' . $url_var . '=' . $id;
        if ($item_id) $append .= '&Itemid=' . $item_id;
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
        $append  = '';


        // Setup redirect info.
        if ($project) $append .= '&filter_project=' . $project;
        if ($parent)  $append .= '&filter_parent_id=' . $parent;
        if ($tmpl)    $append .= '&tmpl=' . $tmpl;

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
        if ($parent)   $append .= '&filter_parent_id=' . $parent;

        if (empty($return) || !JUri::isInternal(base64_decode($return))) {
            return JRoute::_('index.php?option=com_pfrepo&view=' . $this->view_list . $append, false);
        }
        else {
            return base64_decode($return);
        }
    }


    /**
     * Function that allows child controller access to model data after the data has been saved.
     *
     * @param     jmodel    $model    The data model object.
     * @param     array     $data     The validated data.
     *
     * @return    void
     */
    protected function postSaveHook(&$model, $data)
    {
        $task = $this->getTask();

        if ($task == 'save') {
            $this->setRedirect(JRoute::_('index.php?option=com_pfrepo&view=' . $this->view_list, false));
        }
    }
}
