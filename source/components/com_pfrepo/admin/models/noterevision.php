<?php
/**
 * @package      pkg_projectfork
 * @subpackage   com_pfrepo
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2013 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


jimport('joomla.application.component.modeladmin');


/**
 * Item Model for a note revision.
 *
 */
class PFrepoModelNoteRevision extends JModelAdmin
{
    /**
     * The prefix to use with controller messages.
     *
     * @var    string
     */
    protected $text_prefix = 'COM_PROJECTFORK_NOTE';


    /**
     * Returns a Table object, always creating it.
     *
     * @param     string    The table type to instantiate
     * @param     string    A prefix for the table class name. Optional.
     * @param     array     Configuration array for model. Optional.
     *
     * @return    jtable    A database object
     */
    public function getTable($type = 'NoteRevision', $prefix = 'PFtable', $config = array())
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
        $item = parent::getItem($pk)

        if ($item === false) return false;

        // Convert the params field to an array.
        $registry = new JRegistry;

        $registry->loadString($item->attribs);

        $item->params  = $registry;
        $item->attribs = $registry->toArray();

        return $item;
    }


    /**
     * Method to save an item
     *
     * @param     array      $data    The item data
     *
     * @return    boolean             True on success, False on error
     */
    public function save($data)
    {
        // Initialise variables;
        $table  = $this->getTable();
        $pk     = (!empty($data['id'])) ? $data['id'] : (int) $this->getState($this->getName() . '.id');
        $is_new = true;

        // Load the row if saving an existing item.
        if ($pk > 0) {
            if ($table->load($pk)) {
                $is_new = false;
            }
            else {
                $pk = 0;
            }
        }

        // Make sure the title and alias are always unique
        $data['alias'] = '';
        list($title, $alias) = $this->generateNewTitle($data['parent_id'], $data['title'], $data['alias'], $pk);

        $data['title'] = $title;
        $data['alias'] = $alias;

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

        // Store the data.
        if (!$table->store()) {
            $this->setError($table->getError());
            return false;
        }

        $this->setState($this->getName() . '.id', $table->id);

        // Clear the cache
        $this->cleanCache();

        return true;
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
        return false;
    }


    /**
     * Custom clean the cache of com_projectfork and projectfork modules
     *
     */
    protected function cleanCache($group = 'com_pfrepo', $client_id = 0)
    {
        parent::cleanCache($group);
    }


    /**
     * Prepare and sanitise the table data prior to saving.
     *
     * @param     jtable    A JTable object.
     *
     * @return    void
     */
    protected function prepareTable($table)
    {
        if (empty($table->id)) {
            $condition = 'parent_id = ' . (int) $table->parent_id;
            $query     = $this->_db->getQuery(true);

            $query->select('ordering')
                  ->from('#__pf_repo_note_revs')
                  ->where('parent_id = ' . (int) $table->parent_id)
                  ->order('ordering DESC');

            $this->_db->setQuery($query, 0, 1);
            $ordering = (int) $this->_db->loadResult();
            $ordering++;

            $table->ordering = $ordering;

            $table->reorder($condition);
        }
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
        $condition = 'parent_id = '  . (int) $table->parent_id;

        return array($condition);
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
        if (!empty($record->parent_id)) {
            $user  = JFactory::getUser();
            $asset = 'com_pfrepo.note.' . (int) $record->parent_id;

            return $user->authorise('core.delete', $asset);
        }

        return parent::canDelete($record);
    }


    /**
     * Method to change the title.
     *
     * @param     integer    $parent       The parent note
     * @param     string     $title        The directory title
     * @param     string     $alias        The current alias
     * @param     integer    $id           The note id
     *
     * @return    string                   Contains the new title
     */
    protected function generateNewTitle($parent, $title, $alias = '', $id = 0)
    {
        // Alter the title & alias
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
              ->where('parent_id = ' . $db->quote($parent));

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
            while ($table->load(array('alias' => $alias, 'parent_id' => $parent)))
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
		if (!empty($record->parent_id)) {
			return $user->authorise('core.edit.state', 'com_pfrepo.note.' . (int) $record->parent_id);
		}
		else {
		    // Default to component settings.
			return parent::canEditState('com_pfrepo');
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
        if (!empty($record->parent_id)) {
            $asset = 'com_pfrepo.note.' . (int) $record->parent_id;

            return ($user->authorise('core.edit', $asset) || ($access->get('core.edit.own', $asset) && $record->created_by == $user->id));
        }

        return $user->authorise('core.edit', 'com_pfrepo');
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

                $dir_id = (int) $table->dir_id;
                $this->setState($this->getName() . '.dir_id', $dir_id);
            }
        }
        else {
            $dir_id = JRequest::getUInt('filter_parent_id', 0);
            $this->setState($this->getName() . '.dir_id', $dir_id);

            $project = PFApplicationHelper::getActiveProjectId('filter_project');

            if ($project) {
                $this->setState($this->getName() . '.project', $project);
                PFApplicationHelper::setActiveProject($project);
            }
            elseif ($dir_id) {
                $table = $this->getTable('Directory');

                if ($table->load($parent_id)) {
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
