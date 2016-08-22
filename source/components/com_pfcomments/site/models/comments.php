<?php
/**
 * @package      pkg_projectfork
 * @subpackage   com_pfcomments
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2013 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


jimport('joomla.application.component.modellist');


/**
 * This models supports retrieving a list of comments.
 *
 */
class PFcommentsModelComments extends JModelList
{
    /**
     * Constructor.
     *
     * @param    array          An optional associative array of configuration settings.
     * @see      jcontroller
     */
    public function __construct($config = array())
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                'id', 'a.id',
                'title', 'a.title',
                'lft', 'a.lft',
                'created', 'a.created',
                'modified', 'a.modified',
                'state', 'a.state',
                'author_name',
                'editor',
            );
        }

        parent::__construct($config);
    }


    /**
     * Get the master query for retrieving a list of items subject to the model state.
     *
     * @return    jdatabasequery
     */
    public function getListQuery()
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
              . 'a.parent_id, a.lft, a.rgt, a.level'
            )
        );

        $query->from('#__pf_comments AS a');

        // Add the level in the tree.
        /*$query->select('COUNT(DISTINCT c2.id) AS level');
        $query->join('LEFT OUTER', $db->quoteName('#__pf_comments') . ' AS c2 ON '
                    . '(a.lft > c2.lft AND a.rgt < c2.rgt'
                    . (($context != '')       ? ' AND c2.context = ' . $db->quote($context) : '')
                    . ((is_numeric($item_id)) ? ' AND c2.item_id = ' . (int) $item_id       : '')
                    . ')');*/

        // Count the replies of each comment
        /*$query->select('COUNT(r.id) AS replies');
        $query->join('LEFT', $db->quoteName('#__pf_comments') . 'AS r on r.parent_id = a.id');*/

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

        // Filter by context
        $context = $this->getState('filter.context');

        if ($context != '') {
            $query->where('a.context = ' . $db->quote($context));
        }

        // Filter by item id
        $item_id = $this->getState('filter.item_id');

        if (is_numeric($item_id)) {
            $query->where('a.item_id = ' . (int) $item_id);
        }

        // Add the list ordering clause.
        $query->group('a.id');
        $query->order($this->getState('list.ordering', 'a.lft') . ' ' . $this->getState('list.direction', 'ASC'));

        return $query;
    }


    /**
     * Method to get a list of items.
     * Overriden to inject convert the attribs field into a JParameter object.
     *
     * @return    mixed    An array of objects on success, false on failure.
     */
    public function getItems()
    {
        $items = parent::getItems();

        // Get the global params
        $global_params = JComponentHelper::getParams('com_pfcomments', true);

        foreach ($items as $i => &$item)
        {
            $params = new JRegistry;
            $params->loadString($item->attribs);

            // Convert the parameter fields into objects.
            $items[$i]->params = clone $params;

            // Create slug
            // $items[$i]->slug = $items[$i]->title ? ($items[$i]->id . ':' . JApplication::stringURLSafe($items[$i]->title)) : $items[$i]->id;
            $items[$i]->slug = $items[$i]->id;
        }

        return $items;
    }


    /**
     * Method to auto-populate the model state.
     * Note. Calling getState in this method will result in recursion.
     *
     * @return    void
     */
    protected function populateState($ordering = 'a.lft', $direction = 'ASC')
    {
        $app = JFactory::getApplication();

        // Query limit
        $this->setState('list.limit', 0);

        // Query limit start
        $this->setState('list.start', 0);

        // Query order field
        $this->setState('list.ordering', $ordering);

        // Query order direction
        $params = JComponentHelper::getParams('com_pfcomments');
        $this->setState('list.direction', $params->get('filter_order_Dir', 'ASC'));

        // Params
        $value = $app->getParams();
        $this->setState('params', $value);

        // State
        $this->setState('filter.published', 1);

        // Filter - Project
        $value = PFApplicationHelper::getActiveProjectId();
        $this->setState('filter.project', $value);

        // Filter - Context
        $value = $app->getUserStateFromRequest($this->context . '.filter.context', 'filter_context', '');
        $this->setState('filter.context', $value);

        // Filter - Item ID
        $value = $app->getUserStateFromRequest($this->context . '.filter.item_id', 'filter_item_id', '');
        $this->setState('filter.item_id', $value);

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
     * @param     string    $id    A prefix for the store id.
     *
     * @return    string           A store id.
     */
    protected function getStoreId($id = '')
    {
        // Compile the store id
        $id .= ':' . $this->getState('filter.published');
        $id .= ':' . $this->getState('filter.project');
        $id .= ':' . $this->getState('filter.context');
        $id .= ':' . $this->getState('filter.id');

        return parent::getStoreId($id);
    }
}
