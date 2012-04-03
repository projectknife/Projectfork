<?php
/**
* @package   Projectfork
* @copyright Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.php
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

defined('_JEXEC') or die;


jimport('joomla.application.component.controllerform');


/**
 * Projectfork Task List Form Controller
 *
 */
class ProjectforkControllerTasklistForm extends JControllerForm
{
	protected $view_item = 'tasklistform';
	protected $view_list = 'tasklists';


	/**
	 * Method to add a new record.
	 *
	 * @return	boolean	True if the item can be added, false if not.
	 */
	public function add()
	{
		if (!parent::add()) {
			// Redirect to the return page.
			$this->setRedirect($this->getReturnPage());
		}
	}


	/**
	 * Method override to check if you can edit an existing record.
	 *
	 * @param	array	$data	An array of input data.
	 * @param	string	$key	The name of the key for the primary key.
	 * @return	boolean
	 */
	protected function allowEdit($data = array(), $key = 'id')
	{
		// Initialise variables.
		$recordId	= (int) isset($data[$key]) ? $data[$key] : 0;
		$user		= JFactory::getUser();
		$userId		= $user->get('id');
		$asset		= 'com_projectfork.tasklist.'.$recordId;

		// Check general edit permission first.
		if ($user->authorise('core.edit', $asset) || $user->authorise('tasklist.edit', $asset)) {
			return true;
		}

		// Fallback on edit.own.
		// First test if the permission is available.
		if ($user->authorise('core.edit.own', $asset) || $user->authorise('tasklist.edit.own', $asset)) {
			// Now test the owner is the user.
			$ownerId = (int) isset($data['created_by']) ? $data['created_by'] : 0;

			if (empty($ownerId) && $recordId) {
				// Need to do a lookup from the model.
				$record	= $this->getModel()->getItem($recordId);

				if (empty($record)) return false;

				$ownerId = $record->created_by;
			}

			// If the owner matches 'me' then do the test.
			if ($ownerId == $userId) return true;
		}

		// Since there is no asset tracking, revert to the component permissions.
		return parent::allowEdit($data, $key);
	}


	/**
	 * Method to cancel an edit.
	 *
	 * @param	string	$key	The name of the primary key of the URL variable.
	 * @return	Boolean	True if access level checks pass, false otherwise.
	 */
	public function cancel($key = 'id')
	{
		parent::cancel($key);

		// Redirect to the return page.
		$this->setRedirect($this->getReturnPage());
	}


	/**
	 * Method to edit an existing record.
	 *
	 * @param	string	$key	The name of the primary key of the URL variable.
	 * @param	string	$urlVar	The name of the URL variable if different from the primary key (sometimes required to avoid router collisions).
	 * @return	Boolean	True if access level check and checkout passes, false otherwise.
	 */
	public function edit($key = null, $urlVar = 'id')
	{
		$result = parent::edit($key, $urlVar);

		return $result;
	}


	/**
	 * Method to get a model object, loading it if required.
	 *
	 * @param	string	$name	The model name. Optional.
	 * @param	string	$prefix	The class prefix. Optional.
	 * @param	array	$config	Configuration array for model. Optional.
	 *
	 * @return	object	The model.
	 */
	public function &getModel($name = 'TasklistForm', $prefix = '', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);

		return $model;
	}


	/**
	 * Gets the URL arguments to append to an item redirect.
	 *
	 * @param	int		$recordId	The primary key id for the item.
	 * @param	string	$urlVar		The name of the URL variable for the id.
	 * @return	string	The arguments to append to the redirect URL.
	 */
	protected function getRedirectToItemAppend($recordId = null, $urlVar = 'id')
	{
		// Need to override the parent method completely.
		$tmpl	= JRequest::getCmd('tmpl');
		$layout	= JRequest::getCmd('layout', 'edit');
        $itemId	= JRequest::getInt('Itemid');
		$return	= $this->getReturnPage();
		$append	= '';


		// Setup redirect info.
		if ($tmpl) $append .= '&tmpl='.$tmpl;

		$append .= '&layout=edit';

		if ($recordId) $append .= '&'.$urlVar.'='.$recordId;
		if ($itemId)   $append .= '&Itemid='.$itemId;
		if ($return)   $append .= '&return='.base64_encode($return);


		return $append;
	}


	/**
	 * Get the return URL.
	 * If a "return" variable has been passed in the request
	 *
	 * @return	string	The return URL.
	 */
	protected function getReturnPage()
	{
		$return = JRequest::getVar('return', null, 'default', 'base64');

		if (empty($return) || !JUri::isInternal(base64_decode($return))) {
			return JRoute::_('index.php?option=com_projectfork&view='.$this->view_list, false);
		}
		else {
			return base64_decode($return);
		}
	}


	/**
	 * Function that allows child controller access to model data after the data has been saved.
	 *
	 * @param	JModel	$model		The data model object.
	 * @param	array	$validData	The validated data.
	 * @return	void
	 */
	protected function postSaveHook(JModel &$model, $validData)
	{
		$task = $this->getTask();

		if ($task == 'save') {
			$this->setRedirect(JRoute::_('index.php?option=com_projectfork&view='.$this->view_list, false));
		}
	}


	/**
	 * Method to save a record.
	 *
	 * @param	string	$key	The name of the primary key of the URL variable.
	 * @param	string	$urlVar	The name of the URL variable if different from the primary key (sometimes required to avoid router collisions).
	 * @return	Boolean	True if successful, false otherwise.
	 */
	public function save($key = null, $urlVar = 'id')
	{
		$result = parent::save($key, $urlVar);

		// If ok, redirect to the return page.
		if ($result) {
			$this->setRedirect($this->getReturnPage());
		}

		return $result;
	}


    /**
	 * Sets the project of the milestone currently being edited.
	 *
	 * @return	void
	 */
	public function setProject()
	{
		// Initialise variables.
		$app      = JFactory::getApplication();
		$data     = JRequest::getVar('jform', array(), 'post', 'array');
		$recordId = JRequest::getInt('id');
		$project  = (int) $data['project_id'];


        // Set the project as active
        ProjectforkHelper::setActiveProject($project);


        //Save the data in the session.
		//$app->setUserState('com_projectfork.edit.tasklist.project',	$project);
		$app->setUserState('com_projectfork.edit.tasklist.data', $data);

		$this->project_id = $project;

		$this->setRedirect(JRoute::_('index.php?option='.$this->option.'&view='.$this->view_item.$this->getRedirectToItemAppend($recordId), false));
	}


    /**
	 * Sets the selected milestone of the form
	 *
	 * @return	void
	 */
    public function setMilestone()
    {
        $recordId = JRequest::getInt('id');

        $this->setFormData();
		$this->setRedirect(JRoute::_('index.php?option='.$this->option.'&view='.$this->view_item.$this->getRedirectToItemAppend($recordId), false));
    }


    /**
	 * Stores the form data
	 *
	 * @return	void
	 */
    protected function setFormData()
    {
        // Initialise variables.
		$app  = JFactory::getApplication();
		$data = JRequest::getVar('jform', array(), 'post', 'array');

		$app->setUserState('com_projectfork.edit.tasklist.data', $data);
    }
}