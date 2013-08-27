<?php
/**
 * @package      pkg_projectfork
 * @subpackage   plg_content_pftasks
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2013 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


/**
 * Projectfork Tasks Content Plugin Class
 *
 */
class plgContentPFtasks extends JPlugin
{
    /**
     * Supported plugin contexts
     *
     * @var    array
     */
    protected $contexts = array(
        'com_pfprojects.project', 'com_pfprojects.form',
        'com_pfmilestones.milestone', 'com_pfmilestones.form',
        'com_pftasks.tasklist', 'com_pftasks.tasklistform'
    );

    /**
     * Milestone start date before updating
     *
     * @var    string
     */
    protected $prev_start;

    /**
     * Milestone end date before updating
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
        if (!JPluginHelper::isEnabled('content', 'pftasks')) return true;

        // Check if the context is supported
        if (!in_array($context, $this->contexts)) return true;

        // Do nothing if this is a new item
        if ($is_new) return true;

        if (in_array($context, array('com_pfmilestones.milestone', 'com_pfmilestones.form'))) {
            $db    = JFactory::getDbo();
            $query = $db->getQuery(true);

            $query->select('start_date, end_date')
                  ->from('#__pf_milestones')
                  ->where('id = ' . (int) $table->id);

            $db->setQuery($query);
            $item = $db->loadObject();

            if (!empty($item)) {
                $this->prev_start = $item->start_date;
                $this->prev_end   = $item->end_date;
            }
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
        if (!JPluginHelper::isEnabled('content', 'pftasks')) return true;

        // Check if the context is supported
        if (!in_array($context, $this->contexts)) return true;

        // Do nothing if this is a new item
        if ($is_new) return true;

        $context = $this->unalias($context);

        if (in_array($context, array('com_pfprojects.project', 'com_pfmilestones.milestone', 'com_pftasks.tasklist'))) {
            // Update access
            $this->updateAccess($context, $table->id, $table->access);

            // Update publishing state
            $this->updatePubState($context, $table->id, $table->state);

            if ($context == 'com_pfprojects.project') {
                // Update asset hierarchy
                $this->updateParentAsset($table->id);
            }
        }


        if ($context == 'com_pfmilestones.milestone') {
            $start_changed = ($table->start_date != $this->prev_start);
            $end_changed   = ($table->end_date != $this->prev_end);

            // Update start and end dates on tasks
            if ($start_changed || $end_changed) {
                $this->updateTimeline($table->id, $table->start_date, $table->end_date);
            }
        }


        return true;
    }


    /**
     * "onContentChangeState" event handler
     *
     * @param     string     $context    The item context
     * @param     array      $pks        The item id's whose state was changed
     * @param     integer    $value      New state to which the items were changed
     *
     * @return    boolean                True
     */
    public function onContentChangeState($context, $pks, $value)
    {
        // Do nothing if the plugin is disabled
        if (!JPluginHelper::isEnabled('content', 'pftasks')) return true;

        // Check if the context is supported
        if (!in_array($context, $this->contexts)) return true;

        $context = $this->unalias($context);

        // Update publishing state
        foreach ($pks AS $id)
        {
            $this->updatePubState($context, $id, $value);
        }

        return true;
    }


    /**
     * "onContentAfterDelete" event handler
     *
     * @param     string     $context    The item context
     * @param     object     $table      The item table object
     *
     * @return    boolean                True
     */
    public function onContentAfterDelete($context, $table)
    {
        // Do nothing if the plugin is disabled
        if (!JPluginHelper::isEnabled('content', 'pftasks')) return true;

        // Check if the context is supported
        if (!in_array($context, $this->contexts)) return true;

        $context = $this->unalias($context);

        $this->deleteFromContext($context, $table->id);

        if ($context == 'com_pfprojects.project') {
            $this->deleteProjectAsset($table->id);
        }

        return true;
    }


    /**
     * Method to unalias the context
     *
     * @param string $context The context alias
     *
     * @return string $context The actual context
     */
    protected function unalias($context)
    {
        switch ($context)
        {
            case 'com_pfprojects.project':
            case 'com_pfprojects.form':
                return 'com_pfprojects.project';
                break;

            case 'com_pfmilestones.milestone':
            case 'com_pfmilestones.form':
                return 'com_pfmilestones.milestone';
                break;

            case 'com_pftasks.tasklist':
            case 'com_pftasks.tasklistform':
                return 'com_pftasks.tasklist';
                break;
        }

        return $context;
    }


