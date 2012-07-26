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

defined('_JEXEC') or die;


jimport('joomla.application.component.controllerform');


/**
 * Projectfork Comment Form Controller
 *
 */
class ProjectforkControllerCommentform extends JControllerForm
{
	protected $view_item = 'commentform';
	protected $view_list = 'comments';
    protected $id = 0;


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
		$asset		= 'com_projectfork.comment.'.$recordId;

		// Check general edit permission first.
		if ($user->authorise('core.edit', $asset) || $user->authorise('comment.edit', $asset)) {
			return true;
		}

		// Fallback on edit.own.
		// First test if the permission is available.
		if ($user->authorise('core.edit.own', $asset) || $user->authorise('comment.edit.own', $asset)) {
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
	    JPluginHelper::importPlugin('content', 'pfcomments');

        $data = array();
        $id   = (int) JRequest::getVar($key);

        if (!class_exists('plgContentPfcomments')) {
            $data = array('success' => false, 'message' => JText::_('COM_PROJECTFORK_COMMENTS_PLUGIN_NOT_FOUND'));
        }
        else {
            if (!$id) {
                $data = array('success' => false, 'message' => JText::_('COM_PROJECTFORK_COMMENTS_COMMENT_NOT_FOUND'));
            }
            else {
                $model = $this->getModel();
                $item  = $model->getItem($id);

                if (!$item) {
                    $data = array('success' => false, 'message' => $model->getError());
                }
                else {
                    $html = plgContentPfcomments::renderItemContent($item, 0);

                    $data = array('success' => true, 'message' => '', 'data' => $html);
                }
            }
        }

        // Set the MIME type for JSON output.
        JFactory::getDocument()->setMimeEncoding('application/json');

        // Change the suggested filename.
        JResponse::setHeader('Content-Disposition','attachment;filename="'.$this->view_item.'.json"');

        // Output the JSON data.
        echo json_encode($data);

        JFactory::getApplication()->close();
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
		JPluginHelper::importPlugin('content', 'pfcomments');

        $data   = array();
        $id     = (int) JRequest::getVar('id');
        $result = parent::edit($key, $urlVar);

        if (!$result) {
            $data = array('success' => false, 'message' => $this->getError());
        }
        else {
            if (!class_exists('plgContentPfcomments')) {
                $data = array('success' => false, 'message' => JText::_('COM_PROJECTFORK_COMMENTS_PLUGIN_NOT_FOUND'));
            }
            else {
                if(!$id) {
                    $data = array('success' => false, 'message' => JText::_('COM_PROJECTFORK_COMMENTS_COMMENT_NOT_FOUND'));
                }
                else {
                    $model = $this->getModel();
                    $item  = $model->getItem($id);

                    if (!$item) {
                        $data = array('success' => false, 'message' => $model->getError());
                    }
                    else {
                        $html = plgContentPfcomments::renderEditor($item->id, true, $item);

                        $data = array('success' => true, 'message' => '', 'data' => $html);
                    }
                }
            }
        }

        // Set the MIME type for JSON output.
        JFactory::getDocument()->setMimeEncoding('application/json');

        // Change the suggested filename.
        JResponse::setHeader('Content-Disposition','attachment;filename="'.$this->view_item.'.json"');

        // Output the JSON data.
        echo json_encode($data);

        JFactory::getApplication()->close();
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
	public function &getModel($name = 'CommentForm', $prefix = 'ProjectforkModel', $config = array('ignore_request' => true))
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
        $this->id = $model->getState($this->context . '.id');
        $task     = $this->getTask();

		if ($task == 'save') {

			$this->setRedirect(JRoute::_('index.php?option=com_projectfork&view='.$this->view_list, false));
		}
	}


	/**
	 * Method to save a record.
	 *
	 * @param	string	$key	The name of the primary key of the URL variable.
	 * @param	string	$urlVar	The name of the URL variable if different from the primary key (sometimes required to avoid router collisions).
     *
	 * @return	Boolean	True if successful, false otherwise.
	 */
	public function save($key = null, $urlVar = 'id')
	{
	    $id     = (int) JRequest::getVar($urlVar);
	    $result = parent::save($key, $urlVar);


        if(!$result) {
            $data = array('success' => false, 'message' => JText::_($this->getError()));
        }
        else {
            $html  = '';

            if($this->id) {
                // Load the item we just stored
                $model = $this->getModel();
                $item  = $model->getItem($this->id);

                if(class_exists('plgContentPfcomments') && !is_null($item)) {
                    if($id == $this->id) {
                        $html = plgContentPfcomments::renderItemContent($item, 0);
                    }
                    else {
                        $html = plgContentPfcomments::renderItem($item, 0, ($item->parent_replies == 1 && $item->parent_id != 0));
                    }
                }
            }

            $data = array('success' => true, 'message' => JText::_('COM_PROJECTFORK_TASK_UPDATE_SUCCESS'), 'data' => $html);
        }

        // Set the MIME type for JSON output.
        JFactory::getDocument()->setMimeEncoding('application/json');

        // Change the suggested filename.
        JResponse::setHeader('Content-Disposition','attachment;filename="'.$this->view_item.'.json"');

        // Output the JSON data.
        echo json_encode($data);

        JFactory::getApplication()->close();
	}


    public function loadEditor()
    {
        JPluginHelper::importPlugin('content');

        $data   = array();
        $jform  = JRequest::getVar('jform', array(), 'post', 'array');
        $parent = (isset($jform['parent_id']) ? (int) $jform['parent_id'] : 0);

        if(!class_exists('plgContentPfcomments') ) {
            $data = array('success' => false, 'message' => JText::_('COM_PROJECTFORK_COMMENTS_PLUGIN_NOT_FOUND'));
        }
        else {
            $html   = plgContentPfcomments::renderEditor($parent);

            $data = array('success' => true, 'message' => '', 'data' => $html);
        }

        // Set the MIME type for JSON output.
        JFactory::getDocument()->setMimeEncoding('application/json');

        // Change the suggested filename.
        JResponse::setHeader('Content-Disposition','attachment;filename="'.$this->view_item.'.json"');

        // Output the JSON data.
        echo json_encode($data);

        JFactory::getApplication()->close();
    }
}