<?php
/**
 * @package      Projectfork
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


jimport('joomla.application.component.modeladmin');


/**
 * Item Model for a task form.
 *
 */
class ProjectforkModelTask extends JModelAdmin
{
    /**
     * The prefix to use with controller messages.
     *
     * @var    string
     */
    protected $text_prefix = 'COM_PROJECTFORK_TASK';


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
        JLoader::register('ProjectforkHelper',           JPATH_ADMINISTRATOR . '/components/com_projectfork/helpers/projectfork.php');
        JLoader::register('ProjectforkHelperAccess',     JPATH_ADMINISTRATOR . '/components/com_projectfork/helpers/access.php');
        JLoader::register('ProjectforkHelperQuery',      JPATH_ADMINISTRATOR . '/components/com_projectfork/helpers/query.php');
        JLoader::register('ProjectforkHelperRepository', JPATH_ADMINISTRATOR . '/components/com_projectfork/helpers/repository.php');

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
    public function getTable($type = 'Task', $prefix = 'PFTable', $config = array())
    {
        return JTable::getInstance($type, $prefix, $config);
    }


    /**
     * Method to get a single record.
     *
     * @param     integer    The id of the primary key.
     * @return    mixed      Object on success, false on failure.
     */
    public function getItem($pk = null)
    {
        if ($item = parent::getItem($pk)) {
            // Convert the params field to an array.
            $registry = new JRegistry;
            $registry->loadString($item->attribs);
            $item->attribs = $registry->toArray();

            $item->users = $this->getUsers($pk);

            // Convert seconds back to minutes
            if ($item->estimate > 0) {
                $item->estimate = round($item->estimate / 60);
            }

            // Get the attachments
            $attachments = $this->getInstance('Attachments', 'ProjectforkModel');
            $item->attachment = $attachments->getItems('task', $item->id);

            // Get the labels
            $labels = $this->getInstance('Labels', 'ProjectforkModel');
            $item->labels = $labels->getConnections('task', $item->id);
        }

        return $item;
    }


    /**
     * Method to get assigned users of a task
     *
     * @param     integer    The id of the primary key.
     * @return    array      The assigned users
     */
    public function getUsers($pk = NULL)
    {
        if (!$pk) $pk = $this->getState($this->getName() . '.id');
        if (!$pk) return array();

        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->select('user_id')
              ->from('#__pf_ref_users')
              ->where('item_type = ' . $db->quote('task'))
              ->where('item_id = ' . $db->quote($pk));

        $db->setQuery((string) $query);
        $data = (array) $db->loadResultArray();
        $list = array();

        foreach($data AS $i => $uid)
        {
            $list['user' . $i] = $uid;
        }

        return $list;
    }


    /**
     * Method to get the record form.
     *
     * @param     array      Data for the form.
     * @param     boolean    True if the form is to load its own data (default case), false if not.
     * @return    mixed      A JForm object on success, false on failure
     */
    public function getForm($data = array(), $loadData = true)
    {
        // Get the form.
        $form = $this->loadForm('com_projectfork.task', 'task', array('control' => 'jform', 'load_data' => $loadData));
        if (empty($form)) return false;

        $is_new    = ((int) $this->getState($this->getName() . '.id') > 0) ? false : true;
        $project   = (int) $form->getValue('project_id');
        $milestone = (int) $form->getValue('milestone_id');
        $list      = (int) $form->getValue('list_id');

        // Override data if not set
        if ($is_new) {
            if ($project == 0) {
                $active_id = ProjectforkHelper::getActiveProjectId();

                $form->setValue('project_id', null, $active_id);
            }

            // Override milestone selection if set
            if ($milestone == 0) {
                $form->setValue('milestone_id', null, JRequest::getUInt('milestone_id'));
            }

            // Override task list selection if set
            if ($list == 0) {
                $form->setValue('list_id', null, JRequest::getUInt('list_id'));
            }
        }

        return $form;
    }


