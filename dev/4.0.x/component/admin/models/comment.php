<?php
/**
* @package   Projectfork
* @copyright Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
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
 * Item Model for a Comment form.
 *
 */
class ProjectforkModelComment extends JModelAdmin
{
	/**
	 * @var    string    The prefix to use with controller messages.
	 */
	protected $text_prefix = 'COM_PROJECTFORK_COMMENT';


	/**
	 * Returns a Table object, always creating it.
	 *
	 * @param    type	   The table type to instantiate
	 * @param	 string    A prefix for the table class name. Optional.
	 * @param	 array	   Configuration array for model. Optional.
	 * @return	 JTable	   A database object
	 */
	public function getTable($type = 'Comment', $prefix = 'PFTable', $config = array())
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
	 * Method to get the record form.
	 *
	 * @param    array      $data		Data for the form.
	 * @param	 boolean    $loadData	True if the form is to load its own data (default case), false if not.
	 * @return	 mixed                  A JForm object on success, false on failure
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm('com_projectfork.comment', 'comment', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form)) return false;

        $jinput = JFactory::getApplication()->input;
        $user   = JFactory::getUser();
        $id     =  $jinput->get('id', 0);


        // Check for existing item.
		// Modify the form based on Edit State access controls.
		if ($id != 0 && (!$user->authorise('core.edit.state', 'com_projectfork.comment.'.(int) $id) && !$user->authorise('comment.edit.state', 'com_projectfork.comment.'.(int) $id))
		|| ($id == 0 && (!$user->authorise('core.edit.state', 'com_projectfork') && !$user->authorise('comment.edit.state', 'com_projectfork')))
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
	 * Custom clean the cache of com_projectfork and projectfork modules
	 *
	 */
	protected function cleanCache()
	{
		parent::cleanCache('com_projectfork');
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
	    $asset = 'com_projectfork.comment';

		if (!empty($record->id)) {
			if ($record->state != -2) return ;

			$user = JFactory::getUser();
			return ($user->authorise('core.delete', $asset.'.'.(int) $record->id) || $user->authorise('comment.delete', $asset.'.'.(int) $record->id));
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
        $asset = 'com_projectfork.comment';

		// Check for existing item.
		if (!empty($record->id)) {
			return ($user->authorise('core.edit.state', $asset.'.'.(int) $record->id) || $user->authorise('comment.edit.state', $asset.'.'.(int) $record->id));
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
		$data = JFactory::getApplication()->getUserState('com_projectfork.edit.comment.data', array());

		if(empty($data)) $data = $this->getItem();

		return $data;
	}
}
