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


jimport('joomla.application.component.controlleradmin');


/**
 * Repository controller class.
 *
 */
class PFrepoControllerRepository extends JControllerAdmin
{
    /**
     * Proxy for getModel.
     *
     * @param     string    $name      The name of the model.
     * @param     string    $prefix    The prefix for the PHP class name.
     *
     * @return    jmodel
     */
    public function getModel($name = 'Repository', $prefix = 'PFrepoModel', $config = array('ignore_request' => true))
    {
        $model = parent::getModel($name, $prefix, $config);

        return $model;
    }


    /**
     * Check in of one or more records.
     *
     * @return    boolean    True on success
     */
    public function checkin()
    {
        // Check for request forgeries.
        JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

        $parent_id = (int) JRequest::getUInt('filter_parent_id', 0);

        $link = 'index.php?option=' . $this->option . '&view=' . $this->view_list
              . ($parent_id > 1 ? '&filter_parent_id=' . $parent_id : '');

        // Get items to check in from the request.
        $did = JRequest::getVar('did', array(), 'post', 'array');
        $nid = JRequest::getVar('nid', array(), 'post', 'array');
        $fid = JRequest::getVar('fid', array(), 'post', 'array');

        // Check-in directories
        if (count($did)) {
            $model = $this->getModel('Directory');

            if (!$model->checkin($did)) {
                $message = JText::sprintf('JLIB_APPLICATION_ERROR_CHECKIN_FAILED', $model->getError());
                $this->setRedirect(JRoute::_($link, false), $message, 'error');
                return false;
            }
        }

        // Check-in notes
        if (count($nid)) {
            $model = $this->getModel('Note');

            if (!$model->checkin($nid)) {
                $message = JText::sprintf('JLIB_APPLICATION_ERROR_CHECKIN_FAILED', $model->getError());
                $this->setRedirect(JRoute::_($link, false), $message, 'error');
                return false;
            }
        }

        // Check-in files
        if (count($fid)) {
            $model = $this->getModel('File');

            if (!$model->checkin($fid)) {
                $message = JText::sprintf('JLIB_APPLICATION_ERROR_CHECKIN_FAILED', $model->getError());
                $this->setRedirect(JRoute::_($link, false), $message, 'error');
                return false;
            }
        }

        $message = JText::plural($this->text_prefix . '_N_ITEMS_CHECKED_IN', (count($did) + count($nid) + count($fid)));
        $this->setRedirect(JRoute::_($link, false), $message);

        return true;
    }


    /**
     * Removes an item.
     *
     * @return    void
     */
    public function delete()
    {
        // Check for request forgeries
        JSession::checkToken() or die(JText::_('JINVALID_TOKEN'));

        $parent_id = (int) JRequest::getUInt('filter_parent_id', 0);

        // Get items to remove from the request.
        $did = JRequest::getVar('did', array(), 'post', 'array');
        $nid = JRequest::getVar('nid', array(), 'post', 'array');
        $fid = JRequest::getVar('fid', array(), 'post', 'array');

        if ((count($did) < 1 && count($nid) < 1 && count($fid) < 1)) {
            JError::raiseWarning(500, JText::_($this->text_prefix . '_NO_ITEM_SELECTED'));
        }
        else {
            jimport('joomla.utilities.arrayhelper');
            $app = JFactory::getApplication();

            // Delete directories
            if (count($did)) {
                $model = $this->getModel('Directory');

                JArrayHelper::toInteger($did);

                if ($model->delete($did)) {
                    $app->enqueueMessage(JText::plural('COM_PROJECTFORK_DIRECTORIES_N_ITEMS_DELETED', count($did)));
                }
                else {
                    $app->enqueueMessage($model->getError(), 'error');
                }
            }

            // Delete notes
            if (count($nid)) {
                $model = $this->getModel('Note');

                JArrayHelper::toInteger($nid);

                if ($model->delete($nid)) {
                    $app->enqueueMessage(JText::plural('COM_PROJECTFORK_NOTES_N_ITEMS_DELETED', count($nid)));
                }
                else {
                    $app->enqueueMessage($model->getError(), 'error');
                }
            }

            // Delete files
            if (count($fid)) {
                $model = $this->getModel('File');

                JArrayHelper::toInteger($fid);

                if ($model->delete($fid)) {
                    $app->enqueueMessage(JText::plural('COM_PROJECTFORK_FILES_N_ITEMS_DELETED', count($fid)));
                }
                else {
                    $app->enqueueMessage($model->getError(), 'error');
                }
            }
        }

        $link = 'index.php?option=' . $this->option . '&view=' . $this->view_list
              . ($parent_id > 1 ? '&filter_parent_id=' . $parent_id : '');

        $this->setRedirect(JRoute::_($link, false));
    }