    /**
     * Method to delete all task lists and tasks from the given context
     *
     * @param     string $context The context
     * @param     integer    $id    The project
     *
     * @return    void
     */
    protected function deleteFromContext($context, $id)
    {
        static $imported = false;

        if (!$imported) {
            jimport('projectfork.library');
            JLoader::register('PFtableTask', JPATH_ADMINISTRATOR . '/components/com_pftasks/tables/task.php');
            JLoader::register('PFtableTasklist', JPATH_ADMINISTRATOR . '/components/com_pftasks/tables/tasklist.php');

            $imported = true;
        }

        $task_table = JTable::getInstance('Task', 'PFtable');
        $list_table = JTable::getInstance('Tasklist', 'PFtable');

        if (!$task_table || !$list_table) return;

        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);
        $lists = array();
        $tasks = array();

        $fields = array(
            'com_pfprojects.project'     => 'project_id',
            'com_pfmilestones.milestone' => 'milestone_id',
            'com_pftasks.tasklist'       => 'list_id'
        );

        // Get all tasks
        $query->select('id')
              ->from('#__pf_tasks')
              ->where($fields[$context] . ' = ' . (int) $id);

        $db->setQuery($query);
        $tasks = (array) $db->loadColumn();

        // Get all task lists
        if ($context != 'com_pftasks.tasklist') {
            $query->clear();
            $query->select('id')
                  ->from('#__pf_task_lists')
                  ->where($fields[$context] . ' = ' . (int) $id);

            $db->setQuery($query);
            $lists = (array) $db->loadColumn();
        }

        // Delete tasks
        foreach ($tasks AS $pk)
        {
            $task_table->delete((int) $pk);
        }

