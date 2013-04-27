<?php
/**
 * @package      pkg_projectfork
 * @subpackage   plg_content_pfmilestones
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2013 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


/**
 * Projectfork Milestones Content Plugin Class
 *
 */
class plgContentPFmilestones extends JPlugin
{
    /**
     * Supported plugin contexts
     *
     * @var    array
     */
    protected $contexts = array(
        'com_pfprojects.project', 'com_pfprojects.form',
        'com_pfmilestones.milestone', 'com_pfmilestones.form'
    );

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
        if (!JPluginHelper::isEnabled('content', 'pfmilestones')) {
            return true;
        }

        // Check if the context is supported
        if (!in_array($context, $this->contexts)) {
            return true;
        }

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
        if (!JPluginHelper::isEnabled('content', 'pfmilestones')) return true;

        // Check if the context is supported
        if (!in_array($context, $this->contexts)) return true;

        // Do nothing if this is a new item
        if ($is_new) return true;


        if (in_array($context, array('com_pfprojects.project', 'com_pfprojects.form'))) {
            // Update access
            $this->updateAccess($table->id, $table->access);

            // Update publishing state
            $this->updatePubState($table->id, $table->state);
        }


        if (in_array($context, array('com_pfmilestones.milestone', 'com_pfmilestones.form'))) {
            $start_changed = ($table->start_date != $this->prev_start);
            $end_changed   = ($table->end_date != $this->prev_end);

            // Update start and end dates on milestones and tasks
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
        if (!JPluginHelper::isEnabled('content', 'pfmilestones')) return true;

        if (in_array($context, array('com_pfprojects.project', 'com_pfprojects.form'))) {
            // Update publishing state
            foreach ($pks AS $id)
            {
                $this->updatePubState($id, $value);
            }
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
        if (!JPluginHelper::isEnabled('content', 'pfmilestones')) return true;

        if (in_array($context, array('com_pfprojects.project', 'com_pfprojects.form'))) {
            // Delete milestones
            $this->deleteFromProject($table->id);
        }

        return true;
    }


    /**
     * Method to delete all milestones from the given project
     *
     * @param     integer    $id    The project
     *
     * @return    void
     */
    protected function deleteFromProject($id)
    {
        static $imported = false;

        if (!$imported) {
            jimport('projectfork.library');
            JLoader::register('PFtableMilestone', JPATH_ADMINISTRATOR . '/components/com_pfmilestones/tables/milestone.php');

            $imported = true;
        }

        $table = JTable::getInstance('Milestone', 'PFtable');
        if (!$table) return;

        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->select('id')
              ->from('#__pf_milestones')
              ->where('project_id = ' . (int) $id);

        $db->setQuery($query);
        $pks = (array) $db->loadColumn();

        foreach ($pks AS $pk)
        {
            $table->delete((int) $pk);
        }
    }


    /**
     * Method to update the publishing state of all milestones
     * associated with the given project
     *
     * @param     integer    $project    The project
     * @param     integer    $state      The new publishing state
     *
     * @return    void
     */
    protected function updatePubState($project, $state)
    {
        // Do nothing on publish
        if ($state == '1') return;

        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->update('#__pf_milestones')
              ->set('state = ' . $state)
              ->where('project_id = ' . (int) $project);

        if ($state == 0) {
            $query->where('state NOT IN(-2,0,2)');
        }
        else {
            $query->where('state <> -2');
        }

        $db->setQuery($query);
        $db->execute();
    }


    /**
     * Method to update the access level of all milestones
     * associated with the given project
     *
     * @param     integer    $project    The project
     * @param     integer    $access     The access level
     *
     * @return    void
     */
    protected function updateAccess($project, $access)
    {
        jimport('projectfork.library');

        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);

        $allowed = PFAccessHelper::getAccessTree($access);

        $query->update('#__pf_milestones')
              ->set('access = ' . (int) $access)
              ->where('project_id = ' . (int) $project);

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
     * @param     string     $start    The project start date
     * @param     string     $end      The project end date
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
}
