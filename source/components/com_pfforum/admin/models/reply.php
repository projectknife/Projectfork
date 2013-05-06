<?php
/**
 * @package      Projectfork
 * @subpackage   Reply
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
class PFforumModelReply extends JModelAdmin
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
    public function getTable($type = 'Reply', $prefix = 'PFtable', $config = array())
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
            if (PFApplicationHelper::enabled('com_pfrepo')) {
                $attachments = $this->getInstance('Attachments', 'PFrepoModel');
                $item->attachment = $attachments->getItems('com_pfforum.reply', $item->id);
            }
            else {
                $item->attachment = array();
            }
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
        $form = $this->loadForm('com_pfforum.reply', 'reply', array('control' => 'jform', 'load_data' => $loadData));
        if (empty($form)) return false;

        $jinput = JFactory::getApplication()->input;
        $user   = JFactory::getUser();
        $id     = (int) $jinput->get('id', 0);

        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);

        // Check for existing item.
        // Modify the form based on Edit State access controls.
        if ($id != 0 && (!$user->authorise('core.edit.state', 'com_pfforum.reply.' . $id)) || ($id == 0 && !$user->authorise('core.edit.state', 'com_pfforum')))
        {
            // Disable fields for display.
            $form->setFieldAttribute('state', 'disabled', 'true');

            // Disable fields while saving.
			$form->setFieldAttribute('state', 'filter', 'unset');
        }

        // Disable these fields if not an admin
        if (!$user->authorise('core.admin', 'com_pfforum')) {
            $form->setFieldAttribute('access', 'disabled', 'true');
            $form->setFieldAttribute('access', 'filter', 'unset');

            $form->setFieldAttribute('rules', 'disabled', 'true');
            $form->setFieldAttribute('rules', 'filter', 'unset');
        }

        // Disable these fields when updating
        if ($id) {
            $form->setFieldAttribute('project_id', 'disabled', 'true');
            $form->setFieldAttribute('project_id', 'filter', 'unset');
            $form->setFieldAttribute('project_id', 'required', 'false');

            $form->setFieldAttribute('topic_id', 'disabled', 'true');
            $form->setFieldAttribute('topic_id', 'filter', 'unset');
            $form->setFieldAttribute('topic_id', 'required', 'false');

            // We still need to inject the project id when reloading the form
            if (!isset($data['project_id'])) {
                $query->select('project_id')
                      ->from('#__pf_replies')
                      ->where('id = ' . $db->quote($id));

                $db->setQuery($query);
                $form->setValue('project_id', null, (int) $db->loadResult());
            }

            // Same for the topic id
            if (!isset($data['topic_id'])) {
                $query->clear();
                $query->select('topic_id')
                      ->from('#__pf_replies')
                      ->where('id = ' . $db->quote($id));

                $db->setQuery($query);
                $form->setValue('topic_id', null, (int) $db->loadResult());
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
        $data = JFactory::getApplication()->getUserState('com_pfforum.edit.' . $this->getName() . '.data', array());

        if (empty($data)) {
			$data = $this->getItem();

            // Set default values
            if ($this->getState($this->getName() . '.id') == 0) {
                $active_id = PFApplicationHelper::getActiveProjectId();

                $data->set('project_id', $active_id);
                $data->set('topic_id',   $this->getState($this->getName() . '.topic'));
            }
        }

        return $data;
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

        if (!$is_new) {
            $data['project_id'] = $record->project_id;
            $data['topic_id']   = $record->topic_id;
        }

        // Make item published by default if new
        if (!isset($data['state']) && $is_new) {
            $data['state'] = 1;
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
            PFApplicationHelper::setActiveProject($updated->project_id);

            // Store the attachments
            if (isset($data['attachment']) && PFApplicationHelper::exists('com_pfrepo')) {
                $attachments = $this->getInstance('Attachments', 'PFrepoModel');

                if (!$attachments->getState('item.type')) {
                    $attachments->setState('item.type', 'com_pfforum.reply');
                }

                if ($attachments->getState('item.id') == 0) {
                    $attachments->setState('item.id', $id);
                }

                if ((int) $attachments->getState('item.project') == 0) {
                    $attachments->setState('item.project', $updated->project_id);
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
    protected function cleanCache($group = 'com_pfforum', $client = 0)
    {
        parent::cleanCache($group);
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

            $user  = JFactory::getUser();
            $asset = 'com_pfforum.reply.' . (int) $record->id;

            return $user->authorise('core.delete', $asset);
        }

        return parent::canDelete($record);
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
        $user = JFactory::getUser();

		// Check for existing item.
		if (!empty($record->id)) {
			return $user->authorise('core.edit.state', 'com_pfforum.reply.' . (int) $record->id);
		}
        elseif (!empty($record->topic_id)) {
            // New item, so check against the topic.
			return $user->authorise('core.edit.state', 'com_pfforum.topic.' . (int) $record->topic_id);
        }
		else {
		    // Default to component settings if neither article nor category known.
			return parent::canEditState('com_pfforum');
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
        $user = JFactory::getUser();

        // Check for existing item.
        if (!empty($record->id)) {
            $asset = 'com_pfforum.reply.' . (int) $record->id;

            return ($user->authorise('core.edit', $asset) || ($access->get('core.edit.own', $asset) && $record->created_by == $user->id));
        }

        return $user->authorise('core.edit', 'com_pfforum');
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
                PFApplicationHelper::setActiveProject($project);

                $topic = (int) $table->topic_id;
                $this->setState($this->getName() . '.topic', $topic);
            }
        }
        else {
            $topic = JRequest::getUInt('filter_topic', 0);
            $this->setState($this->getName() . '.topic', $topic);

            $project = PFApplicationHelper::getActiveProjectId('filter_project');

            if ($project) {
                $this->setState($this->getName() . '.project', $project);
            }
            elseif ($topic) {
                $table = $this->getTable('Topic');

                if ($table->load($topic)) {
                    $project = (int) $table->project_id;

                    $this->setState($this->getName() . '.project', $project);
                    PFApplicationHelper::setActiveProject($project);
                }
            }
        }

		// Load the parameters.
		$value = JComponentHelper::getParams($this->option);
		$this->setState('params', $value);
    }
}
