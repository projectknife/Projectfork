<?php
/**
 * @package      pkg_projectfork
 * @subpackage   com_pfmilestones
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2013 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


jimport('joomla.application.component.modeladmin');


/**
 * Item Model for a milestone form.
 *
 */
class PFmilestonesModelMilestone extends JModelAdmin
{
    /**
     * The prefix to use with controller messages.
     *
     * @var    string
     */
    protected $text_prefix = 'COM_PROJECTFORK_MILESTONE';


    /**
     * Constructor.
     *
     * @param    array          $config    An optional associative array of configuration settings.
     *
     * @see      jcontroller
     */
    public function __construct($config = array())
    {
        // Register dependencies

        parent::__construct($config);
    }


    /**
     * Returns a Table object, always creating it.
     *
     * @param     string    The table type to instantiate
     * @param     string    A prefix for the table class name. Optional.
     * @param     array     Configuration array for model. Optional.
     *
     * @return    jtable    A database object
     */
    public function getTable($type = 'Milestone', $prefix = 'PFtable', $config = array())
    {
        return JTable::getInstance($type, $prefix, $config);
    }


    /**
     * Method to get a single record.
     *
     * @param     integer    $pk      The id of the primary key.
     *
     * @return    mixed      $item    Object on success, false on failure.
     */
    public function getItem($pk = null)
    {
        $pk    = (!empty($pk)) ? (int) $pk : (int) $this->getState($this->getName() . '.id');
        $table = $this->getTable();

        if ($pk > 0) {
            // Attempt to load the row.
            $return = $table->load($pk);

            // Check for a table object error.
            if ($return === false && $table->getError()) {
                $this->setError($table->getError());
                return false;
            }
        }

        // Convert to the JObject before adding other data.
        $properties = $table->getProperties(1);
        $item = JArrayHelper::toObject($properties, 'JObject');

        // Convert attributes to JRegistry params
        $item->params = new JRegistry();

        $item->params->loadString($item->attribs);
        $item->attribs = $item->params->toArray();

        // Get the attachments
        $item->attachment = array();

        if (PFApplicationHelper::exists('com_pfrepo')) {
            $attachments = $this->getInstance('Attachments', 'PFrepoModel');
            $item->attachment = $attachments->getItems('com_pfmilestones.milestone', $item->id);
        }

        // Get the labels
        $model_labels = $this->getInstance('Labels', 'PFModel');
        $item->labels = $model_labels->getConnections('com_pfmilestones.milestone', $item->id);

        return $item;
    }


    public function getProgress($pks)
    {
        if (!is_array($pks) || !count($pks)) {
            return array();
        }

        // Get total task count
        $query = $this->_db->getQuery(true);

        $query->select('milestone_id, COUNT(*) AS total')
              ->from('#__pf_tasks')
              ->where('milestone_id IN(' . implode(', ', $pks) . ')')
              ->where('milestone_id > 0')
              ->where('state > 0')
              ->group('milestone_id');

        try {
            $this->_db->setQuery($query);
            $items_total = $this->_db->loadAssocList('milestone_id', 'total');
        }
        catch (RuntimeException $e) {
            $this->setError($e->getMessage());
            return array();
        }

        // Count completed tasks
        $query->clear()
              ->select('milestone_id, COUNT(*) AS completed')
              ->from('#__pf_tasks')
              ->where('milestone_id IN(' . implode(', ', $pks) . ')')
              ->where('state > 0')
              ->where('complete = 1')
              ->where('milestone_id > 0')
              ->group('milestone_id');

        try {
            $this->_db->setQuery($query);
            $items_completed = $this->_db->loadAssocList('milestone_id', 'completed');
        }
        catch (RuntimeException $e) {
            $this->setError($e->getMessage());
            return array();
        }

        $progress = array();

        foreach ($items_total AS $id => $total)
        {
            if (!array_key_exists($id, $items_completed) || $items_completed[$id] == 0 || $total == 0) {
                $progress[$id] = 0;
            }
            else {
                $progress[$id] = ($items_completed[$id] / $total) * 100;
            }
        }

        return $progress;
    }


