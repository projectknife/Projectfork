<?php
/**
 * @package      Projectfork
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


// Base this model on the backend version.
require_once JPATH_ADMINISTRATOR . '/components/com_projectfork/models/tasklist.php';


/**
 * Projectfork Component Task List Form Model
 *
 */
class ProjectforkModelTasklistForm extends ProjectforkModelTasklist
{
    /**
     * Method to get item data.
     *
     * @param     integer    The id of the item.
     * @return    mixed      Item data object on success, false on failure.
     */
    public function getItem($id = null)
    {
        // Initialise variables.
        $id = (int) (!empty($id)) ? $id : $this->getState('tasklist.id');

        // Get a row instance.
        $table = $this->getTable();

        // Attempt to load the row.
        $return = $table->load($id);

        // Check for a table object error.
        if ($return === false && $table->getError()) {
            $this->setError($table->getError());
            return false;
        }

        $properties = $table->getProperties(1);
        $value = JArrayHelper::toObject($properties, 'JObject');

        // Convert attrib field to Registry.
        $value->params = new JRegistry;
        $value->params->loadString($value->attribs);

        // Compute selected asset permissions.
        $uid    = JFactory::getUser()->get('id');
        $access = ProjectforkHelperAccess::getActions('tasklist', $value->id);

        // Check general edit permission first.
        if ($access->get('tasklist.edit')) {
            $value->params->set('access-edit', true);
        }
        // Now check if edit.own is available.
        elseif (!empty($uid) && $access->get('tasklist.edit.own')) {
            // Check for a valid user and that they are the owner.
            if ($uid == $value->created_by) {
                $value->params->set('access-edit', true);
            }
        }

        // Check edit state permission.
        if ($id) {
            // Existing item
            $value->params->set('access-change', $access->get('tasklist.edit.state'));
        }
        else {
            // New item
            $access = ProjectforkHelperAccess::getActions();
            $value->params->set('access-change', $access->get('tasklist.edit.state'));
        }

        return $value;
    }


    /**
     * Get the return URL.
     *
     * @return    string    The return URL.
     */
    public function getReturnPage()
    {
        return base64_encode($this->getState('return_page'));
    }


    /**
     * Method to get the data that should be injected in the form.
     *
     * @return    mixed    The data for the form.
     */
    protected function loadFormData()
    {
        // Check the session for previously entered form data.
        $data = JFactory::getApplication()->getUserState('com_projectfork.edit.tasklistform.data', array());

        if (empty($data)) $data = $this->getItem();

        return $data;
    }


    /**
     * Method to auto-populate the model state.
     * Note. Calling getState in this method will result in recursion.
     *
     */
    protected function populateState()
    {
        $app = JFactory::getApplication();

        // Load state from the request.
        $pk = JRequest::getInt('id');
        $this->setState('tasklist.id', $pk);

        $return = JRequest::getVar('return', null, 'default', 'base64');
        $this->setState('return_page', base64_decode($return));

        // Load the parameters.
        $params = $app->getParams();
        $this->setState('params', $params);

        $this->setState('layout', JRequest::getCmd('layout'));
    }
}
