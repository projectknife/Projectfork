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
 * This models supports retrieving lists of comments.
 *
 */
class ProjectforkModelComments extends JModelList
{
	/**
	 * Constructor.
	 *
	 * @param	array	An optional associative array of configuration settings.
	 * @see		        JController
	 */
	public function __construct($config = array())
	{
	    if (empty($config['filter_fields'])) {
			$config['filter_fields'] = array(
				'id', 'a.id',
				'title', 'a.title',
                'category_title', 'c.title',
				'created', 'a.created',
                'modified', 'a.modified',
                'state', 'a.state',
                'start_date', 'a.start_date',
                'end_date', 'a.end_date',
                'author_name',
                'editor',
                'access_level',
                'milestones',
                'tasks',
                'tasklists'
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
	protected function populateState($ordering = 'a.created', $direction = 'ASC')
	{
		$app  = JFactory::getApplication();
        $user = JFactory::getUser();

		// Query limit
		$value = JRequest::getUInt('limit', $app->getCfg('list_limit', 0));
		$this->setState('list.limit', $value);

        // Query limit start
		$value = JRequest::getUInt('limitstart', 0);
		$this->setState('list.start', $value);

        // Query order field
		$value = JRequest::getCmd('filter_order', 'a.parent_id, a.lft');
		if(!in_array($value, $this->filter_fields)) $order_col = 'a.parent_id, a.lft';
		$this->setState('list.ordering', $value);

        // Query order direction
		$value = JRequest::getCmd('filter_order_Dir', 'ASC');
		if(!in_array(strtoupper($value), array('ASC', 'DESC', ''))) $value = 'ASC';
		$this->setState('list.direction', $value);

        // Params
		$value = $app->getParams();
		$this->setState('params', $value);

        // State
        $value = JRequest::getCmd('filter_published', '');
        $this->setState('filter.published', $value);

        if ((!$user->authorise('core.edit.state', 'com_projectfork') && !$user->authorise('comment.edit.state', 'com_projectfork')) &&
            (!$user->authorise('core.edit', 'com_projectfork') && !$user->authorise('comment.edit', 'com_projectfork'))){
			// Filter on published for those who do not have edit or edit.state rights.
			$this->setState('filter.published', 1);
		}

        // Filter - Search
        $value = JRequest::getString('filter_search', '');
        $this->setState('filter.search', $value);

        // Filter - Author
        $value = JRequest::getCmd('filter_author', '');
        $this->setState('filter.author', $value);

        // Filter - Project
        $value = JRequest::getUInt('filter_project', '');
        $this->setState('filter.project', $value);

        // Filter - Context
        $value = JRequest::getCmd('filter_context', '');
        $this->setState('filter.context', $value);

        // Filter - Item ID
        $value = JRequest::getUInt('filter_item_id', '');
        $this->setState('filter.id', $value);

        // View Layout
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
		// Compile the store id
        $id .= ':'.$this->getState('filter.published');
        $id .= ':'.$this->getState('filter.author');
        $id .= ':'.$this->getState('filter.project');
        $id .= ':'.$this->getState('filter.context');
        $id .= ':'.$this->getState('filter.id');
        $id .= ':'.$this->getState('filter.search');

		return parent::getStoreId($id);
	}


	/**
	 * Get the master query for retrieving a list of items subject to the model state.
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
			$this->getState('list.select',
			    'a.id, a.project_id, a.item_id, a.title, a.context, '
              . 'a.description, a.created,  a.created_by, a.modified, a.modified_by, '
              . 'a.checked_out,  a.checked_out_time, a.attribs, a.state, '
              . 'a.parent_id, a.lft, a.rgt'
			)
		);

		$query->from('#__pf_comments AS a');

        // Add the level in the tree.
		$query->select('COUNT(DISTINCT c2.id) AS level');
		$query->join('LEFT OUTER', $db->quoteName('#__pf_comments').' AS c2 ON a.lft > c2.lft AND a.rgt < c2.rgt');

        // Join over the users for the checked out user.
		$query->select('uc.name AS editor');
		$query->join('LEFT', '#__users AS uc ON uc.id = a.checked_out');

        // Join over the users for the owner.
		$query->select('ua.name AS author_name, ua.email AS author_email');
		$query->join('LEFT', '#__users AS ua ON ua.id = a.created_by');

        // Join over the projects for the project title.
		$query->select('p.title AS project_title');
		$query->join('LEFT', '#__pf_projects AS p ON p.id = a.project_id');

        // Filter by published state
		$published = $this->getState('filter.published');
		if (is_numeric($published)) {
			$query->where('a.state = ' . (int) $published);
		}
		elseif ($published === '') {
			$query->where('(a.state = 0 OR a.state = 1)');
		}

        // Filter by author
		$author_id = $this->getState('filter.author');
		if (is_numeric($author_id)) {
			$type = $this->getState('filter.author_id.include', true) ? '= ' : '<>';
			$query->where('a.created_by ' . $type . (int) $author_id);
		}

        // Filter by context
		$context = $this->getState('filter.context');
		if ($context != '') {
			$query->where('a.context = '.$db->quote($context));
		}

        // Filter by item id
		$item_id = $this->getState('filter.id');
		if (is_numeric($item_id)) {
			$query->where('a.item_id = ' . (int) $item_id);
		}

        // Filter by project id
		$project = $this->getState('filter.project');
		if (is_numeric($project)) {
			$query->where('a.project_id = ' . (int) $project);
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
				$query->where('(a.item_title LIKE '.$search.')');
			}
		}

		// Add the list ordering clause.
        $query->group('a.id, a.lft, a.rgt, a.parent_id, a.context');
		//$query->order($this->getState('list.ordering', 'a.parent_id, a.lft').' '.$this->getState('list.direction', 'ASC'));
		$query->order('a.lft'.' '.$this->getState('list.direction', 'ASC'));

		return $query;
	}


	/**
	 * Method to get a list of items.
	 * Overriden to inject convert the attribs field into a JParameter object.
	 *
	 * @return	mixed	An array of objects on success, false on failure.
	 */
	public function getItems()
	{
		$items = parent::getItems();

		// Get the global params
		$global_params = JComponentHelper::getParams('com_projectfork', true);

		foreach ($items as $i => &$item)
		{
            $params = new JRegistry;
			$params->loadString($item->attribs);

            // Convert the parameter fields into objects.
			$items[$i]->params = clone $params;

            // Create slug
            $items[$i]->slug = $items[$i]->title ? ($items[$i]->id . ':' . JApplication::stringURLSafe($items[$i]->title)) : $items[$i]->id;
        }

		return $items;
	}


	public function getStart()
	{
		return $this->getState('list.start');
	}
}
