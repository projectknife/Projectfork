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


require_once dirname(__FILE__) . '/fileform.php';

/**
 * Item Model for a file revision.
 *
 */
class PFrepoModelFileRevision extends PFrepoModelFileForm
{
    /**
     * The prefix to use with controller messages.
     *
     * @var    string
     */
    protected $text_prefix = 'COM_PROJECTFORK_FILE_REVISION';


    /**
     * Returns a Table object, always creating it.
     *
     * @param     string    The table type to instantiate
     * @param     string    A prefix for the table class name. Optional.
     * @param     array     Configuration array for model. Optional.
     *
     * @return    jtable    A database object
     */
    public function getTable($type = 'FileRevision', $prefix = 'PFTable', $config = array())
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
        $date   = JFactory::getDate();
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

        // Use the file name as title if empty
        if ($data['title'] == '' && isset($data['file_name'])) {
            $data['title'] = $data['file_name'];
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
     * Prepare and sanitise the table data prior to saving.
     *
     * @param     jtable    A JTable object.
     *
     * @return    void
     */
    protected function prepareTable(&$table)
    {
        // Reorder the items within the category so the new item is first
        if (empty($table->id)) {
            $condition = 'parent_id = ' . (int) $table->parent_id;
            $query     = $this->_db->getQuery(true);

            $query->select('ordering')
                  ->from('#__pf_repo_file_revs')
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
     * Method to get the data that should be injected in the form.
     *
     * @return    mixed    The data for the form.
     */
    protected function loadFormData()
    {
        // Check the session for previously entered form data.
        $data = JFactory::getApplication()->getUserState('com_pfrepo.edit.' . $this->getName() . '.data', array());

        if (empty($data)) {
			$data = $this->getItem();

            // Set default values
            if ($this->getState($this->getName() . '.id') == 0) {
                $active_id = PFApplicationHelper::getActiveProjectId();

                $data->set('project_id', $active_id);
                $data->set('dir_id', $this->getState($this->getName() . '.dir_id'));
            }
        }

        return $data;
    }


    /**
     * Custom clean the cache of com_projectfork and projectfork modules
     *
     */
    protected function cleanCache()
    {
        parent::cleanCache('com_pfrepo');
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
            $asset = 'com_pfrepo.file.' . (int) $record->parent_id;

            return $user->authorise('core.delete', $asset);
        }

        return parent::canDelete($record);
    }


    /**
     * Method to change the title.
     *
     * @param     integer    $parent_id    The parent file
     * @param     string     $title     The directory title
     * @param     string     $alias     The current alias
     * @param     integer    $id        The note id
     *
     * @return    string                Contains the new title
     */
    protected function generateNewTitle($parent_id, $title, $alias = '', $id = 0)
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
              ->where('parent_id = ' . (int) $parent_id);

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
            while ($table->load(array('alias' => $alias, 'parent_id' => $parent_id)))
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
			return $user->authorise('core.edit.state', 'com_pfrepo.file.' . (int) $record->parent_id);
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
            $asset = 'com_pfrepo.file.' . (int) $record->parent_id;

            return ($user->authorise('core.edit', $asset) || ($access->get('core.edit.own', $asset) && $record->created_by == $user->id));
        }

        return $user->authorise('core.edit', 'com_pfrepo');
    }
}
