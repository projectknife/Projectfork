<?php
/**
* @package   Projectfork
* @copyright Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL, see license.txt
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

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.controllerform');


class ProjectforkControllerTask extends JControllerForm
{
	/**
	 * Class constructor.
	 *
	 * @param	  array    $config    A named array of configuration variables
	 * @return    JControllerForm
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);
	}


    /**
	 * Sets the project of the task currently being edited.
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
		$app->setUserState('com_projectfork.edit.task.project',	$project);
		$app->setUserState('com_projectfork.edit.task.data', $data);

		$this->project_id = $project;

		$this->setRedirect(JRoute::_('index.php?option='.$this->option.'&view='.$this->view_item.$this->getRedirectToItemAppend($recordId), false));
	}


    public function setTasklist()
    {
        // Initialise variables.
		$app      = JFactory::getApplication();
		$data     = JRequest::getVar('jform', array(), 'post', 'array');
		$recordId = JRequest::getInt('id');

		$app->setUserState('com_projectfork.edit.task.data', $data);

		$this->setRedirect(JRoute::_('index.php?option='.$this->option.'&view='.$this->view_item.$this->getRedirectToItemAppend($recordId), false));
    }
}