    /**
     * Method to delete one or more records.
     *
     * @param     array      An array of record primary keys.
     *
     * @return    boolean    True if successful, false if an error occurs.
     */
    public function delete(&$pks)
    {
        // Initialise variables.
		$dispatcher = JDispatcher::getInstance();
		$pks   = (array) $pks;
		$table = $this->getTable();

		// Include the content plugins for the on delete events.
		JPluginHelper::importPlugin('content');

		// Iterate the items to delete each one.
		foreach ($pks as $i => $pk)
		{
			if ($table->load($pk)) {
				if ($this->canDelete($table)) {
					$context = $this->option . '.' . $this->name;
					// Trigger the onContentBeforeDelete event.
					$result = $dispatcher->trigger($this->event_before_delete, array($context, $table));

					if (in_array(false, $result, true)) {
						$this->setError($table->getError());
						return false;
					}

					if (!$table->delete($pk)) {
						$this->setError($table->getError());
						return false;
					}

                    // Delete every item related to this project
                    $tables = array('attachment');
                    $field  = array('item_type' => 'task', 'item_id' => $pk);

                    if (!ProjectforkHelperQuery::deleteFromTablesByField($tables, $field)) {
                        return false;
                    }

                    $tables = array('comment');
                    $field  = array('context' => 'com_projectfork.task', 'item_id' => $pk);

                    if (!ProjectforkHelperQuery::deleteFromTablesByField($tables, $field)) {
                        return false;
                    }

                    $tables = array('userref');
                    $field  = array('item_type' => 'task', 'item_id' => $pk);

                    if (!ProjectforkHelperQuery::deleteFromTablesByField($tables, $field)) {
                        return false;
                    }

                    $tables = array('labelref');
                    $field  = array('item_type' => 'task', 'item_id' => $pk);

                    if (!ProjectforkHelperQuery::deleteFromTablesByField($tables, $field)) {
                        return false;
                    }

					// Trigger the onContentAfterDelete event.
					$dispatcher->trigger($this->event_after_delete, array($context, $table));
				}
				else {
					// Prune items that you can't change.
					unset($pks[$i]);

					$error = $this->getError();

					if ($error) {
						JError::raiseWarning(500, $error);
						return false;
					}
					else {
						JError::raiseWarning(403, JText::_('JLIB_APPLICATION_ERROR_DELETE_NOT_PERMITTED'));
						return false;
					}
				}
			}
			else {
				$this->setError($table->getError());
				return false;
			}
		}

		// Clear the component's cache
		$this->cleanCache();

		return true;
    }


    /**
     * Method to get the data that should be injected in the form.
     *
     * @return    mixed    The data for the form.
     */
    protected function loadFormData()
    {
        // Check the session for previously entered form data.
        $data = JFactory::getApplication()->getUserState('com_projectfork.edit.' . $this->getName() . '.data', array());

        if (empty($data)) $data = $this->getItem();

        return $data;
    }


    /**
     * A protected method to get a set of ordering conditions.
     *
     * @param     object    A record object.
     *
     * @return    array     An array of conditions to add to add to ordering queries.
     */
    protected function getReorderConditions($table)
    {
        $catid = intval($table->project_id) . '-' . intval($table->milestone_id) . '-' . intval($table->list_id);

        $condition = array();
        $condition[] = 'catid = '.(int) $catid;

        return $condition;
    }


    /**
     * Prepare and sanitise the table data prior to saving.
     *
     * @param     jtable    A JTable object.
     *
     * @return    void
     */
    protected function prepareTable(&$table)
    {
        // Generate catid
        $catid = intval($table->project_id) . '-' . intval($table->milestone_id) . '-' . intval($table->list_id);

        // Reorder the items within the category so the new item is first
        if (empty($table->id)) {
            $table->reorder('catid = '.(int) $catid.' AND state >= 0');
        }
    }


