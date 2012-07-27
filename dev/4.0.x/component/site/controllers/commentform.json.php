<?php
/**
* @package      Projectfork
*
* @author       Tobias Kuhn (eaxs)
* @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
* @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
**/

defined('_JEXEC') or die();


jimport('joomla.application.component.controllerform');


/**
 * Projectfork Comment Form Controller
 *
 */
class ProjectforkControllerCommentform extends JControllerForm
{
    /**
     * The item view name.
     *
     * @var    string
     **/
    protected $view_item = 'commentform';

    /**
     * The list view name.
     *
     * @var    string
     **/
    protected $view_list = 'comments';

    /**
     * The id of the current item.
     *
     * @var    integer
     **/
    protected $id = 0;


    /**
     * Method to cancel an edit.
     *
     * @param     string    $key    The name of the primary key of the URL variable.
     *
     * @return    void
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
        JResponse::setHeader('Content-Disposition', 'attachment;filename="' . $this->view_item. '.json"');

        // Output the JSON data.
        echo json_encode($data);

        JFactory::getApplication()->close();
    }


    /**
     * Method to edit an existing record.
     *
     * @param     string    $key        The name of the primary key of the URL variable.
     * @param     string    $url_var    The name of the URL variable if different from the primary key.
     *
     * @return    void
     */
    public function edit($key = null, $url_var = 'id')
    {
        JPluginHelper::importPlugin('content', 'pfcomments');

        $data   = array();
        $id     = (int) JRequest::getVar('id');
        $result = parent::edit($key, $url_var);

        if (!$result) {
            $data = array('success' => false, 'message' => $this->getError());
        }
        else {
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
                        $html = plgContentPfcomments::renderEditor($item->id, true, $item);
                        $data = array('success' => true, 'message' => '', 'data' => $html);
                    }
                }
            }
        }

        // Set the MIME type for JSON output.
        JFactory::getDocument()->setMimeEncoding('application/json');
        JResponse::setHeader('Content-Disposition', 'attachment;filename="' . $this->view_item. '.json"');

        // Output the JSON data.
        echo json_encode($data);

        JFactory::getApplication()->close();
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
    public function &getModel($name = 'CommentForm', $prefix = 'ProjectforkModel', $config = array('ignore_request' => true))
    {
        $model = parent::getModel($name, $prefix, $config);

        return $model;
    }


    /**
     * Method to save a record.
     *
     * @param     string    $key        The name of the primary key of the URL variable.
     * @param     string    $url_var    The name of the URL variable if different from the primary key.
     *
     * @return    void
     */
    public function save($key = null, $url_var = 'id')
    {
        $id     = (int) JRequest::getVar($url_var);
        $result = parent::save($key, $url_var);


        if (!$result) {
            $data = array('success' => false, 'message' => JText::_($this->getError()));
        }
        else {
            $html  = '';

            if ($this->id) {
                // Load the item we just stored
                $model = $this->getModel();
                $item  = $model->getItem($this->id);

                if (class_exists('plgContentPfcomments') && !is_null($item)) {
                    if ($id == $this->id) {
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
        JResponse::setHeader('Content-Disposition', 'attachment;filename="' . $this->view_item. '.json"');

        // Output the JSON data.
        echo json_encode($data);

        JFactory::getApplication()->close();
    }


    /**
     * Method to load the comment editor
     *
     * @return    void
     */
    public function loadEditor()
    {
        JPluginHelper::importPlugin('content');

        $data   = array();
        $jform  = JRequest::getVar('jform', array(), 'post', 'array');
        $parent = (isset($jform['parent_id']) ? (int) $jform['parent_id'] : 0);

        if (!class_exists('plgContentPfcomments') ) {
            $data = array('success' => false, 'message' => JText::_('COM_PROJECTFORK_COMMENTS_PLUGIN_NOT_FOUND'));
        }
        else {
            $html = plgContentPfcomments::renderEditor($parent);
            $data = array('success' => true, 'message' => '', 'data' => $html);
        }

        // Set the MIME type for JSON output.
        JFactory::getDocument()->setMimeEncoding('application/json');
        JResponse::setHeader('Content-Disposition','attachment;filename="' . $this->view_item. '.json"');

        // Output the JSON data.
        echo json_encode($data);

        JFactory::getApplication()->close();
    }


    /**
     * Function that allows child controller access to model data after the data has been saved.
     *
     * @param     JModel    $model         The data model object.
     * @param     array     $valid_data    The validated data.
     *
     * @return    void
     */
    protected function postSaveHook(JModel &$model, $valid_data)
    {
        $this->id = $model->getState($this->context . '.id');
    }


    /**
     * Method override to check if you can edit an existing record.
     *
     * @param     array     $data    An array of input data.
     * @param     string    $key     The name of the key for the primary key.
     *
     * @return    boolean
     */
    protected function allowEdit($data = array(), $key = 'id')
    {
        // Initialise variables.
        $id     = (int) isset($data[$key]) ? $data[$key] : 0;
        $uid    = JFactory::getUser()->get('id');
        $access = ProjectforkHelperAccess::getActions('comment', $id);

        // Check general edit permission first.
        if ($access->get('comment.edit')) {
            return true;
        }

        // Fallback on edit.own.
        // First test if the permission is available.
        if ($access->get('comment.edit.own')) {
            // Now test the owner is the user.
            $owner = (int) isset($data['created_by']) ? $data['created_by'] : 0;

            if (empty($owner) && $id) {
                // Need to do a lookup from the model.
                $record = $this->getModel()->getItem($id);

                if (empty($record)) return false;

                $owner = $record->created_by;
            }

            // If the owner matches 'me' then do the test.
            if ($owner == $uid) return true;
        }

        // Since there is no asset tracking, revert to the component permissions.
        return parent::allowEdit($data, $key);
    }
}
