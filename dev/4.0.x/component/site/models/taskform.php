<?php
/**
* @package   Projectfork
* @copyright Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
*
* This file is part of Projectfork.
*
* Projectfork is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
*
* Projectfork is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with Projectfork. If not, see <http://www.gnu.org/licenses/gpl.html>.
**/

defined( '_JEXEC' ) or die( 'Restricted access' );

// Base this model on the backend version.
require_once JPATH_ADMINISTRATOR.DS.'components'.DS.'com_projectfork'.DS.'models'.DS.'task.php';


/**
 * Projectfork Component Task Form Model
 *
 */
class ProjectforkModelTaskForm extends ProjectforkModelTask
{
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
		$this->setState('task.id', $pk);

		$return = JRequest::getVar('return', null, 'default', 'base64');
		$this->setState('return_page', base64_decode($return));

		// Load the parameters.
		$params	= $app->getParams();
		$this->setState('params', $params);

		$this->setState('layout', JRequest::getCmd('layout'));
	}


	/**
	 * Method to get item data.
	 *
	 * @param	  integer	The id of the item.
	 * @return    mixed	    Item data object on success, false on failure.
	 */
	public function getItem($itemId = null)
	{
		// Initialise variables.
		$itemId = (int) (!empty($itemId)) ? $itemId : $this->getState('task.id');

		// Get a row instance.
		$table = $this->getTable();

		// Attempt to load the row.
		$return = $table->load($itemId);

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

        // Get assigned users
        $value->users = $this->getUsers($itemId);

		// Compute selected asset permissions.
		$user	= JFactory::getUser();
		$userId	= $user->get('id');
		$asset	= 'com_projectfork.task.'.$value->id;

		// Check general edit permission first.
		if ($user->authorise('core.edit', $asset) || $user->authorise('task.edit', $asset)) {
			$value->params->set('access-edit', true);
		}
		// Now check if edit.own is available.
		elseif (!empty($userId) && ($user->authorise('core.edit.own', $asset) || $user->authorise('task.edit.own', $asset))) {
			// Check for a valid user and that they are the owner.
			if ($userId == $value->created_by) {
				$value->params->set('access-edit', true);
			}
		}

		// Check edit state permission.
		if ($itemId) {
			// Existing item
			$value->params->set('access-change', ($user->authorise('core.edit.state', $asset) || $user->authorise('task.edit.state', $asset)));
		}
		else {
		    // New item
			$value->params->set('access-change', ($user->authorise('core.edit.state', 'com_projectfork') ||
                                                  $user->authorise('task.edit.state', 'com_projectfork')));
		}

		return $value;
	}


    /**
	 * Method to save the priority of one or more tasks
	 *
	 * @param	  array    $ids    An array of primary key ids.
     * @param	  array    $pids   An array of priority values.
	 * @return    mixed	           True on success, otherwise false
	 */
    public function savePriority($pks = null, $priority = null)
    {
        // Initialise variables.
		$table = $this->getTable();
		$conditions = array();

		if (empty($pks)) {
			return JError::raiseWarning(500, JText::_($this->text_prefix . '_ERROR_NO_ITEMS_SELECTED'));
		}

		// update priority values
		foreach ($pks as $i => $pk)
		{
			$table->load((int) $pk);

			// Access checks.
			if (!$this->canEditState($table))
			{
				// Prune items that you can't change.
				unset($pks[$i]);
				JError::raiseWarning(403, JText::_('JLIB_APPLICATION_ERROR_EDITSTATE_NOT_PERMITTED'));
			}
			elseif ($table->priority != $priority[$pk])
			{
				$table->priority = $priority[$pk];

				if (!$table->store()) {
					$this->setError($table->getError());
					return false;
				}
			}
		}

		// Clear the component's cache
		$this->cleanCache();

		return true;
    }


    /**
	 * Method to assign a user to one or more tasks
	 *
	 * @param	  array    $ids    An array of primary key ids.
     * @param	  array    $uids   An array of user id values.
	 * @return    mixed	           True on success, otherwise false
	 */
    public function addUsers($pks = null, $uids = null)
    {
        // Initialise variables.
		$table = $this->getTable();
		$conditions = array();

		if (empty($pks)) {
			return JError::raiseWarning(500, JText::_($this->text_prefix . '_ERROR_NO_ITEMS_SELECTED'));
		}

		// update priority values
		foreach ($pks as $i => $pk)
		{
			$table->load((int) $pk);

			// Access checks.
			if (!$this->canEditState($table))
			{
				// Prune items that you can't change.
				unset($pks[$i]);
				JError::raiseWarning(403, JText::_('JLIB_APPLICATION_ERROR_EDIT_NOT_PERMITTED'));
			}

            $refs = JModel::getInstance('UserRefs', 'ProjectforkModel', array('ignore_request' => true));

            if(!$refs->store($uids, 'task', $pk)) {
                return false;
            }
		}

		// Clear the component's cache
		$this->cleanCache();

		return true;
    }


    /**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return    mixed    The data for the form.
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState('com_projectfork.edit.taskform.data', array());

		if(empty($data)) $data = $this->getItem();

		return $data;
	}


	/**
	 * Get the return URL.
	 *
	 * @return	string	The return URL.
	 */
	public function getReturnPage()
	{
		return base64_encode($this->getState('return_page'));
	}
}