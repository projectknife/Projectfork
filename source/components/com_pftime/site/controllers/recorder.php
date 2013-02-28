<?php
/**
 * @package      Projectfork
 * @subpackage   Timetracking
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2013 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


jimport('joomla.application.component.controlleradmin');


/**
 * Projectfork Time Recorder List Controller
 *
 */
class PFtimeControllerRecorder extends JControllerAdmin
{
    /**
     * The default view
     *
     * @var    string
     */
    protected $view_list = 'recorder';

    /**
     * The prefix to use with controller messages.
     *
     * @var    string
     */
    protected $text_prefix = 'COM_PROJECTFORK_TIME_REC';


    /**
     * Method to get a model object, loading it if required.
     *
     * @param     string    $name      The model name. Optional.
     * @param     string    $prefix    The class prefix. Optional.
     * @param     array     $config    Configuration array for model. Optional.
     *
     * @return    object               The model.
     */
    public function &getModel($name = 'Recorder', $prefix = 'PFtimeModel', $config = array('ignore_request' => true))
    {
        $model = parent::getModel($name, $prefix, $config);
        return $model;
    }


    /**
     * Method to add one or more tasks to the recorder
     *
     * @return    boolean    True on success, False on error
     */
    public function add()
    {
        $user  = JFactory::getUser();
		$input = JFactory::getApplication()->input;
		$cid   = $input->get('cid', array(), 'array');

        // Check general access
        if (!$user->authorise('core.create', $this->option)) {
            $this->setError(JText::_('JLIB_APPLICATION_ERROR_SAVE_NOT_PERMITTED'));
			$this->setMessage($this->getError(), 'error');
            $this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_list . '&tmpl=component', false));
            return false;
        }

        // Check if empty list
        if (empty($cid)) {
			JError::raiseWarning(500, JText::_($this->text_prefix . '_NO_ITEM_SELECTED'));
            $this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_list . '&tmpl=component', false));
            return false;
		}

        $model = $this->getModel();

        // Add the items to the recorder
		if (!$model->addItems($cid)) {
			JError::raiseWarning(500, $model->getError());
            $this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_list . '&tmpl=component', false));
            return false;
		}

        $this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_list . '&tmpl=component', false));
        return true;
    }
}
