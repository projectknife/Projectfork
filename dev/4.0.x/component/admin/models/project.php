<?php
/**
* @package   Projectfork
* @copyright Copyright (C) 2006-2011 Tobias Kuhn. All rights reserved.
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
	public function getTable($type = 'Project', $prefix = 'JTable', $config = array())
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
		$form = $this->loadForm('com_projectfork.project', 'project', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form)) return false;

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
		$data = JFactory::getApplication()->getUserState('com_projectfork.edit.project.data', array());

		if(empty($data)) $data = $this->getItem();

		return $data;
	}


	/**
	 * Method to save the form data.
	 *
	 * @param     array	     The form data
	 * @return    boolean    True on success
	 */
	public function save($data)
	{
		// Alter the title for save as copy
		if (JRequest::getVar('task') == 'save2copy') {
			list($title,$alias) = $this->generateNewTitle($data['alias'], $data['title']);
			$data['title']	= $title;
			$data['alias']	= $alias;
		}
        else {
            // Always re-generate the alias unless save2copy
            $data['alias'] = '';
        }

        // Store the record
		if (parent::save($data)) return true;

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
	 * @param    string     $alias    The alias
	 * @param    string     $title    The title
	 * @return	 array                Contains the modified title and alias
	 */
	protected function generateNewTitle($alias, $title)
	{
		// Alter the title & alias
		$table = $this->getTable();
		while ($table->load(array('alias' => $alias))) {
			$m = null;
			if (preg_match('#-(\d+)$#', $alias, $m)) {
				$alias = preg_replace('#-(\d+)$#', '-'.($m[1] + 1).'', $alias);
			} else {
				$alias .= '-2';
			}
			if (preg_match('#\((\d+)\)$#', $title, $m)) {
				$title = preg_replace('#\(\d+\)$#', '('.($m[1] + 1).')', $title);
			} else {
				$title .= ' (2)';
			}
		}

		return array($title, $alias);
	}
}