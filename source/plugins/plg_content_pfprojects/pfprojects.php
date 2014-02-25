<?php
/**
 * @package      pkg_projectfork
 * @subpackage   plg_content_pfprojects
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2013 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


/**
 * Projectfork Projects Content Plugin Class
 *
 */
class plgContentPFprojects extends JPlugin
{
    /**
     * Supported plugin contexts
     *
     * @var    array
     */
    protected $contexts = array('com_pfprojects.project', 'com_pfprojects.form');

    /**
     * Start date before updating
     *
     * @var    string
     */
    protected $prev_start;

    /**
     * End date before updating
     *
     * @var    string
     */
    protected $prev_end;


    /**
     * "onContentBeforeSave" event handler
     *
     * @param     string     $context    The item context
     * @param     object     $table      The item table object
     * @param     boolean    $is_new     New item indicator (True is new, False is update)
     *
     * @return    boolean                True
     */
    public function onContentBeforeSave($context, $table, $is_new = false)
    {
        // Do nothing if the plugin is disabled
        if (!JPluginHelper::isEnabled('content', 'pfprojects')) {
            return true;
        }

        // Check if the context is supported
        if (!in_array($context, $this->contexts)) {
            return true;
        }

        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->select('start_date, end_date')
              ->from('#__pf_projects')
              ->where('id = ' . (int) $table->id);

        $db->setQuery($query);
        $item = $db->loadObject();

        if (!empty($item)) {
            $this->prev_start = $item->start_date;
            $this->prev_end   = $item->end_date;
        }

        return true;
    }


    /**
     * "onContentAfterSave" event handler
     *
     * @param     string     $context    The item context
     * @param     object     $table      The item table object
     * @param     boolean    $is_new     New item indicator (True is new, False is update)
     *
     * @return    boolean                True
     */
    public function onContentAfterSave($context, $table, $is_new = false)
    {
        // Do nothing if the plugin is disabled
        if (!JPluginHelper::isEnabled('content', 'pfprojects')) return true;

        // Check if the context is supported
        if (!in_array($context, $this->contexts)) return true;

        // Do nothing if this is a new project
        if ($is_new) {
            $app = JFactory::getApplication();

            $context = ($app->isAdmin() ? "com_pfprojects.copy.project.id" : "com_pfprojects.copy.form.id");
            $copy_id = (int) $app->getUserState($context);

            if (!$copy_id) {
                return true;
            }

            $app->setUserState($context, 0);

            $old_table = JTable::getInstance('Project', 'PFtable');

            if (!$old_table->load($copy_id)) {
                return true;
            }

            $this->copyContent($old_table, $table);

            return true;
        }

        $start_changed = ($table->start_date != $this->prev_start);
        $end_changed   = ($table->end_date != $this->prev_end);

        // Update start and end dates on milestones and tasks
        if ($start_changed || $end_changed) {
            $this->updateTimeline($table->id, $table->start_date, $table->end_date);
        }

        return true;
    }


    /**
     * Method to update the start and end dates of milestones and task
     * associated to the project.
     *
     * @param     integer    $project    The project id
     * @param     string     $start      The project start date
     * @param     string     $end        The project end date
     *
     * @return    void
     */
    protected function updateTimeline($project, $start = null, $end = null)
    {
        jimport('projectfork.library');

        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);
        $nd    = $db->getNullDate();

        $has_start  = !(empty($start) || $start == $nd);
        $has_end    = !(empty($end)   || $end == $nd);

        // Do nothing if no start and end date are set
        if (!$has_start && !$has_end) return;

        $this_const = array($start, $end);
        $prev_const = array($this->prev_start, $this->prev_end);

        // Get all milestones
        $query->select('id, start_date, end_date')
              ->from('#__pf_milestones')
              ->where('project_id = ' . (int) $project)
              ->order('id ASC');

        $db->setQuery($query);
        $milestones = (array) $db->loadObjectList('id');

