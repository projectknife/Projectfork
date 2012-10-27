<?php
/**
 * @package      Projectfork
 * @subpackage   Repository
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
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
     * Constructor.
     *
     * @param    array          $config    An optional associative array of configuration settings
     * @see      jcontroller
     */
    public function __construct($config = array())
    {
        parent::__construct($config);
    }


    /**
     * Proxy for getModel.
     *
     * @param     string    $name      The name of the model.
     * @param     string    $prefix    The prefix for the PHP class name.
     * @return    jmodel
     */
    public function getModel($name = 'Repository', $prefix = 'PFrepoModel', $config = array('ignore_request' => true))
    {
        $model = parent::getModel($name, $prefix, $config);

        return $model;
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
        $did = (array) JRequest::getVar('did', array(), '', 'array');
        $nid = (array) JRequest::getVar('nid', array(), '', 'array');
        $fid = (array) JRequest::getVar('fid', array(), '', 'array');


        if ((!is_array($did) && !is_array($nid) && !is_array($fid)) || (count($did) < 1 && count($nid) < 1 && count($fid) < 1)) {
            JError::raiseWarning(500, JText::_($this->text_prefix . '_NO_ITEM_SELECTED'));
        }
        else {
            jimport('joomla.utilities.arrayhelper');
            $app = JFactory::getApplication();

            // Delete directories
            if (is_array($did) && count($did) > 0) {
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
            if (is_array($nid) && count($nid) > 0) {
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
            if (is_array($fid) && count($fid) > 0) {
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

        $this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_list . ($parent_id > 1 ? '&filter_parent_id=' . $parent_id : ''), false));
    }


    /**
     * Method to run batch operations.
     *
     * @return    void
     */
    public function batch()
    {
        // Check for request forgeries
        JSession::checkToken() or die(JText::_('JINVALID_TOKEN'));

        $parent_id = (int) JRequest::getUInt('filter_parent_id', 0);

        $vars = (array) JRequest::getVar('batch', array(), '', 'array');
        $did  = (array) JRequest::getVar('did', array(), '', 'array');
        $nid  = (array) JRequest::getVar('nid', array(), '', 'array');
        $fid  = (array) JRequest::getVar('fid', array(), '', 'array');

        if ((!is_array($did) && !is_array($nid) && !is_array($fid)) || (count($did) < 1 && count($nid) < 1 && count($fid) < 1)) {
            JError::raiseWarning(500, JText::_($this->text_prefix . '_NO_ITEM_SELECTED'));
        }
        else {
            jimport('joomla.utilities.arrayhelper');
            $app = JFactory::getApplication();

            // Batch directories
            if (is_array($did) && count($did) > 0) {
                $model = $this->getModel('Directory');

                JArrayHelper::toInteger($did);

                if ($model->batch($vars, $did)) {
                    $app->enqueueMessage(JText::_('COM_PROJECTFORK_SUCCESS_BATCH_DIRECTORIES'));
                }
                else {
                    $app->enqueueMessage($model->getError(), 'error');
                }
            }

            // Batch notes
            if (is_array($nid) && count($nid) > 0) {
                $model = $this->getModel('Note');

                JArrayHelper::toInteger($nid);

                if ($model->batch($vars, $nid)) {
                    $app->enqueueMessage(JText::_('COM_PROJECTFORK_SUCCESS_BATCH_NOTES'));
                }
                else {
                    $app->enqueueMessage($model->getError(), 'error');
                }
            }

            // Batch files
            if (is_array($fid) && count($fid) > 0) {
                $model = $this->getModel('File');

                JArrayHelper::toInteger($fid);

                if ($model->batch($vars, $fid)) {
                    $app->enqueueMessage(JText::_('COM_PROJECTFORK_SUCCESS_BATCH_FILES'));
                }
                else {
                    $app->enqueueMessage($model->getError(), 'error');
                }
            }
        }

        $this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_list . ($parent_id > 1 ? '&filter_parent_id=' . $parent_id : ''), false));
    }
}