    /**
     * Method to run batch operations.
     *
     * @param     object     $model    The model of the component being processed.
     *
     * @return    boolean              True if successful, false otherwise and internal error is set.
     */
    public function batch($model = null)
    {
        // Check for request forgeries
        JSession::checkToken() or die(JText::_('JINVALID_TOKEN'));

        $parent_id = (int) JRequest::getUInt('filter_parent_id', 0);
        $return    = true;

        $vars = JRequest::getVar('batch', array(), 'post', 'array');
        $did  = JRequest::getVar('did', array(), 'post', 'array');
        $nid  = JRequest::getVar('nid', array(), 'post', 'array');
        $fid  = JRequest::getVar('fid', array(), 'post', 'array');

        if (count($did) < 1 && count($nid) < 1 && count($fid) < 1) {
            JError::raiseWarning(500, JText::_($this->text_prefix . '_NO_ITEM_SELECTED'));
            $return = false;
        }
        else {
            jimport('joomla.utilities.arrayhelper');
            $app = JFactory::getApplication();

            // Batch directories
            if (count($did) > 0) {
                $model    = $this->getModel('Directory');
                $contexts = array();

                JArrayHelper::toInteger($did);

                // Build an array of item contexts to check
                foreach ($did as $id)
                {
                    $contexts[$id] = $this->option . '.directory.' . $id;
                }

                // Process
                if ($model->batch($vars, $did, $contexts)) {
                    $app->enqueueMessage(JText::_('COM_PROJECTFORK_SUCCESS_BATCH_DIRECTORIES'));
                }
                else {
                    $app->enqueueMessage($model->getError(), 'error');
                    $return = false;
                }
            }

            // Batch notes
            if (count($nid) > 0) {
                $model    = $this->getModel('Note');
                $contexts = array();

                JArrayHelper::toInteger($nid);

                // Build an array of item contexts to check
                foreach ($nid as $id)
                {
                    $contexts[$id] = $this->option . '.note.' . $id;
                }

                // Process
                if ($model->batch($vars, $nid, $contexts)) {
                    $app->enqueueMessage(JText::_('COM_PROJECTFORK_SUCCESS_BATCH_NOTES'));
                }
                else {
                    $app->enqueueMessage($model->getError(), 'error');
                    $return = false;
                }
            }

            // Batch files
            if (count($fid) > 0) {
                $model    = $this->getModel('File');
                $contexts = array();

                JArrayHelper::toInteger($fid);

                // Build an array of item contexts to check
                foreach ($fid as $id)
                {
                    $contexts[$id] = $this->option . '.file.' . $id;
                }

                // Process
                if ($model->batch($vars, $fid, $contexts)) {
                    $app->enqueueMessage(JText::_('COM_PROJECTFORK_SUCCESS_BATCH_FILES'));
                }
                else {
                    $app->enqueueMessage($model->getError(), 'error');
                    $return = false;
                }
            }
        }

        $link = 'index.php?option=' . $this->option . '&view=' . $this->view_list
              . ($parent_id > 1 ? '&filter_parent_id=' . $parent_id : '');

        $this->setRedirect(JRoute::_($link, false));

        return $return;
    }
}