    /**
     * Method to get the record form.
     *
     * @param     array      Data for the form.
     * @param     boolean    True if the form is to load its own data (default case), false if not.
     *
     * @return    mixed      A JForm object on success, false on failure
     */
    public function getForm($data = array(), $loadData = true)
    {
        // Get the form.
        $form = $this->loadForm('com_pfmilestones.milestone', 'milestone', array('control' => 'jform', 'load_data' => $loadData));
        if (empty($form)) return false;

        $jinput = JFactory::getApplication()->input;
        $user   = JFactory::getUser();
        $id     = (int) $jinput->get('id', 0);
        $task   = $jinput->get('task');

        // Check for existing item.
        // Modify the form based on Edit State access controls.
        if ($id != 0 && (!$user->authorise('core.edit.state', 'com_pfmilestones.milestone.' . $id)) || ($id == 0 && !$user->authorise('core.edit.state', 'com_pfmilestones')))
        {
            // Disable fields for display.
            $form->setFieldAttribute('state', 'disabled', 'true');
            $form->setFieldAttribute('start_date', 'disabled', 'true');
            $form->setFieldAttribute('end_date', 'disabled', 'true');

            // Disable fields while saving.
            $form->setFieldAttribute('state', 'filter', 'unset');
            $form->setFieldAttribute('start_date', 'filter', 'unset');
            $form->setFieldAttribute('end_date', 'filter', 'unset');
        }

        // Always disable these fields while saving
        $form->setFieldAttribute('alias', 'filter', 'unset');

        // Disable these fields if not an admin
        if (!$user->authorise('core.admin', 'com_pfmilestones') && !$user->authorise('core.manage', 'com_pfmilestones')) {
            $form->setFieldAttribute('access', 'disabled', 'true');
            $form->setFieldAttribute('access', 'filter', 'unset');

            $form->setFieldAttribute('rules', 'disabled', 'true');
            $form->setFieldAttribute('rules', 'filter', 'unset');
        }

        // Disable these fields when updating
        if ($id) {
            $form->setFieldAttribute('project_id', 'readonly', 'true');
            $form->setFieldAttribute('project_id', 'required', 'false');

            if ($task != 'save2copy') {
                $form->setFieldAttribute('project_id', 'disabled', 'true');
                $form->setFieldAttribute('project_id', 'filter', 'unset');
            }

            // We still need to inject the project id when reloading the form
            if (!isset($data['project_id'])) {
                $db    = JFactory::getDbo();
                $query = $db->getQuery(true);

                $query->select('project_id')
                      ->from('#__pf_milestones')
                      ->where('id = ' . $db->quote($id));

                $db->setQuery($query);
                $form->setValue('project_id', null, (int) $db->loadResult());
            }
        }

        return $form;
    }


    /**
     * Method to get the data that should be injected in the form.
     *
     * @return    mixed    The data for the form.
     */
    protected function loadFormData()
    {
        // Check the session for previously entered form data.
        $data = JFactory::getApplication()->getUserState('com_pfmilestones.edit.' . $this->getName() . '.data', array());

        if (empty($data)) {
            $data = $this->getItem();

            // Set default values
            if ($this->getState($this->getName() . '.id') == 0) {
                $active_id = PFApplicationHelper::getActiveProjectId();

                $data->set('project_id', $active_id);
            }
        }

        return $data;
    }