        // Process milestones
        foreach ($milestones AS $i => $item)
        {
            $item_has_start  = !(empty($item->start_date) || $item->start_date == $nd);
            $item_has_end    = !(empty($item->end_date)   || $item->end_date == $nd);

            $milestones[$i]->prev_start_date = $item->start_date;
            $milestones[$i]->prev_end_date   = $item->end_date;

            // Skip if not start and end date are set
            if (!$item_has_start && !$item_has_end) continue;

            $span = array($item->start_date, $item->end_date);

            // Shift dates
            list($new_start, $new_end) = PFDate::shiftTimeline($span, $this_const, $prev_const);

            // Update the database
            $updates = array();

            if ($item_has_start && $item->start_date != $new_start) {
                $updates[] = 'start_date = ' . $db->quote($new_start);

                $milestones[$i]->start_date = $new_start;
            }

            if ($item_has_end && $item->end_date != $new_end) {
                $updates[] = 'end_date = ' . $db->quote($new_end);

                $milestones[$i]->end_date = $new_end;
            }

            if (count($updates)) {
                $query->clear()
                      ->update('#__pf_milestones')
                      ->set(implode(', ', $updates))
                      ->where('id = ' . (int) $item->id);

                $db->setQuery($query);
                $db->execute();
            }
        }

        // Get all tasks
        $query->clear();
        $query->select('id, milestone_id, start_date, end_date')
              ->from('#__pf_tasks')
              ->where('project_id = ' . (int) $project)
              ->order('id ASC');

        $db->setQuery($query);
        $tasks = (array) $db->loadObjectList('id');

