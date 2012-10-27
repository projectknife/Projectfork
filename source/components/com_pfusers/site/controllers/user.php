<?php
/**
 * @package      Projectfork
 * @subpackage   Users
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


jimport('joomla.application.component.controllerform');


/**
 * Projectfork User Form Controller
 *
 */
class PFusersControllerUser extends JControllerForm
{
    /**
     * Default item view
     *
     * @var    string
     */
    protected $view_item = 'user';

    /**
     * Default list view
     *
     * @var    string
     */
    protected $view_list = 'users';


    /**
     * Constructor
     *
     */
    public function __construct($config = array())
	{
	    parent::__construct($config);
    }


    /**
     * Method to add a new record.
     *
     * @return    boolean    True if the article can be added, false if not.
     */
    public function add()
    {
        return false;
    }


    /**
     * Method to cancel an edit.
     *
     * @param     string    $key    The name of the primary key of the URL variable.
     *
     * @return    void
     */
    public function cancel($key = 'id')
    {
        return false;
    }


    /**
     * Method to edit an existing record.
     *
     * @param     string     $key        The name of the primary key of the URL variable.
     * @param     string     $url_var    The name of the URL variable if different from the primary key.
     *
     * @return    boolean                True if access level check and checkout passes, false otherwise.
     */
    public function edit($key = null, $url_var = 'id')
    {
        return false;
    }


    public function deleteAvatar()
    {
        // Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

        $user   = JFactory::getUser();
        $access = PFusersHelper::getActions();
        $id     = JRequest::getUInt('id');
        $model  = $this->getModel();

        // Access check
        if ($user->id != $id) {
            if (!$access->get('core.admin')) {
                $this->setError(JText::_('JERROR_ALERTNOAUTHOR'));
			    $this->setMessage($this->getError(), 'error');

                $this->setRedirect(
    				JRoute::_(
    					'index.php?option=' . $this->option . '&view=' . $this->view_item
    					. $this->getRedirectToItemAppend($id), false
    				)
    			);
                return false;
            }
        }

        if (!$id) {
            $this->setRedirect(
				JRoute::_(
					'index.php?option=' . $this->option . '&view=' . $this->view_item
					. $this->getRedirectToItemAppend($id), false
				)
			);
            return false;
        }

        if (!$model->deleteAvatar($id)) {
            $this->setError($model->getError());
			$this->setMessage($this->getError(), 'error');
            $this->setRedirect(
				JRoute::_(
					'index.php?option=' . $this->option . '&view=' . $this->view_item
					. $this->getRedirectToItemAppend($id), false
				)
			);

            return false;
        }

        $this->setRedirect(
			JRoute::_(
				'index.php?option=' . $this->option . '&view=' . $this->view_item
				. $this->getRedirectToItemAppend($id), false
			)
		);

        return true;
    }


    /**
     * Method to upload or delete a user avatar image
     *
     * @return boolean True on success, False on error
     */
    public function avatar()
    {
        // Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

        $user   = JFactory::getUser();
        $access = PFusersHelper::getActions();
        $file   = JRequest::getVar('avatar', '', 'files', 'array');
        $id     = JRequest::getUInt('id');
        $model  = $this->getModel();

        // Access check
        if ($user->id != $id) {
            if (!$access->get('core.admin')) {
                $this->setError(JText::_('JERROR_ALERTNOAUTHOR'));
			    $this->setMessage($this->getError(), 'error');

                $this->setRedirect(
    				JRoute::_(
    					'index.php?option=' . $this->option . '&view=' . $this->view_item
    					. $this->getRedirectToItemAppend($id), false
    				)
    			);
                return false;
            }
        }

        if (!empty($file['tmp_name'])) {
            if (!$model->saveAvatar($id, $file)) {
                $this->setError($model->getError());
			    $this->setMessage($this->getError(), 'error');

                $this->setRedirect(
    				JRoute::_(
    					'index.php?option=' . $this->option . '&view=' . $this->view_item
    					. $this->getRedirectToItemAppend($id), false
    				)
    			);

                return false;
            }
        }

        $this->setRedirect(
			JRoute::_(
				'index.php?option=' . $this->option . '&view=' . $this->view_item
				. $this->getRedirectToItemAppend($id), false
			)
		);

        return true;
    }


    /**
     * Method to get a model object, loading it if required.
     *
     * @param     string    $name      The model name. Optional.
     * @param     string    $prefix    The class prefix. Optional.
     * @param     array     $config    Configuration array for model. Optional.
     *
     * @return    object               The model.
     */
    public function &getModel($name = 'User', $prefix = 'PFusersModel', $config = array('ignore_request' => true))
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
        return false;
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
        return false;
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
        $append  = '';

        // Setup redirect info.
        if ($tmpl) $append .= '&tmpl=' . $tmpl;

        if ($id)      $append .= '&' . $url_var . '=' . $id;
        if ($item_id) $append .= '&Itemid=' . $item_id;

        return $append;
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


    }
}