    /**
     * Method to save the form data.
     *
     * @param     array      The form data
     * @return    boolean    True on success
     */
    public function save($data)
    {
        $table  = $this->getTable();
        $key    = $table->getKeyName();
        $pk     = (!empty($data[$key])) ? $data[$key] : (int) $this->getState($this->getName() . '.id');
        $is_new = true;
        $old    = null;

        // Include the content plugins for the on save events.
        JPluginHelper::importPlugin('content');
        $dispatcher = JDispatcher::getInstance();

        // Allow an exception to be thrown.
        try {
            if ($pk > 0) {
                if ($table->load($pk)) {
                    $is_new = false;
                    $old    = clone $table;
                }
            }

            if (!$is_new) {
                $data['project_id'] = $table->project_id;
            }

            // Make sure the title and alias are always unique
            $data['alias'] = '';
            list($title, $alias) = $this->generateNewTitle($data['title'], $data['project_id'], $data['alias'], $pk);

            $data['title'] = $title;
            $data['alias'] = $alias;

            // Handle permissions and access level
            if (isset($data['rules'])) {
                $access = PFAccessHelper::getViewLevelFromRules($data['rules'], intval($data['access']));

                if ($access) {
                    $data['access'] = $access;
                }
            }
            else {
                if ($is_new) {
                    // Let the table class find the correct access level
                    $data['access'] = 0;
                }
                else {
                    // Keep the existing access in the table
                    if (isset($data['access'])) {
                        unset($data['access']);
                    }
                }
            }

            // Make item published by default if new
            if (!isset($data['state']) && $is_new) {
                $data['state'] = 1;
            }

            // Bind the data.
            if (!$table->bind($data)) {
                $this->setError($table->getError());
                return false;
            }

            // Prepare the row for saving
            $this->prepareTable($table);

            // Check the data.
            if (!$table->check()) {
                $this->setError($table->getError());
                return false;
            }

            // Trigger the onContentBeforeSave event.
            $result = $dispatcher->trigger($this->event_before_save, array($this->option . '.' . $this->name, &$table, $is_new));

            if (in_array(false, $result, true)) {
                $this->setError($table->getError());
                return false;
            }

            // Store the data.
            if (!$table->store()) {
                $this->setError($table->getError());
                return false;
            }

            $pk_name = $table->getKeyName();

            if (isset($table->$pk_name)) {
                $this->setState($this->getName() . '.id', $table->$pk_name);
            }

            $this->setState($this->getName() . '.new', $is_new);

            $id = $this->getState($this->getName() . '.id');

            // Set the active project
            PFApplicationHelper::setActiveProject($table->project_id);

            // Add to watch list - if not opt-out
            if ($is_new) {
                $plugin  = JPluginHelper::getPlugin('content', 'pfnotifications');
                $params  = new JRegistry($plugin->params);
                $opt_out = (int) $params->get('sub_method', 0);

                if (!$opt_out) {
                    $cid = array($id);

                    if (!$this->watch($cid, 1)) {
                        return false;
                    }
                }
            }

            // Store the attachments
            if (isset($data['attachment']) && PFApplicationHelper::exists('com_pfrepo')) {
                $attachments = $this->getInstance('Attachments', 'PFrepoModel');

                if (!$attachments->getState('item.type')) {
                    $attachments->setState('item.type', 'com_pfmilestones.milestone');
                }

                if ($attachments->getState('item.id') == 0) {
                    $attachments->setState('item.id', $id);
                }

                if ((int) $attachments->getState('item.project') == 0) {
                    $attachments->setState('item.project', $table->project_id);
                }

                if (!$attachments->save($data['attachment'])) {
                    $this->setError($attachments->getError());
                    return false;
                }
            }

            // Store the labels
            if (isset($data['labels'])) {
                $labels = $this->getInstance('Labels', 'PFModel');

                if ((int) $labels->getState('item.project') == 0) {
                    $labels->setState('item.project', $table->project_id);
                }

                $labels->setState('item.type', 'com_pfmilestones.milestone');
                $labels->setState('item.id', $id);

                if (!$labels->saveRefs($data['labels'])) {
                    return false;
                }
            }

            // Clean the cache.
            $this->cleanCache();

            // Trigger the onContentAfterSave event.
            $dispatcher->trigger($this->event_after_save, array($this->option . '.' . $this->name, &$table, $is_new));
        }
        catch (Exception $e) {
            $this->setError($e->getMessage());
            return false;
        }

        return true;
    }


    /**
     * Method to watch an item
     *
     * @param    array      $pks      The items to watch
     * @param    integer    $value    1 to watch, 0 to unwatch
     * @param    integer    $uid      The user id to watch the item
     */
    public function watch(&$pks, $value = 1, $uid = null)
    {
        $user  = JFactory::getUser($uid);
        $table = $this->getTable();
        $pks   = (array) $pks;

        $is_admin = $user->authorise('core.admin', $this->option);
        $my_views = $user->getAuthorisedViewLevels();
        $projects = array();

        $item_type = $this->option . '.milestone';


        // Access checks.
        foreach ($pks as $i => $pk) {
            $table->reset();

            if ($table->load($pk)) {
                if (!$is_admin && !in_array($table->access, $my_views)) {
                    unset($pks[$i]);
                    JError::raiseWarning(403, JText::_('JERROR_ALERTNOAUTHOR'));
                    $this->setError(JText::_('JERROR_ALERTNOAUTHOR'));
                    return false;
                }

                $projects[$pk] = (int) $table->project_id;
            }
            else {
                unset($pks[$i]);
            }
        }

        // Attempt to watch/unwatch the selected items
        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);

        foreach ($pks AS $i => $pk)
        {
            $query->clear();

            if ($value == 0) {
                $query->delete('#__pf_ref_observer')
                      ->where('item_type = ' . $db->quote( $item_type ) )
                      ->where('item_id = ' . $db->quote((int) $pk))
                      ->where('user_id = ' . $db->quote((int) $user->get('id')));

                $db->setQuery($query);
                $db->execute();

                if ($db->getError()) {
                    $this->setError($db->getError());
                    return false;
                }
            }
            else {
                $query->select('COUNT(*)')
                      ->from('#__pf_ref_observer')
                      ->where('item_type = ' . $db->quote( $item_type ) )
                      ->where('item_id = ' . $db->quote((int) $pk))
                      ->where('user_id = ' . $db->quote((int) $user->get('id')));

                $db->setQuery($query);
                $count = (int) $db->loadResult();

                if (!$count) {
                    $data = new stdClass;

                    $data->user_id   = (int) $user->get('id');
                    $data->item_type = $item_type;
                    $data->item_id   = (int) $pk;
                    $data->project_id= (int) $projects[$pk];

                    $db->insertObject('#__pf_ref_observer', $data);

                    if ($db->getError()) {
                        $this->setError($db->getError());
                        return false;
                    }
                }
            }
        }

