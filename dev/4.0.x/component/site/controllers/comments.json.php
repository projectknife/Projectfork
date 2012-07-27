<?php
/**
 * @package      Projectfork
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


jimport('joomla.application.component.controlleradmin');


/**
 * Projectfork Comment List Controller
 *
 */
class ProjectforkControllerComments extends JControllerAdmin
{
    /**
     * The default view
     *
     * @var    string
     */
    protected $view_list = 'comments';


    /**
     * Method to get a model object, loading it if required.
     *
     * @param     string    $name      The model name. Optional.
     * @param     string    $prefix    The class prefix. Optional.
     * @param     array     $config    Configuration array for model. Optional.
     *
     * @return    object               The model.
     */
    public function &getModel($name = 'CommentForm', $prefix = 'ProjectforkModel', $config = array('ignore_request' => true))
    {
        $model = parent::getModel($name, $prefix, $config);

        return $model;
    }


    public function publish()
    {
        // Check for request forgeries
        if (!JSession::checkToken()) {
            $data = array('success' => false, 'message' => JText::_('JINVALID_TOKEN'));
        }
        else {
            // Get items to publish from the request.
            $cid   = (array) JRequest::getVar('cid', array(), '', 'array');
            $id    = (int)   JRequest::getVar('id');
            $data  = array('publish' => 1, 'unpublish' => 0, 'archive' => 2, 'trash' => -2, 'report' => -3);
            $task  = $this->getTask();
            $value = JArrayHelper::getValue($data, $task, 0, 'int');

            if ($id) $cid[] = $id;

            if (empty($cid)) {
                $data = array('success' => false, 'message' => JText::_($this->text_prefix . '_NO_ITEM_SELECTED'));
            }
            else {
                // Get the model.
                $model = $this->getModel();

                // Make sure the item ids are integers
                JArrayHelper::toInteger($cid);

                // Publish the items.
                if (!$model->publish($cid, $value)) {
                    $data = array('success' => false, 'message' => $model->getError());
                }
                else {
                    if ($value == 1) {
                        $ntext = $this->text_prefix . '_N_ITEMS_PUBLISHED';
                    }
                    elseif ($value == 0) {
                        $ntext = $this->text_prefix . '_N_ITEMS_UNPUBLISHED';
                    }
                    elseif ($value == 2) {
                        $ntext = $this->text_prefix . '_N_ITEMS_ARCHIVED';
                    }
                    else {
                        $ntext = $this->text_prefix . '_N_ITEMS_TRASHED';
                    }

                    $data = array('success' => true, 'message' => JText::plural($ntext, count($cid)));
                }
            }
        }

        // Set the MIME type for JSON output.
        JFactory::getDocument()->setMimeEncoding('application/json');
        JResponse::setHeader('Content-Disposition','attachment;filename="' . $this->view_list. '.json"');

        // Output the JSON data.
        echo json_encode($data);

        JFactory::getApplication()->close();
    }
}
