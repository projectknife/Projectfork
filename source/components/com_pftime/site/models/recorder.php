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


jimport('joomla.application.component.modellist');


/**
 * Projectfork Time Recorder Model
 *
 */
class PFtimeModelRecorder extends JModelList
{
    /**
     * Constructor.
     *
     * @param    array    $config    An optional associative array of configuration settings.
     */
    public function __construct($config = array())
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array('a.id');
        }

        parent::__construct($config);
    }


    /**
     * Method to get a list of items.
     *
     * @return    mixed    $items    An array of objects on success, false on failure.
     */
    public function getItems()
    {
        $items = (array) $this->getState('list.items', array());
        $model = JModelLegacy::getInstance('Form', 'PFtimeModel');

        foreach ($items AS &$item)
        {
            $item['data'] = $model->getItem((int) $item['id']);
        }

        return $items;
    }


    /**
     * Method to add one or more tasks to the recorder
     *
     * @param     array      $cid    The tasks to add
     *
     * @return    boolean            True on success, False on error
     */
    public function addItems($cid)
    {
        $user   = JFactory::getUser();
        $app    = JFactory::getApplication();
        $items  = $app->getUserState('com_pftime.recorder.data');
        $levels = $user->getAuthorisedViewLevels();
        $admin  = $user->authorise('core.admin');
        $query  = $this->_db->getQuery(true);
        $form   = JModelLegacy::getInstance('Form', 'PFtimeModel');
        $ids    = array();

        if (!is_array($items)) {
            $app->setUserState('com_pftime.recorder', null);
            $app->setUserState('com_pftime.recorder.data', array());
            $items = array();
        }

        JArrayHelper::toInteger($cid);

        // Get all the task ids
        foreach ($items AS $item)
        {
            $id = $item['task_id'];
            $ids[$id] = $item;
        }

        foreach ($cid AS $id)
        {
            // Check if not already in list
            if (isset($ids[$id])) {
                if (!$ids[$id]['pause']) continue;

                // Resume tracking of this task
                foreach ($items AS $i => $item)
                {
                    if ($item['task_id'] != $id) continue;

                    unset($items[$i]);
                    $item['pause'] = 0;
                    $items[] = $item;
                }

                continue;
            }

            // Validate access if not an admin
            if (!$admin) {
                $query->clear()
                      ->select('access')
                      ->from('#__pf_tasks')
                      ->where('id = ' . $this->_db->quote($id));

                $this->_db->setQuery($query);

                if (!in_array(intval($this->_db->loadResult()), $levels)) {
                    JError::raiseWarning(403, JText::_('JERROR_ALERTNOAUTHOR'));
                    return false;
                }
            }

            // Get the task project id and rate
            $query->clear()
                  ->select('project_id, rate')
                  ->from('#__pf_tasks')
                  ->where('id = ' . $this->_db->quote($id));

            $this->_db->setQuery($query);
            $obj = $this->_db->loadObject();

            if (!$obj) continue;

            $data = array();
            $data['id']         = null;
            $data['project_id'] = (int) $obj->project_id;
            $data['task_id']    = $id;
            $data['log_time']   = 1;
            $data['rate']       = $obj->rate;
            $data['billable']   = 1;

            // Reset the form
            $form->setState($form->getName() . '.id', 0);
            $form->setState($form->getName() . '.new', true);

            // Save the record
            if (!$form->save($data)) {
                $this->setError($form->getError());
                return false;
            }

            $rec_id = $form->getState($form->getName() . '.id');
            if (!$rec_id) continue;

            // Check-out the record
            $form->checkout($rec_id);

            // Add to list
            $items[] = array(
                'id'      => $rec_id,
                'task_id' => $id,
                'pause'   => 0,
                'time'    => 1
            );
        }

        // Update session data
        $app->setUserState('com_pftime.recorder.data', $items);

        return true;
    }


    /**
     * Method to toggle the pause state one or more tasks in the recorder
     *
     * @var       array      $cid    The tasks to toggle
     *
     * @return    boolean            True on success, False on error
     */
    public function pause($cid)
    {
        $app   = JFactory::getApplication();
        $items = $app->getUserState('com_pftime.recorder.data');

        if (!is_array($items)) return false;

        JArrayHelper::toInteger($cid);

        foreach ($cid AS $i => $id)
        {
            foreach ($items AS &$item)
            {
                if ($item['id'] != $id) continue;

                // Toggle the pause state
                $item['pause'] = ($item['pause'] > 0 ? 0 : time());
            }
        }

        // Update session data
        $app->setUserState('com_pftime.recorder.data', $items);

        return true;
    }


    /**
     * Method to update a one or more time records
     *
     * @param     array      $cid    The records to update
     *
     * @return    boolean            True on success, False on error
     */
    public function save($cid)
    {
        $app = JFactory::getApplication();

        $descriptions = $app->input->post->get('description', array(), 'array');
        $rates        = $app->input->post->get('rate', array(), 'array');
        $billables    = $app->input->post->get('billable', array(), 'array');
        $filter       = JFilterInput::getInstance();

        JArrayHelper::toInteger($cid);

        foreach ($cid AS $i => $id)
        {
            $upd = new stdClass();
            $upd->id = $id;
            $upd->description = $filter->clean((isset($descriptions[$i]) ? $descriptions[$i] : ''));
            $upd->rate        = $filter->clean((isset($rates[$i]) ? $rates[$i] : '0.00'));
            $upd->billable    = (isset($billables[$i]) ? (int) $billables[$i] : 0);

            if (!$this->_db->updateObject('#__pf_timesheet', $upd, 'id', false)) {
                $this->setError($this->_db->getError());
                return false;
            }
        }

        return true;
    }


    /**
     * Method to remove one or more records from the recorder
     *
     * @param     array      $cid         The items to remove
     * @param     integer    $complete    Whether to complete the tasks or not
     *
     * @return    boolean                 True on success, False on error
     */
    public function delete($cid, $complete = 0)
    {
        $app   = JFactory::getApplication();
        $items = $app->getUserState('com_pftime.recorder.data');
        $model = JModelLegacy::getInstance('TaskForm', 'PFtasksModel');
        $query = $this->_db->getQuery(true);
        $tasks = array();

        JArrayHelper::toInteger($cid);

        foreach ($items AS $i => &$rec)
        {
            $id  = (int) $rec['id'];
            $tid = (int) $rec['task_id'];

            if (in_array($id, $cid)) {
                unset($items[$i]);

                if ($complete) {
                    $query->clear()
                          ->select('complete')
                          ->from('#__pf_tasks')
                          ->where('id = ' . (int) $tid);

                    $this->_db->setQuery($query);
                    if (!$this->_db->loadResult()) $tasks[] = $tid;
                }
            }
        }

        if (count($tasks)) {
            $model->complete($tasks);
        }

        // Update session data
        $app->setUserState('com_pftime.recorder.data', $items);

        return true;
    }


    /**
     * Method to punch-in items that are currently in the recorder,
     * increasing the recorded time by 60 secs
     *
     * @return    boolean    True on success, False on error
     */
    public function punch()
    {
        $user   = JFactory::getUser();
        $app    = JFactory::getApplication();
        $items  = $app->getUserState('com_pftime.recorder.data');
        $time   = (int) $app->getUserState('com_pftime.recorder.time');
        $levels = $user->getAuthorisedViewLevels();
        $admin  = $user->authorise('core.admin');
        $model  = JModelLegacy::getInstance('Form', 'PFtimeModel');
        $date   = JFactory::getDate()->toSql();

        // Make sure we have items
        if (!is_array($items) || count($items) == 0) {
            return true;
        }

        // Check when we punched in the last time
        if (time() - $time < 60 && $time != 0) {
            return true;
        }

        $app->setUserState('com_pftime.recorder.time', time());

        foreach ($items AS &$rec)
        {
            $id    = $rec['id'];
            $pause = $rec['pause'];

            // Skip paused
            if ($pause) continue;

            $item = $model->getItem($id);

            if (!$item) {
                $this->setError($model->getError());
                return false;
            }

            // Validate access if not an admin
            if (!$admin && !in_array(intval($item->access), $levels)) {
                continue;
            }

            // Manually update the record because we dont want
            // to trigger plugins every minute
            $upd = new stdClass();

            $upd->id          = $item->id;
            $upd->log_time    = ($item->log_time * 60) + 60;
            $upd->modified    = $date;
            $upd->modified_by = $user->get('id');
            $upd->checked_out = $user->get('id');
            $upd->checked_out_time = $date;

            if (!$this->_db->updateObject('#__pf_timesheet', $upd, 'id', false)) {
                $this->setError($this->_db->getError());
                return false;
            }

            // Update the record time
            $rec['time'] = $upd->log_time;
        }

        // Update session data
        $app->setUserState('com_pftime.recorder.data', $items);

        return true;
    }


    /**
     * Method to auto-populate the model state.
     * Note. Calling getState in this method will result in recursion.
     *
     * @return    void
     */
    protected function populateState($ordering = 'a.id', $direction = 'asc')
    {
        $app = JFactory::getApplication();

        // Get Recorder data
        $items = (array) $app->getUserState('com_pftime.recorder.data');
        $this->setState('list.items', array_reverse($items));

        // Params
        $value = $app->getParams();
        $this->setState('params', $value);

        // Call parent method
        parent::populateState($ordering, $direction);
    }
}