        // Process tasks
        foreach ($tasks AS $i => $item)
        {
            $item_has_start  = !(empty($item->start_date) || $item->start_date == $nd);
            $item_has_end    = !(empty($item->end_date)   || $item->end_date == $nd);

            // Skip if not start and end date are set
            if (!$item_has_start && !$item_has_end) continue;

            $span  = array($item->start_date, $item->end_date);
            $ms_id = $item->milestone_id;

            // Prepare constraints
            $c_constraints = array();
            $p_constraints = array();

            if ($ms_id && isset($milestones[$ms_id])) {
                $ms = $milestones[$ms_id];

                $c_constraints[0] = (empty($ms->start_date) || $ms->start_date == $nd) ? $this_const[0] : $ms->start_date;
                $c_constraints[1] = (empty($ms->end_date) || $ms->end_date == $nd)     ? $this_const[1] : $ms->end_date;

                $p_constraints[0] = (empty($ms->prev_start_date) || $ms->prev_start_date == $nd) ? $prev_const[0] : $ms->prev_start_date;
                $p_constraints[1] = (empty($ms->prev_end_date) || $ms->prev_end_date == $nd)     ? $prev_const[1] : $ms->prev_end_date;
            }
            else {
                $c_constraints = $this_const;
                $p_constraints = $prev_const;
            }

            // Shift dates
            list($new_start, $new_end) = PFDate::shiftTimeline($span, $c_constraints, $p_constraints);

            // Update the database
            $updates = array();

            if ($item_has_start && $item->start_date != $new_start) {
                $updates[] = 'start_date = ' . $db->quote($new_start);

                $milestones[$i]->start_date = $new_start;
            }

            if ($item_has_end && $item->end_date != $new_end) {
                $updates[] = 'end_date = ' . $db->quote($new_end);

                $milestones[$i]->end_date = $new_end;
            }

            if (count($updates)) {
                $query->clear()
                      ->update('#__pf_tasks')
                      ->set(implode(', ', $updates))
                      ->where('id = ' . (int) $item->id);

                $db->setQuery($query);
                $db->execute();
            }
        }
    }


    protected function copyContent($old_table, $new_table)
    {
        // Copy milestones
        if (PFApplicationHelper::exists('com_pfmilestones')) {
            $milestones = $this->copyMilestones($old_table, $new_table);
        }
        else {
            $milestones = array();
        }

        // Copy lists and tasks
        if (PFApplicationHelper::exists('com_pftasks')) {
            $lists = $this->copyLists($old_table, $new_table, $milestones);
            $tasks = $this->copyTasks($old_table, $new_table, $milestones, $lists);

            $this->copyTaskDependencies($old_table->id, $new_table->id, $tasks);
        }
        else {
            $lists = array();
            $tasks = array();
        }

        // Copy label references
        $this->copyLabelRefs($old_table->id, $new_table->id, $milestones, $tasks);

        // Copy observers
        $this->copyObservers($old_table->id, $new_table->id, $milestones, $tasks);
    }


    protected function copyMilestones($old_table, $new_table)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->select('id')
              ->from('#__pf_milestones')
              ->where('project_id = ' . (int) $old_table->id)
              ->order('id ASC');

        $db->setQuery($query);
        $cid = $db->loadColumn();

        if (!is_array($cid) || empty($cid)) {
            return array();
        }

        JLoader::register('PFtableMilestone', JPATH_ADMINISTRATOR . '/components/com_pfmilestones/tables/milestone.php');

        $id_map  = array();
        $table   = JTable::getInstance('Milestone', 'PFtable');

        foreach ($cid AS $id)
        {
            if (!$table->load($id)) {
                continue;
            }

            $table->id         = null;
            $table->asset_id   = null;
            $table->project_id = $new_table->id;
            $table->attachment = array();

            // Convert attributes to JRegistry params
            $params = new JRegistry();

            $params->loadString($table->attribs);
            $table->attribs = $params->toArray();

            if (!$table->check()) {
                continue;
            }

            if (!$table->store()) {
                continue;
            }

            $id_map[$id] = $table->id;
        }

        return $id_map;
    }


    protected function copyLists($old_table, $new_table, $milestones)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->select('id')
              ->from('#__pf_task_lists')
              ->where('project_id = ' . (int) $old_table->id)
              ->order('id ASC');

        $db->setQuery($query);
        $cid = $db->loadColumn();

        if (!is_array($cid) || empty($cid)) {
            return array();
        }

        JLoader::register('PFtableTasklist', JPATH_ADMINISTRATOR . '/components/com_pftasks/tables/tasklist.php');

        $id_map  = array();
        $table   = JTable::getInstance('Tasklist', 'PFtable');

        foreach ($cid AS $id)
        {
            if (!$table->load($id)) {
                continue;
            }

            $table->id         = null;
            $table->asset_id   = null;
            $table->project_id = $new_table->id;
            $table->attachment = array();

            if ($table->milestone_id) {
                if (isset($milestones[$table->milestone_id])) {
                    $table->milestone_id = $milestones[$table->milestone_id];
                }
                else {
                    $table->milestone_id = 0;
                }
            }

            // Convert attributes to JRegistry params
            $params = new JRegistry();

            $params->loadString($table->attribs);
            $table->attribs = $params->toArray();

            if (!$table->check()) {
                continue;
            }

            if (!$table->store()) {
                continue;
            }

            $id_map[$id] = $table->id;
        }

        return $id_map;
    }


    protected function copyTasks($old_table, $new_table, $milestones, $lists)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->select('id')
              ->from('#__pf_tasks')
              ->where('project_id = ' . (int) $old_table->id)
              ->order('id ASC');

        $db->setQuery($query);
        $cid = $db->loadColumn();

        if (!is_array($cid) || empty($cid)) {
            return array();
        }

        JLoader::register('PFtableTask', JPATH_ADMINISTRATOR . '/components/com_pftasks/tables/task.php');

        $id_map  = array();
        $table   = JTable::getInstance('Task', 'PFtable');

        foreach ($cid AS $id)
        {
            if (!$table->load($id)) {
                continue;
            }

            $table->id         = null;
            $table->asset_id   = null;
            $table->project_id = $new_table->id;
            $table->attachment = array();

            if ($table->milestone_id) {
                if (isset($milestones[$table->milestone_id])) {
                    $table->milestone_id = $milestones[$table->milestone_id];
                }
                else {
                    $table->milestone_id = 0;
                }
            }

            if ($table->list_id) {
                if (isset($lists[$table->list_id])) {
                    $table->list_id = $lists[$table->list_id];
                }
                else {
                    $table->list_id = 0;
                }
            }

            // Convert attributes to JRegistry params
            $params = new JRegistry();

            $params->loadString($table->attribs);
            $table->attribs = $params->toArray();

            if (!$table->check()) {
                continue;
            }

            if (!$table->store()) {
                continue;
            }

            $id_map[$id] = $table->id;

            // Copy assigned users
            $query->clear();
            $query->select('user_id')
                  ->from('#__pf_ref_users')
                  ->where('item_type = ' . $db->quote('com_pftasks.task'))
                  ->where('item_id = ' . (int) $id)
                  ->order('id ASC');

            $db->setQuery($query);
            $old_users = (array) $db->loadColumn();

            foreach ($old_users AS $uid)
            {
                $obj = new stdClass();
                $obj->id = null;
                $obj->item_type = 'com_pftasks.task';
                $obj->item_id   = $table->id;
                $obj->user_id   = $uid;

                $db->insertObject('#__pf_ref_users', $obj);
            }
        }

        return $id_map;
    }


    protected function copyTaskDependencies($old_id, $new_id, $tasks)
    {
        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->select('task_id, parent_id')
              ->from('#__pf_ref_tasks')
              ->where('project_id = ' . (int) $old_id)
              ->order('id ASC');

        $db->setQuery($query);
        $old_dep = $db->loadObjectList();

        if (!is_array($old_dep) || empty($old_dep)) {
            return false;
        }

        foreach ($old_dep AS $dep)
        {
            $tid = isset($tasks[$dep->task_id])   ? $tasks[$dep->task_id]   : 0;
            $pid = isset($tasks[$dep->parent_id]) ? $tasks[$dep->parent_id] : 0;

            if (!$tid || !$pid) {
                continue;
            }

            $obj = new stdClass();

            $obj->id         = null;
            $obj->project_id = $new_id;
            $obj->task_id    = $tid;
            $obj->parent_id  = $pid;

            $db->insertObject('#__pf_ref_tasks', $obj);
        }

        return true;
    }


    protected function copyLabelRefs($old_id, $new_id, $ms_map, $t_map)
    {
        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);

        // Get old label ids
        $query->select('id')
              ->from('#__pf_labels')
              ->where('project_id = ' . (int) $old_id)
              ->order('id ASC');

        $db->setQuery($query);
        $old_labels = $db->loadColumn();

        if (!is_array($old_labels) || empty($old_labels)) {
            return false;
        }

        // Get new label ids
        $query->clear();
        $query->select('id')
              ->from('#__pf_labels')
              ->where('project_id = ' . (int) $new_id)
              ->order('id ASC');

        $db->setQuery($query);
        $new_labels = $db->loadColumn();

        if (!is_array($new_labels) || empty($new_labels)) {
            return false;
        }

        if (count($old_labels) != count($new_labels)) {
            return false;
        }

        // Map labels
        $label_map = array();

        foreach ($old_labels AS $key => $id)
        {
            $label_map[$id] = $new_labels[$key];
        }

        // Get old refs
        $query->clear();
        $query->select('item_id, item_type, label_id')
              ->from('#__pf_ref_labels')
              ->where('project_id = ' . (int) $old_id)
              ->order('id ASC');

        $db->setQuery($query);
        $old_refs = $db->loadObjectList();

        if (!is_array($old_refs) || empty($old_refs)) {
            return false;
        }

        // Copy refs
        foreach ($old_refs AS $old_ref)
        {
            // Re-assign item id
            switch ($old_ref->item_type)
            {
                case 'com_pfmilestones.milestone':
                    $item_id = isset($ms_map[$old_ref->item_id]) ? $ms_map[$old_ref->item_id] : 0;
                    break;

                case 'com_pftasks.task':
                    $item_id = isset($t_map[$old_ref->item_id]) ? $t_map[$old_ref->item_id] : 0;
                    break;

                default:
                    $item_id = 0;
                    break;
            }

            // Re-assign label id
            $label_id = isset($label_map[$old_ref->label_id]) ? $label_map[$old_ref->label_id] : 0;

            if (!$item_id || !$label_id) {
                continue;
            }

            // Save reference
            $obj = new stdClass();
            $obj->id         = null;
            $obj->project_id = $new_id;
            $obj->item_id    = $item_id;
            $obj->item_type  = $old_ref->item_type;
            $obj->label_id   = $label_id;

            $db->insertObject('#__pf_ref_labels', $obj);
        }

        return true;
    }


    protected function copyObservers($old_id, $new_id, $ms_map, $t_map)
    {
        $user  = JFactory::getUser();
        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);

        // Get old observers
        $query->clear();
        $query->select('user_id, item_type, item_id')
              ->from('#__pf_ref_observer')
              ->where('project_id = ' . (int) $old_id);

        $db->setQuery($query);
        $old_obs = $db->loadObjectList();

        if (!is_array($old_obs) || empty($old_obs)) {
            return false;
        }

        // Copy obs
        foreach ($old_obs AS $old_ob)
        {
            // Re-assign item id
            switch ($old_ob->item_type)
            {
                case 'com_pfprojects.project':
                    if ($old_ob->user_id == $user->id) {
                        $item_id = 0;
                    }
                    else {
                        $item_id = $new_id;
                    }
                    break;

                case 'com_pfmilestones.milestone':
                    $item_id = isset($ms_map[$old_ob->item_id]) ? $ms_map[$old_ob->item_id] : 0;
                    break;

                case 'com_pftasks.task':
                    $item_id = isset($t_map[$old_ob->item_id]) ? $t_map[$old_ob->item_id] : 0;
                    break;

                default:
                    $item_id = 0;
                    break;
            }

            if (!$item_id) {
                continue;
            }

            // Save
            $obj = new stdClass();
            $obj->user_id    = $old_ob->user_id;
            $obj->item_type  = $old_ob->item_type;
            $obj->item_id    = $item_id;
            $obj->project_id = $new_id;

            $db->insertObject('#__pf_ref_observer', $obj);
        }

        return true;
    }
}