        // Delete lists
        foreach ($lists AS $pk)
        {
            $list_table->delete((int) $pk);
        }
    }


    /**
     * Method to update the publishing state of all tasks and task lists
     * associated with the given context
     *
     * @param     string $context The context name
     * @param     integer    $id    The context item id
     * @param     integer    $state      The new publishing state
     *
     * @return    void
     */
    protected function updatePubState($context, $id, $state)
    {
        // Do nothing on publish
        if ($state == '1') return;

        $fields = array(
            'com_pfprojects.project'     => 'project_id',
            'com_pfmilestones.milestone' => 'milestone_id',
            'com_pftasks.tasklist'       => 'list_id'
        );

        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);

        // Update tasks
        $query->update('#__pf_tasks')
              ->set('state = ' . $state)
              ->where($fields[$context] . ' = ' . (int) $id)
              ->where(($state == 0 ? 'state NOT IN(-2,0,2)' : 'state <> -2'));

        $db->setQuery($query);
        $db->execute();

        // Update lists
        if ($context != 'com_pftasks.tasklist') {
            $query->clear();
            $query->update('#__pf_task_lists')
                  ->set('state = ' . $state)
                  ->where($fields[$context] . ' = ' . (int) $id)
                  ->where(($state == 0 ? 'state NOT IN(-2,0,2)' : 'state <> -2'));

            $db->setQuery($query);
            $db->execute();
        }
    }


    /**
     * Method to update the access level of all lists and tasks
     * associated with the given context
     *
     * @param    string $context The context name
     * @param     integer    $id    The context id
     * @param     integer    $access     The access level
     *
     * @return    void
     */
    protected function updateAccess($context, $id, $access)
    {
        jimport('projectfork.library');

        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);

        $allowed = PFAccessHelper::getAccessTree($access);

        $fields = array(
            'com_pfprojects.project'     => 'project_id',
            'com_pfmilestones.milestone' => 'milestone_id',
            'com_pftasks.tasklist'       => 'list_id'
        );

        // Update tasks
        $query->update('#__pf_tasks')
              ->set('access = ' . (int) $access)
              ->where($fields[$context] . ' = ' . (int) $id);

        if (count($allowed) == 1) {
            $query->where('access <> ' . (int) $allowed[0]);
        }
        elseif (count($allowed) > 1) {
            $query->where('access NOT IN( ' . implode(', ', $allowed) . ')');
        }

        $db->setQuery($query);
        $db->execute();


        if ($context == 'com_pftasks.tasklist') return;

        // Update task lists
        $query->clear();
        $query->update('#__pf_task_lists')
              ->set('access = ' . (int) $access)
              ->where($fields[$context] . ' = ' . (int) $id);

        if (count($allowed) == 1) {
            $query->where('access <> ' . (int) $allowed[0]);
        }
        elseif (count($allowed) > 1) {
            $query->where('access NOT IN( ' . implode(', ', $allowed) . ')');
        }

        $db->setQuery($query);
        $db->execute();
    }


    /**
     * Method to update the start and end dates of tasks
     * associated with the milestone.
     *
     * @param     integer    $id       The milestone id
     * @param     string     $start    The milestone start date
     * @param     string     $end      The milestone end date
     *
     * @return    void
     */
    protected function updateTimeline($id, $start = null, $end = null)
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

        // Get all tasks
        $query->select('id, start_date, end_date')
              ->from('#__pf_tasks')
              ->where('milestone_id = ' . (int) $id)
              ->order('id ASC');

        $db->setQuery($query);
        $tasks = (array) $db->loadObjectList('id');

        // Process tasks
        foreach ($tasks AS $i => $item)
        {
            $item_has_start = !(empty($item->start_date) || $item->start_date == $nd);
            $item_has_end   = !(empty($item->end_date)   || $item->end_date == $nd);

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
                      ->update('#__pf_tasks')
                      ->set(implode(', ', $updates))
                      ->where('id = ' . (int) $item->id);

                $db->setQuery($query);
                $db->execute();
            }
        }
    }


    /**
     * Method to change the hierarchy of all task assets
     *
     * @param     integer    $project    The project id
     *
     * @return    boolean                True on success
     */
    protected function updateParentAsset($project)
    {
        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);

        // Find the component asset id
        $query->select('id')
              ->from('#__assets')
              ->where('name = ' . $db->quote('com_pftasks'));

        $db->setQuery($query);
        $com_asset = (int) $db->loadResult();


        if (!$com_asset) return false;

        // Find the project asset id
        $query->clear();
        $query->select('id')
              ->from('#__assets')
              ->where('name = ' . $db->quote('com_pftasks.project.' . $project));

        $db->setQuery($query);
        $project_asset = (int) $db->loadResult();

        if (!$project_asset) return false;

        // Find all task assets that need to be updated
        $query->clear();
        $query->select('a.asset_id')
              ->from('#__pf_tasks AS a')
              ->join('INNER', '#__assets AS s ON s.id = a.asset_id')
              ->where('a.project_id = ' . (int) $project)
              ->where('s.parent_id = ' . (int) $com_asset)
              ->order('a.id ASC');

        $db->setQuery($query);
        $pks = $db->loadColumn();

        if (!is_array($pks)) $pks = array();

        // Update each asset
        foreach ($pks AS $pk)
        {
            $asset = JTable::getInstance('Asset', 'JTable', array('dbo' => $db));

            $asset->load($pk);

            $asset->setLocation($project_asset, 'last-child');
            $asset->parent_id = $project_asset;

            if (!$asset->check() || !$asset->store(false)) {
                return false;
            }
        }

        // Find all task list assets that need to be updated
        $query->clear();
        $query->select('a.asset_id')
              ->from('#__pf_task_lists AS a')
              ->join('INNER', '#__assets AS s ON s.id = a.asset_id')
              ->where('a.project_id = ' . (int) $project)
              ->where('s.parent_id != ' . (int) $project_asset)
              ->order('a.id ASC');

        $db->setQuery($query);
        $pks = $db->loadColumn();

        if (!is_array($pks)) $pks = array();

        // Update each asset
        foreach ($pks AS $pk)
        {
            $asset = JTable::getInstance('Asset', 'JTable', array('dbo' => $db));

            $asset->load($pk);

            $asset->setLocation($project_asset, 'last-child');
            $asset->parent_id = $project_asset;

            if (!$asset->check() || !$asset->store(false)) {
                return false;
            }
        }

        return true;
    }


    /**
     * Method to delete a component project asset
     *
     * @param     integer    $project    The project id
     *
     * @return    boolean                True on success
     */
    protected function deleteProjectAsset($project)
    {
        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->delete('#__assets')
              ->where('name = ' . $db->quote('com_pftasks.project.' . (int) $project));

        $db->setQuery($query);

        if (!$db->execute()) return false;

        return true;
    }
}
