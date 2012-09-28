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
 * Item Model for a topic reply form.
 *
 */
class ProjectforkModelReply extends JModelAdmin
{
    /**
     * The prefix to use with controller messages.
     *
     * @var    string
     */
    protected $text_prefix = 'COM_PROJECTFORK_REPLY';


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
    public function getTable($type = 'Reply', $prefix = 'PFTable', $config = array())
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

            // Get the attachments
            $attachments = $this->getInstance('Attachments', 'ProjectforkModel');
            $item->attachment = $attachments->getItems('reply', $item->id);
        }

        return $item;
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
        $form = $this->loadForm('com_projectfork.reply', 'reply', array('control' => 'jform', 'load_data' => $loadData));
        if (empty($form)) return false;

        // Check if a project and topic is already selected. If not, get them from the current state
        $project_id = (int) $form->getValue('project_id');
        $topic_id   = (int) $form->getValue('topic_id');

        if (!$project_id) {
            $form->setValue('project_id', null, $this->getState($this->getName() . '.project'));
        }
        if (!$topic_id) {
            $form->setValue('topic_id', null, $this->getState($this->getName() . '.topic'));
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
        $data = JFactory::getApplication()->getUserState('com_projectfork.edit.' . $this->getName() . '.data', array());

        if (empty($data)) $data = $this->getItem();

        return $data;
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

                    // Delete every item related to this reply
                    $tables = array('attachment');
                    $field  = array('item_type' => 'reply', 'item_id' => $pk);

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

        // On quick-save, access and publishing state are missing
        if ($this->getState('task') == 'quicksave' && isset($data['topic_id'])) {
            $db    = JFactory::getDbo();
            $query = $db->getQuery(true);
            $topic = (int) $data['topic_id'];

            if ($topic) {
                $query->select('state, access')
                      ->from('#__pf_topics')
                      ->where('id = ' . $db->quote($topic));

                $db->setQuery((string) $query);
                $parent = $db->loadObject();

                if ($parent) {
                    $data['state']  = $parent->state;
                    $data['access'] = $parent->access;
                }
            }
        }

        // Store the record
        if (parent::save($data)) {
            $id = $this->getState($this->getName() . '.id');

            // Load the just updated row
            $updated = $this->getTable();
            if ($updated->load($id) === false) return false;

            // Set the active project
            ProjectforkHelper::setActiveProject($updated->project_id);

            // Store the attachments
            if (isset($data['attachment']) && !$is_new) {
                $attachments = $this->getInstance('Attachments', 'ProjectforkModel');

                if ($attachments->getState('item.id') == 0) {
                    $attachments->setState('item.id', $id);
                    $attachments->setState('item.type', 'reply');
                }

                if (!$attachments->save($data['attachment'])) {
                    $this->setError($attachments->getError());
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

            $access = ProjectforkHelperAccess::getActions('reply', $record->id);
            return $access->get('reply.delete');
        }
        else {
            $access = ProjectforkHelperAccess::getActions();
            return $access->get('reply.delete');
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
            $access = ProjectforkHelperAccess::getActions('reply', $record->id);
            return $access->get('reply.edit.state');
        }
        else {
            $access = ProjectforkHelperAccess::getActions();
            return $access->get('reply.edit.state');
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
            $access = ProjectforkHelperAccess::getActions('reply', $record->id);
            $user   = JFactory::getUser();

            return ($access->get('reply.edit') || ($access->get('reply.edit.own') && $record->created_by == $user->id));
        }
        else {
            $access = ProjectforkHelperAccess::getActions();
            return $access->get('reply.edit');
        }
    }


    /**
     * Method to auto-populate the model state.
     * Note: Calling getState in this method will result in recursion.
     *
     * @return    void
     */
    protected function populateState()
    {
        // Initialise variables.
        $app   = JFactory::getApplication();
		$table = $this->getTable();
		$key   = $table->getKeyName();

		// Get the pk of the record from the request.
		$pk = JRequest::getInt($key);
		$this->setState($this->getName() . '.id', $pk);

        if ($pk) {
            $table = $this->getTable();

            if ($table->load($pk)) {
                $project = (int) $table->project_id;
                $this->setState($this->getName() . '.project', $project);
                ProjectforkHelper::setActiveProject($project);

                $topic = (int) $table->topic_id;
                $this->setState($this->getName() . '.topic', $topic);
            }
        }
        else {
            $topic = JRequest::getUInt('filter_topic', 0);
            $this->setState($this->getName() . '.topic', $topic);

            $project = ProjectforkHelper::getActiveProjectId('filter_project');

            if ($project) {
                $this->setState($this->getName() . '.project', $project);
            }
            elseif ($topic) {
                $table = $this->getTable('Topic');

                if ($table->load($topic)) {
                    $project = (int) $table->project_id;

                    $this->setState($this->getName() . '.project', $project);
                    ProjectforkHelper::setActiveProject($project);
                }
            }
        }

		// Load the parameters.
		$value = JComponentHelper::getParams($this->option);
		$this->setState('params', $value);
    }
}
