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

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.application.component.modellist');


/**
 * This models supports retrieving lists of task lists.
 *
 */
class ProjectforkModelTasklists extends JModelList
{

	/**
	 * Constructor.
	 *
	 * @param	array	An optional associative array of configuration settings.
	 * @see		JController
	 */
	public function __construct($config = array())
	{
	    if (empty($config['filter_fields'])) {
			$config['filter_fields'] = array(
				'id', 'a.id',
                'project_id', 'a.project_id',
                'milestone_id', 'a.milestone_id',
				'title', 'a.title',
				'description', 'a.description',
				'alias', 'a.alias',
				'created', 'a.created',
                'created_by', 'a.created_by',
                'modified', 'a.modified',
                'modified_by', 'a.modified_by',
                'checked_out', 'a.checked_out',
                'checked_out_time', 'a.checked_out_time',
                'attribs', 'a.attribs',
                'access', 'a.access', 'access_level',
                'state', 'a.state',
                'ordering', 'a.ordering',
                'project_title', 'p.title',
                'milestone_title', 'm.title'
			);
		}

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
		$app  = JFactory::getApplication();
        $user = JFactory::getUser();

		// List state information
		$value = JRequest::getUInt('limit', $app->getCfg('list_limit', 0));
		$this->setState('list.limit', $value);

		$value = JRequest::getUInt('limitstart', 0);
		$this->setState('list.start', $value);

		$order_col = JRequest::getCmd('filter_order', 'a.title');
		if(!in_array($order_col, $this->filter_fields)) $order_col = 'a.title';

		$this->setState('list.ordering', $order_col);

		$list_order	= JRequest::getCmd('filter_order_Dir', 'ASC');
		if(!in_array(strtoupper($list_order), array('ASC', 'DESC', ''))) $list_order = 'ASC';

		$this->setState('list.direction', $list_order);

		$params = $app->getParams();
		$this->setState('params', $params);

        $value = JRequest::getCmd('filter_published', '');
        $this->setState('filter.published', $value);

        if ((!$user->authorise('core.edit.state', 'com_projectfork') && !$user->authorize('tasklist.edit.state', 'com_projectfork')) &&
            (!$user->authorise('core.edit', 'com_projectfork') && !$user->authorize('tasklist.edit', 'com_projectfork'))){
			// filter on published for those who do not have edit or edit.state rights.
			$this->setState('filter.published', 1);
		}

		$this->setState('filter.access', true);

        $value = JRequest::getString('filter_search', '');
        $this->setState('filter.search', $value);

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
        $id .= ':'.$this->getState('filter.published');

		return parent::getStoreId($id);
	}


	/**
	 * Get the master query for retrieving a list of milestones subject to the model state.
	 *
	 * @return	JDatabaseQuery
	 */
	function getListQuery()
	{
		// Create a new query object.
		$db    = $this->getDbo();
		$query = $db->getQuery(true);
        $user  = JFactory::getUser();


		// Select the required fields from the table.
		$query->select(
			$this->getState(
				'list.select',
				'a.id, a.project_id, a.milestone_id, a.title, a.alias, a.description, '
				. 'a.checked_out, a.checked_out_time, a.state, a.access, a.created, '
                . 'a.created_by, a.ordering, a.attribs'
			)
		);
		$query->from('#__pf_task_lists AS a');

		// Join over the users for the checked out user.
		$query->select('uc.name AS editor');
		$query->join('LEFT', '#__users AS uc ON uc.id=a.checked_out');

		// Join over the asset groups.
		$query->select('ag.title AS access_level');
		$query->join('LEFT', '#__viewlevels AS ag ON ag.id = a.access');

		// Join over the users for the author.
		$query->select('ua.name AS author_name');
		$query->join('LEFT', '#__users AS ua ON ua.id = a.created_by');

        // Join over the projects for the project title.
		$query->select('p.title AS project_title');
		$query->join('LEFT', '#__pf_projects AS p ON p.id = a.project_id');

        // Join over the milestones for the milestone title.
		$query->select('m.title AS milestone_title');
		$query->join('LEFT', '#__pf_milestones AS m ON m.id = a.milestone_id');

        // Implement View Level Access
		if(!$user->authorise('core.admin')) {
		    $groups	= implode(',', $user->getAuthorisedViewLevels());
			$query->where('a.access IN ('.$groups.')');
		}

        // Filter by published state
		$published = $this->getState('filter.published');

		if (is_numeric($published)) {
			$query->where('a.state = ' . (int) $published);
		}
		elseif ($published === '') {
			$query->where('(a.state = 0 OR a.state = 1)');
		}

        // Filter by search in title.
		$search = $this->getState('filter.search');
		if (!empty($search)) {
			if (stripos($search, 'id:') === 0) {
				$query->where('a.id = '.(int) substr($search, 4));
			}
			elseif (stripos($search, 'author:') === 0) {
				$search = $db->Quote('%'.$db->getEscaped(trim(substr($search, 8)), true).'%');
				$query->where('(ua.name LIKE '.$search.' OR ua.username LIKE '.$search.')');
			}
			else {
				$search = $db->Quote('%'.$db->getEscaped($search, true).'%');
				$query->where('(a.title LIKE '.$search.' OR a.alias LIKE '.$search.')');
			}
		}


		// Add the list ordering clause.
        $query->group('a.id');
		$query->order($this->getState('list.ordering', 'a.title').' '.$this->getState('list.direction', 'ASC'));

		return $query;
	}


	/**
	 * Method to get a list of projects.
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

        // Convert the parameter fields into objects.
		foreach ($items as $i => &$item)
		{
            $params = new JRegistry;
			$params->loadString($item->attribs);

			$items[$i]->params = clone $this->getState('params');
        }

		return $items;
	}


	public function getStart()
	{
		return $this->getState('list.start');
	}
}