<?php
/**
* @package   Projectfork
* @copyright Copyright (C) 2006-2011 Tobias Kuhn. All rights reserved.
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

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.application.component.modellist');


/**
 * This models supports retrieving lists of projects.
 *
 */
class ProjectforkModelProjects extends JModelList
{

	/**
	 * Constructor.
	 *
	 * @param	array	An optional associative array of configuration settings.
	 * @see		JController
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);
	}


	/**
	 * Method to auto-populate the model state.
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @return	void
	 */
	protected function populateState($ordering = 'title', $direction = 'ASC')
	{
		$app = JFactory::getApplication();

		// List state information
		$value = JRequest::getUInt('limit', $app->getCfg('list_limit', 0));
		$this->setState('list.limit', $value);

		$value = JRequest::getUInt('limitstart', 0);
		$this->setState('list.start', $value);

		$order_col = JRequest::getCmd('filter_order', 'p.title');
		if (!in_array($order_col, $this->filter_fields)) $order_col = 'p.title';

		$this->setState('list.ordering', $order_col);

		$list_order	= JRequest::getCmd('filter_order_dir', 'ASC');
		if (!in_array(strtoupper($list_order), array('ASC', 'DESC', ''))) $list_order = 'ASC';

		$this->setState('list.direction', $list_order);

		$params = $app->getParams();
		$this->setState('params', $params);

        $value = JRequest::getUInt('state', 0);
        $this->setState('filter.state', $value);

		$this->setState('filter.access', true);

		$this->setState('layout', JRequest::getCmd('layout'));
	}


	/**
	 * Method to get a store id based on model configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @param	string		$id	A prefix for the store id.
	 * @return	string		A store id.
	 */
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id .= ':'.$this->getState('filter.access');

		return parent::getStoreId($id);
	}


	/**
	 * Get the master query for retrieving a list of projects subject to the model state.
	 *
	 * @return	JDatabaseQuery
	 */
	function getListQuery()
	{
		// Create a new query object.
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		// Select the required fields from the table.
		$query->select(
			$this->getState('list.select',
			    'p.id, p.asset_id, p.title, p.alias, p.description, p.created,'
                . 'p.created_by, p.modified, p.modified_by, p.checked_out,'
                . 'p.checked_out_time, p.attribs, p.access, p.state, p.start_date,'
                . 'p.end_date'
			)
		);

		$query->from('#__pf_projects AS p');

		// Filter by access level.
		if ($access = $this->getState('filter.access')) {
			$user	= JFactory::getUser();
			$groups	= implode(',', $user->getAuthorisedViewLevels());

			$query->where('p.access IN ('.$groups.')');
		}

		// Add the list ordering clause.
		$query->order($this->getState('list.ordering', 'p.title').' '.$this->getState('list.direction', 'ASC'));

		return $query;
	}


	/**
	 * Method to get a list of articles.
	 *
	 * Overriden to inject convert the attribs field into a JParameter object.
	 *
	 * @return	mixed	An array of objects on success, false on failure.
	 */
	public function getItems()
	{
		$items	= parent::getItems();
		$user	= JFactory::getUser();
		$userId	= $user->get('id');
		$guest	= $user->get('guest');
		$groups	= $user->getAuthorisedViewLevels();

		// Get the global params
		$global_params = JComponentHelper::getParams('com_projectfork', true);

		return $items;
	}


	public function getStart()
	{
		return $this->getState('list.start');
	}
}