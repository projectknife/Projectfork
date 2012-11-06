<?php
/**
 * @package      Projectfork
 * @subpackage   Tasks
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


jimport('joomla.application.component.modellist');


/**
 * Methods supporting a list of task dependencies.
 *
 */
class PFtasksModelTaskRefs extends JModelList
{
    /**
     * Constructor
     *
     * @param    array          An optional associative array of configuration settings.
     * @see      jcontroller
     */
    public function __construct($config = array())
    {
        parent::__construct($config);
    }


    /**
     * Method to get a list of task dependencies.
     *
     * @param     integer    $task_id      Optional task id
     *
     * @return    mixed                    An array of data items on success, false on failure.
     */
    public function getItems($task_id = 0, $diagnostic = false)
    {
        $task_id = ((int) $task_id > 0 ? (int) $task_id : $this->getState('item.id'));

        // Make sure we have an item to select from
        if (empty($task_id)) {
            $this->setError(JText::_('COM_PROJECTFORK_WARNING_DEPENDENCY_NO_ITEM_REFERENCE'));
            return array();
        }

        $db     = $this->getDbo();
        $query  = $db->getQuery(true);

        if ($diagnostic) {
            $fields = 'a.parent_id';
        }
        else {
            $fields = 'a.id, a.task_id, a.parent_id, t.title, t.alias, '
                    . 't.project_id, t.milestone_id, t.list_id, t.access, t.complete';
        }

        $query->select($fields)
              ->from('#__pf_ref_tasks AS a')
              ->where('a.task_id = ' . $db->quote((int) $task_id));

        if (!$diagnostic) {
            $query->join('left', '#__pf_tasks AS t ON (t.id = a.parent_id)')
                  ->group('a.parent_id');
        }

        $db->setQuery((string) $query);
        $items = (array) ($diagnostic ? $db->loadColumn() : $db->loadObjectList());

        if ($db->getError()) {
            $this->setError($db->getErrorMsg());
            return $list;
        }

        return $items;
    }


    /**
     * Method to get a list task connections.
     *
     * @param     integer    $task_id      Optional task id
     *
     * @return    mixed                    An array of data items on success, false on failure.
     */
    public function getConnections($task_id = 0)
    {
        $task_id = ((int) $task_id > 0 ? (int) $task_id : $this->getState('item.id'));

        // Make sure we have an item to select from
        if (empty($task_id)) {
            $this->setError(JText::_('COM_PROJECTFORK_WARNING_DEPENDENCY_NO_ITEM_REFERENCE'));
            return array();
        }

        $db    = $this->getDbo();
        $query = $db->getQuery(true);

        $query->select('a.id, a.task_id, a.parent_id')
              ->from('#__pf_ref_tasks AS a')
              ->where('a.parent_id = ' . $db->quote((int) $task_id));

        $db->setQuery((string) $query);
        $items = (array) $db->loadObjectList();

        if ($db->getError()) {
            $this->setError($db->getErrorMsg());
            return $list;
        }

        return $items;
    }


    /**
     * Method to save task dependencies
     *
     * @param     array      $data          $the repo items
     * @param     integer    $task_id       The task id
     * @param     integer    $project_id    The project id to which the item belongs
     *
     * @return    boolean                   True on success, False on error
     */
    public function save($data = array(), $task_id = 0, $project_id = 0)
    {
        $task_id    = ((int) $task_id > 0    ? (int) $task_id    : $this->getState('item.id'));
        $project_id = ((int) $project_id > 0 ? (int) $project_id : $this->getState('item.project'));
        $success    = true;

        // Check if an item is set
        if (!$task_id || !$project_id) {
            $this->setError(JText::_('COM_PROJECTFORK_WARNING_DEPENDENCY_NO_ITEM_REFERENCE'));
            return false;
        }

        if (!is_array($data)) {
            $data = array();
        }

        // Load the existing dependencies
        $taskref  = $this->getInstance('TaskRef', 'PFtasksModel', array('ignore_request' => true));
        $existing = $this->getItems($task_id);
        $delete   = array();
        $ids      = array();
        $keep     = array();

        // Get the IDs
        foreach($data AS $id)
        {
            $id = (int) $id;

            if (!in_array($id, $ids) && $id > 0) {
                $ids[] = (int) $id;
            }
        }

        // Filter out items that are no longer there
        foreach ($existing AS $item)
        {
            $id = (int) $item->parent_id;

            if (!in_array($id, $ids)) {
                $delete[] = $item->id;
            }
            else {
                $keep[] = $id;
            }
        }

        // Save dependencies
        $looped = array();

        foreach ($data AS $i => $id)
        {
            $id = (int) $id;

            if ($id <= 0 || in_array($id, $keep) || in_array($id, $looped)) continue;

            $looped[] = $id;

            $item_data = array(
                'id'         => 0,
                'project_id' => $project_id,
                'task_id'    => $task_id,
                'parent_id'  => $id
            );

            if (!$taskref->save($item_data)) {
                $this->setError($taskref->getError());
                $success = false;
            }
        }

        // Delete dependencies
        if (count($delete)) {
            if (!$taskref->delete($delete)) {
                $this->setError($taskref->getError());
                $success = false;
            }
        }

        return $success;
    }


    /**
     * Method to auto-populate the model state.
     * Note. Calling getState in this method will result in recursion.
     *
     * @return    void
     */
    protected function populateState($ordering = null, $direction = null)
    {
        // Get potential form data
        $form = JRequest::getVar('jform', array(), 'post', 'array');

        // Item id
        $value = JRequest::getUint('id');

        if (!$value) {
            if (isset($form['id'])) {
                $value = (int) $form['id'];
            }
        }

        $this->setState('item.id', $value);

        // Project id
        $value = (int) $this->getUserStateFromRequest('com_projectfork.project.active.id', 'filter_project', '');

        if (!$value) {
            if (isset($form['project_id'])) {
                $value = (int) $form['project_id'];
            }
        }

        $this->setState('item.project', $value);
    }
}
