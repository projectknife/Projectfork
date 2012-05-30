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


jimport('joomla.application.component.controlleradmin');


/**
 * Projectfork Task List Controller
 *
 */
class ProjectforkControllerTasks extends JControllerAdmin
{
	/**
	 * The default view
     *
	 */
	protected $view_list = 'tasks';


	/**
	 * Method to get a model object, loading it if required.
	 *
	 * @param	string	$name	The model name. Optional.
	 * @param	string	$prefix	The class prefix. Optional.
	 * @param	array	$config	Configuration array for model. Optional.
	 *
	 * @return	object	The model.
	 */
	public function &getModel($name = 'TaskForm', $prefix = 'ProjectforkModel', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);

		return $model;
	}


    /**
	 * Override for json return response
     *
     * @see controlleradmin.php
	 *
	 * @return	string	JSON encoded response
	 */
    public function saveorder()
    {
        // Call parent method to save order
        $result = parent::saveorder();

        // Set the MIME type for JSON output.
        JFactory::getDocument()->setMimeEncoding('application/json');

        // Change the suggested filename.
        JResponse::setHeader('Content-Disposition','attachment;filename="'.$this->view_list.'.json"');

        if(!$result) {
            $data = array('success' => false, 'message' => JText::sprintf('JLIB_APPLICATION_ERROR_REORDER_FAILED', $model->getError()));
        }
        else {
            $data = array('success' => true, 'message' => JText::_('JLIB_APPLICATION_SUCCESS_ORDERING_SAVED'));
        }

        // Output the JSON data.
        echo json_encode($data);
        JFactory::getApplication()->close();
    }


    /**
	 * Override for json return response
     *
     * @see controlleradmin.php
	 *
	 * @return	string	JSON encoded response
	 */
    public function complete()
    {
        // Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Get the input
		$pks     = JRequest::getVar('cid', null, 'post', 'array');
		$complete = JRequest::getVar('complete', null, 'post', 'array');

		// Sanitize the input
		JArrayHelper::toInteger($pks);
		JArrayHelper::toInteger($order);

		// Get the model
		$model = $this->getModel();

		// Save the ordering
		$result = $model->setComplete($pks, $complete);

        // Set the MIME type for JSON output.
        JFactory::getDocument()->setMimeEncoding('application/json');

        // Change the suggested filename.
        JResponse::setHeader('Content-Disposition','attachment;filename="'.$this->view_list.'.json"');

        if(!$result) {
            $data = array('success' => false, 'message' => JText::_($model->getError()));
        }
        else {
            $data = array('success' => true, 'message' => JText::_('COM_PROJECTFORK_TASK_UPDATE_SUCCESS'));
        }

        // Output the JSON data.
        echo json_encode($data);
        JFactory::getApplication()->close();
    }
}