        // Clear the component's cache
        $this->cleanCache();

        return true;
    }


    /**
     * Custom clean the cache of com_projectfork and projectfork modules
     *
     */
    protected function cleanCache($group = null, $client_id = 0)
    {
        parent::cleanCache('com_pfmilestones');
    }


    /**
     * Method to change the title & alias.
     * Overloaded from JModelAdmin class
     *
     * @param     string     $title      The title
     * @param     integer    $project    The project id
     * @param     string     $alias      The alias
     * @param     integer    $id         The item id
     *
     *
     * @return    array                  Contains the modified title and alias
     */
    protected function generateNewTitle($title, $project, $alias = '', $id = 0)
    {
        $table = $this->getTable();
        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);

        if (empty($alias)) {
            $alias = JApplication::stringURLSafe($title);

            if (trim(str_replace('-', '', $alias)) == '') {
                $alias = JApplication::stringURLSafe(JFactory::getDate()->format('Y-m-d-H-i-s'));
            }
        }

        $query->select('COUNT(id)')
              ->from($table->getTableName())
              ->where('alias = ' . $db->quote($alias))
              ->where('project_id = ' . $db->quote((int) $project));

        if ($id) {
            $query->where('id != ' . intval($id));
        }

        $db->setQuery((string) $query);
        $count = (int) $db->loadResult();

        if ($id > 0 && $count == 0) {
            return array($title, $alias);
        }
        elseif ($id == 0 && $count == 0) {
            return array($title, $alias);
        }
        else {
            while ($table->load(array('alias' => $alias, 'project_id' => $project)))
            {
                $m = null;

                if (preg_match('#-(\d+)$#', $alias, $m)) {
                    $alias = preg_replace('#-(\d+)$#', '-'.($m[1] + 1).'', $alias);
                }
                else {
                    $alias .= '-2';
                }

                if (preg_match('#\((\d+)\)$#', $title, $m)) {
                    $title = preg_replace('#\(\d+\)$#', '('.($m[1] + 1).')', $title);
                }
                else {
                    $title .= ' (2)';
                }
            }
        }

        return array($title, $alias);
    }


    /**
     * Method to test whether a record can be deleted.
     * Defaults to the permission set in the component.
     *
     * @param     object     A record object.
     *
     * @return    boolean    True if allowed to delete the record.
     */
    protected function canDelete($record)
    {
        if (empty($record->id)) {
            return parent::canDelete($record);
        }

        if ($record->state != -2) {
            return false;
        }

        $user = JFactory::getUser();

        if (!$user->authorise('core.admin') && !in_array($record->access, $user->getAuthorisedViewLevels())) {
            return false;
        }

        return $user->authorise('core.delete', 'com_pfmilestones.milestone.' . (int) $record->id);
    }


    /**
     * Method to test whether a record can have its state edited.
     * Defaults to the permission set in the component.
     *
     * @param     object     A record object.
     *
     * @return    boolean    True if allowed to edit the state of the record.
     */
    protected function canEditState($record)
    {
        if (empty($record->id)) {
            return parent::canEditState($record);
        }

        $user = JFactory::getUser();

        if (!$user->authorise('core.admin') && !in_array($record->access, $user->getAuthorisedViewLevels())) {
            return false;
        }

        return $user->authorise('core.edit.state', 'com_pfmilestones.milestone.' . (int) $record->id);
    }


    /**
     * Method to test whether a record can be edited.
     * Defaults to the permission for the component.
     *
     * @param     object     A record object.
     *
     * @return    boolean    True if allowed to edit the record.
     */
    protected function canEdit($record)
    {
        if (empty($record->id)) {
            return $user->authorise('core.edit', 'com_pfmilestones');
        }

        $user  = JFactory::getUser();
        $asset = 'com_pfmilestones.milestone.' . (int) $record->id;

        if (!$user->authorise('core.admin') && !in_array($record->access, $user->getAuthorisedViewLevels())) {
            return false;
        }

        return ($user->authorise('core.edit', $asset) || ($access->get('core.edit.own', $asset) && $record->created_by == $user->id));
    }
}
