<?php
/**
 * @package      pkg_projectfork
 * @subpackage   plg_content_pftime
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
class plgContentPFtime extends JPlugin
{
    /**
     * Supported plugin contexts
     *
     * @var    array
     */
    protected $contexts = array(
        'com_pfprojects.project', 'com_pfprojects.form'
    );


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
        if (!JPluginHelper::isEnabled('content', 'pftime')) return true;

        // Check if the context is supported
        if (!in_array($context, $this->contexts)) return true;

        // Do nothing if this is a new item
        if ($is_new) return true;

        // Update access
        $this->updateAccess($table->id, $table->access);

        // Update publishing state
        $this->updatePubState($table->id, $table->state);

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
        if (!JPluginHelper::isEnabled('content', 'pftime')) return true;

        // Check if the context is supported
        if (!in_array($context, $this->contexts)) return true;

        // Update publishing state
        foreach ($pks AS $id)
        {
            $this->updatePubState($id, $value);
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
        if (!JPluginHelper::isEnabled('content', 'pftime')) return true;

        // Check if the context is supported
        if (!in_array($context, $this->contexts)) return true;

        // Delete milestones
        $this->deleteFromProject($table->id);

        return true;
    }


    /**
     * Method to delete all time records from the given project
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
            JLoader::register('PFtableTime', JPATH_ADMINISTRATOR . '/components/com_pftime/tables/time.php');

            $imported = true;
        }

        $table = JTable::getInstance('Time', 'PFtable');
        if (!$table) return;

        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->select('id')
              ->from('#__pf_timesheet')
              ->where('project_id = ' . (int) $id);

        $db->setQuery($query);
        $pks = (array) $db->loadColumn();

        foreach ($pks AS $pk)
        {
            $table->delete((int) $pk);
        }
    }


    /**
     * Method to update the publishing state of all time records
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

        $query->update('#__pf_timesheet')
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
     * Method to update the access level of all time records
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

        $query->update('#__pf_timesheet')
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
}