    /**
     * Method to save the form data.
     *
     * @param     array      The form data
     *
     * @return    boolean    True on success
     */
    public function save($data)
    {
        $record = $this->getTable();
        $key    = $record->getKeyName();
        $pk     = (!empty($data[$key])) ? $data[$key] : (int) $this->getState($this->getName() . '.id');
        $is_new = true;

        if ($pk > 0) {
            if ($record->load($pk)) {
                $is_new = false;
            }
        }

        // Make sure the title and alias are always unique
        $data['alias'] = '';
        list($title, $alias) = $this->generateNewTitle($data['title'], $data['project_id'], $data['milestone_id'], $data['list_id'], $data['alias'], $pk);

        $data['title'] = $title;
        $data['alias'] = $alias;

        // Handle permissions and access level
        if (isset($data['rules'])) {
            $access = ProjectforkHelperAccess::getViewLevelFromRules($data['rules'], intval($data['access']));

            if ($access) {
                $data['access'] = $access;
            }
        }
        else {
            if ($is_new) {
                $data['access'] = 1;
            }
            else {
                if (isset($data['access'])) {
                    unset($data['access']);
                }
            }
        }

        // Try to convert estimate string to time
        if (isset($data['estimate'])) {
            if (!is_numeric($data['estimate'])) {
                $estimate_time = strtotime($data['estimate']);

                if ($estimate_time === false || $estimate_time < 0) {
                    $data['estimate'] = 1;
                }
                else {
                    $data['estimate'] = $estimate_time - time();
                }
            }
            else {
                // not a literal time, so convert minutes to secs
                $data['estimate'] = $data['estimate'] * 60;
            }
        }

        // Store the base record
        if(parent::save($data)) {
            $id = $this->getState($this->getName() . '.id');

            // Load the just updated row
            $updated = $this->getTable();
            if ($updated->load($id) === false) return false;

            // Set the active project
            ProjectforkHelper::setActiveProject($updated->project_id);

            // Store the attachments
            if (isset($data['attachment'])) {
                $attachments = $this->getInstance('Attachments', 'ProjectforkModel');

                if ($attachments->getState('item.id') == 0) {
                    $attachments->setState('item.id', $this->getState($this->getName() . '.id'));
                }

                if (!$attachments->save($data['attachment'])) {
                    $this->setError($attachments->getError());
                    return false;
                }
            }

            // Store the labels
            if (isset($data['labels'])) {
                $labels = $this->getInstance('Labels', 'ProjectforkModel');

                if ((int) $labels->getState('item.project') == 0) {
                    $labels->setState('item.project', $updated->project_id);
                }

                $labels->setState('item.type', 'task');
                $labels->setState('item.id', $id);

                if (!$labels->saveRefs($data['labels'])) {
                    return false;
                }
            }

            return true;
        }

        return false;
    }


    /**
     * Custom clean the cache of com_projectfork and projectfork modules
     *
     */
    protected function cleanCache()
    {
        parent::cleanCache('com_projectfork');
    }


    /**
     * Method to change the title & alias.
     * Overloaded from JModelAdmin class
     *
     * @param     string     $title      The title
     * @param     integer    $project    The project id
     * @param     integer    $milestone    The milestone id
     * @param     integer    $list    The list id
     * @param     string     $alias      The alias
     * @param     integer    $id         The item id
     *
     *
     * @return    array                  Contains the modified title and alias
     */
    protected function generateNewTitle($title, $project, $milestone = 0, $list = 0, $alias = '', $id = 0)
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
              ->where('project_id = ' . $db->quote((int) $project))
              ->where('milestone_id = ' . $db->quote((int) $milestone))
              ->where('list_id = ' . $db->quote((int) $list));

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
            while ($table->load(array('alias' => $alias, 'project_id' => $project, 'milestone_id' => $milestone, 'list_id' => $list)))
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
        if (!empty($record->id)) {
            if ($record->state != -2) return false;

            $access = ProjectforkHelperAccess::getActions('task', $record->id);
            return $access->get('task.delete');
        }
        else {
            $access = ProjectforkHelperAccess::getActions();
            return $access->get('task.delete');
        }
    }


    /**
     * Method to test whether a record can have its state edited.
     * Defaults to the permission set in the component.
     *
     * @param     object     A record object.
     *
     * @return    boolean    True if allowed to delete the record.
     */
    protected function canEditState($record)
    {
        // Check for existing item.
        if (!empty($record->id)) {
            $access = ProjectforkHelperAccess::getActions('task', $record->id);
            return $access->get('task.edit.state');
        }
        else {
            $access = ProjectforkHelperAccess::getActions();
            return $access->get('task.edit.state');
        }
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
        // Check for existing item.
        if (!empty($record->id)) {
            $access = ProjectforkHelperAccess::getActions('task', $record->id);
            $user   = JFactory::getUser();

            return ($access->get('task.edit') || ($access->get('task.edit.own') && $record->created_by == $user->id));
        }
        else {
            $access = ProjectforkHelperAccess::getActions();
            return $access->get('task.edit');
        }
    }
}
