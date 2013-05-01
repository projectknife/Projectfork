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
        if ($is_new) return true;

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
}
