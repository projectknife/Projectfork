<?php
/**
* @package   Projectfork
* @copyright Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL, see license.txt
*
* This file is part of Projectfork.
*
* Projectfork is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
*
* Projectfork is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with Projectfork. If not, see <http://www.gnu.org/licenses/gpl.html>.
**/

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.modeladmin');


/**
 * Item Model for an Project form.
 *
 */
class ProjectforkModelProject extends JModelAdmin
{
	/**
	 * @var    string    The prefix to use with controller messages.
	 */
	protected $text_prefix = 'COM_PROJECTFORK_PROJECT';


	/**
	 * Returns a Table object, always creating it.
	 *
	 * @param    type	   The table type to instantiate
	 * @param	 string    A prefix for the table class name. Optional.
	 * @param	 array	   Configuration array for model. Optional.
	 * @return	 JTable	   A database object
	 */
	public function getTable($type = 'Project', $prefix = 'PFTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}


	/**
	 * Method to get a single record.
	 *
	 * @param	  integer	The id of the primary key.
	 * @return    mixed     Object on success, false on failure.
	 */
	public function getItem($pk = null)
	{
		if ($item = parent::getItem($pk)) {
			// Convert the params field to an array.
			$registry = new JRegistry;
			$registry->loadString($item->attribs);
			$item->attribs = $registry->toArray();
		}

		return $item;
	}


    /**
     * Method to get the user groups of a project
     *
     * @param     integer    $pk    The project id
     *
     * @return    array             The user groups
     **/
    public function getUserGroups($pk = NULL, $children = true)
    {
        // Include the helper class
        require_once(JPATH_ADMINISTRATOR.'/components/com_projectfork/helpers/projectfork.php');

        $pk = (!empty($pk)) ? $pk : (int) $this->getState($this->getName() . '.id');
		$table = $this->getTable();

        if ($pk > 0) {
			// Attempt to load the row.
			$return = $table->load($pk);

			// Check for a table object error.
			if ($return === false && $table->getError()) {
				$this->setError($table->getError());
				return false;
			}

            return ProjectforkHelper::getGroupsByAccess($table->access, $children);
		}

        return false;
    }


	/**
	 * Method to get the record form.
	 *
	 * @param    array      $data		Data for the form.
	 * @param	 boolean    $loadData	True if the form is to load its own data (default case), false if not.
	 * @return	 mixed                  A JForm object on success, false on failure
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm('com_projectfork.project', 'project', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form)) return false;

        $jinput = JFactory::getApplication()->input;
        $user   = JFactory::getUser();
        $id     =  $jinput->get('id', 0);


        // Check for existing item.
		// Modify the form based on Edit State access controls.
		if ($id != 0 && (!$user->authorise('core.edit.state', 'com_projectfork.project.'.(int) $id) && !$user->authorise('project.edit.state', 'com_projectfork.project.'.(int) $id))
		|| ($id == 0 && (!$user->authorise('core.edit.state', 'com_projectfork') && !$user->authorise('project.edit.state', 'com_projectfork')))
		)
		{
			// Disable fields for display.
			$form->setFieldAttribute('state', 'disabled', 'true');

			// Disable fields while saving.
			// The controller has already verified this is an item you can edit.
			$form->setFieldAttribute('state', 'filter', 'unset');
		}

		return $form;
	}


	/**
	 * Method to save the form data.
	 *
	 * @param     array	     The form data
	 * @return    boolean    True on success
	 */
	public function save($data)
	{
 	   // Get the users helper class
	    require_once(JPATH_ADMINISTRATOR.'/components/com_users/helpers/users.php');

		// Alter the title for save as copy
		if (JRequest::getVar('task') == 'save2copy') {
			list($title, $alias) = $this->generateNewTitle($data['alias'], $data['title']);

			$data['title'] = $title;
			$data['alias'] = $alias;
		}
        else {
            // Always re-generate the alias unless save2copy
            $data['alias'] = '';
        }

        // Create new access level?
        $new_access = trim($data['access_new']);
        $can_do     = UsersHelper::getActions();

        if(!isset($data['rules'])) {
            $data['rules']= array();
        }

        if(strlen($new_access) && $can_do->get('core.create')) {
            $data['access'] = $this->saveAccessLevel($new_access, $data['rules']);
        }

        if($data['access'] <= 0) $data['access'] = 1;


        // Filter the rules
        $rules = array();
        foreach ((array) $data['rules'] as $action => $ids)
		{
			if(is_numeric($action)) continue;

            // Build the rules array.
			$rules[$action] = array();
			foreach ($ids as $id => $p)
			{
				if ($p !== '')
				{
					$rules[$action][$id] = ($p == '1' || $p == 'true') ? true : false;
				}
			}
		}
        $data['rules'] = $rules;


        $id      = (int) $data['id'];
        $is_new  = ($id > 0) ? false : true;
        $project = null;

        if(!$is_new) {
            // Load the existing project record before updating it
            $project = $this->getTable();
            $project->load($id);
        }


        // Store the record
		if(parent::save($data)) {
		    $this->setActive(array('id' => $this->getState('project.id')));

            // To keep data integrity, update all child assets
            if(!$is_new && is_object($project)) {
                $updated    = $this->getTable();
                $milestones = JTable::getInstance('Milestone', 'PFTable');
                $tasklists  = JTable::getInstance('Tasklist', 'PFTable');
                $tasks      = JTable::getInstance('Task', 'PFTable');

                $parent_data = array();
                $null_date   = JFactory::getDbo()->getNullDate();


                // Load the just updated row
                if($updated->load($this->getState('project.id')) === false) return false;


                // Check if any relevant values have changed that need to be updated to children
                if($project->access != $updated->access) {
                    $parent_data['access'] = $updated->access;
                }

                if($project->start_date != $updated->start_date && $project->start_date != $null_date) {
                    $parent_data['start_date'] = $updated->start_date;
                }

                if($project->start_date != $updated->end_date && $project->end_date != $null_date) {
                    $parent_data['end_date'] = $updated->end_date;
                }

                if($project->state != $updated->state) {
                    $parent_data['state'] = $updated->state;
                }


                if(count($parent_data)) {
                    $milestones->updateByReference($id, 'project_id', $parent_data);
                    $tasklists->updateByReference($id, 'project_id', $parent_data);
                    $tasks->updateByReference($id, 'project_id', $parent_data);
                }
            }

		    return true;
		}

		return false;
	}


    /**
	 * Method to set a project to active on the current user
	 *
	 * @param     array	     The form data
	 * @return    boolean    True on success
	 */
    public function setActive($data)
    {
        $app = JFactory::getApplication();

        $id = (int) $data['id'];

        if ($id) {
            // Load the project and verify the access
            $user  = JFactory::getUser();
            $table = $this->getTable();

            if ($table->load($id) === false) {
                return false;
            }

            if (!$user->authorise('core.admin')) {
		        if(!in_array($table->access, $user->getAuthorisedViewLevels())) {
		            $this->setError(JText::_('COM_PROJECTFORK_ERROR_PROJECT_ACCESS'));
		            return false;
		        }
            }

            $app->setUserState('com_projectfork.project.active.id', $id);
            $app->setUserState('com_projectfork.project.active.title', $table->title);
        }
        else {
            $app->setUserState('com_projectfork.project.active.id', 0);
            $app->setUserState('com_projectfork.project.active.title', '');
        }

        return true;
    }


    /**
	 * Method to delete one or more records.
	 *
	 * @param     array    &$pks    An array of record primary keys.
	 * @return    boolean           True if successful, false if an error occurs.
	 */
    public function delete(&$pks)
    {
        $original_pks = $pks;

        // Delete the records
        $success = parent::delete($pks);

        // Cancel if something went wrong
        if(!$success) return false;


        $app        = JFactory::getApplication();
        $milestones = JTable::getInstance('Milestone', 'PFTable');
        $tasklists  = JTable::getInstance('Tasklist', 'PFTable');
        $tasks      = JTable::getInstance('Task', 'PFTable');

        $active_id = (int) $app->getUserState('com_projectfork.project.active.id', 0);

        foreach ($pks as $i => $pk)
		{
		    // Delete all other items referenced to each project
            if (!$milestones->deleteByReference($pk, 'project_id')) $success = false;
            if (!$tasklists->deleteByReference($pk, 'project_id'))  $success = false;
            if (!$tasks->deleteByReference($pk, 'project_id'))      $success = false;

            // The active project has been delete?
            if ($active_id == $pk) $this->setActive( array('id' => 0) );
        }

        return $success;
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
	 * @param    string     $alias    The alias
	 * @param    string     $title    The title
	 * @return	 array                Contains the modified title and alias
	 */
	protected function generateNewTitle($alias, $title)
	{
		// Alter the title & alias
		$table = $this->getTable();

		while ($table->load(array('alias' => $alias)))
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

		return array($title, $alias);
	}


    /**
    * Method to generate a new access level for a project
    *
    * @param    string    $title    The project title
    * @param    array     $rules    Optional associated user groups
    *
    * @return   integer             The access level id
    **/
    protected function saveAccessLevel($title, $tmp_rules = array())
    {
        // Get user viewing level model
        JModel::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_users/models');

        $model = JModel::getInstance('Level', 'UsersModel');


        // Trim project name if too long for access level
        if(strlen($title) > 100) $title = substr($title, 0, 97).'...';


        // Filter out groups from the permission rules
        $rules = array();
        foreach($tmp_rules AS $key => $value)
        {
            if(is_numeric($key) && is_numeric($value)) $rules[] = $value;
        }


        // Set access level data
        $data = array('id' => 0, 'title' => $title, 'rules' => $rules);


        // Store access level
        if(!$model->save($data)) return false;


        return $model->getState('level.id');
    }


    /**
	 * Method to test whether a record can be deleted.
	 *
	 * @param	  object     $record    A record object.
	 * @return    boolean	            True if allowed to delete the record.
     *                                  Defaults to the permission set in the component.
	 */
	protected function canDelete($record)
	{
	    $asset = 'com_projectfork.project';

		if (!empty($record->id)) {
			if ($record->state != -2) return ;

			$user = JFactory::getUser();
			return ($user->authorise('core.delete', $asset.'.'.(int) $record->id) || $user->authorise('project.delete', $asset.'.'.(int) $record->id));
		}
	}


	/**
	 * Method to test whether a record can have its state edited.
	 *
	 * @param	  object     $record    A record object.
	 * @return    boolean	            True if allowed to delete the record.
     *                                  Defaults to the permission set in the component.
	 */
	protected function canEditState($record)
	{
		$user  = JFactory::getUser();
        $asset = 'com_projectfork.project';

		// Check for existing item.
		if (!empty($record->id)) {
			return ($user->authorise('core.edit.state', $asset.'.'.(int) $record->id) || $user->authorise('project.edit.state', $asset.'.'.(int) $record->id));
		}
		else {
			return parent::canEditState('com_projectfork');
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
		$data = JFactory::getApplication()->getUserState('com_projectfork.edit.project.data', array());

		if(empty($data)) $data = $this->getItem();

		return $data;
	}
}
