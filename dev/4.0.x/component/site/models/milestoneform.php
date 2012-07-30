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
require_once JPATH_ADMINISTRATOR.'/components/com_projectfork/models/milestone.php';


/**
 * Projectfork Component Milestone Form Model
 *
 */
class ProjectforkModelMilestoneForm extends ProjectforkModelMilestone
{
    /**
     * Method to get item data.
     *
     * @param     integer    $id       The id of the item.
     *
     * @return    mixed      $value    Item data object on success, false on failure.
     */
    public function getItem($id = null)
    {
        // Initialise variables.
        $id = (int) (!empty($id)) ? $id : $this->getState('milestone.id');

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
        $uid    =  JFactory::getUser()->get('id');
        $access = ProjectforkHelper::getActions('milestone', $value->id);

        // Check general edit permission first.
        if ($access->get('milestone.edit')) {
            $value->params->set('access-edit', true);
        }
        elseif (!empty($uid) && $access->get('milestone.edit.own')) {
            // Now check if edit.own is available.
            // Check for a valid user and that they are the owner.
            if ($uid == $value->created_by) {
                $value->params->set('access-edit', true);
            }
        }

        // Check edit state permission.
        if ($id) {
            // Existing item
            $value->params->set('access-change', $access->get('milestone.edit.state'));
        }
        else {
            // New item
            $access = ProjectforkHelper::getActions();
            $value->params->set('access-change', $access->get('milestone.edit.state'));
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
     * Method to auto-populate the model state.
     * Note. Calling getState in this method will result in recursion.
     *
     * @return    void    
     */
    protected function populateState()
    {
        $app = JFactory::getApplication();

        // Load state from the request.
        $pk = JRequest::getInt('id');
        $this->setState('milestone.id', $pk);

        $return = JRequest::getVar('return', null, 'default', 'base64');
        $this->setState('return_page', base64_decode($return));

        // Load the parameters.
        $params = $app->getParams();
        $this->setState('params', $params);

        $this->setState('layout', JRequest::getCmd('layout'));
    }


    /**
     * Method to get the data that should be injected in the form.
     *
     * @return    mixed    $data    The data for the form.
     */
    protected function loadFormData()
    {
        // Check the session for previously entered form data.
        $data = JFactory::getApplication()->getUserState('com_projectfork.edit.milestoneform.data', array());

        if (empty($data)) $data = $this->getItem();

        return $data;
    }
}
