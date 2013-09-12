<?php
/**
 * @package      pkg_projectfork
 * @subpackage   com_pfusers
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2013 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


jimport('joomla.application.component.modellist');
jimport('projectfork.application.helper');

/**
 * Methods supporting a list of users references.
 *
 */
class PFusersModelUserRef extends JModelList
{
    /**
     * Constructor
     *
     * @param    array    An optional associative array of configuration settings.
     */
    public function __construct($config = array())
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                'a.username'
            );
        }

        parent::__construct($config);
    }


    /**
     * Build an SQL query to load the list data.
     *
     * @return    jdatabasequery
     */
    protected function getListQuery()
    {
        $query = $this->_db->getQuery(true);

        // Get possible filters
        $filter_search = $this->getState('filter.search');
        $filter_access = (int) $this->getState('filter.access');
        $filter_groups = PFAccessHelper::getGroupsByAccessLevel($filter_access, true);

        if (!count($filter_groups)) {
            return $query;
        }

        // Select the required fields from the table.
        $query->select(
            $this->getState(
                'list.select',
                'a.id, a.username, a.name'
            )
        );

        $query->from('#__users AS a');

        // Join on user groups
        $query->join('INNER', '#__user_usergroup_map AS m ON m.user_id = a.id');
        $query->where('m.group_id IN(' . implode(',', $filter_groups) . ')');

        // Filter by search
        if (!empty($filter_search)) {
            if (stripos($filter_search, 'id:') === 0) {
                $query->where('a.id = '. (int) substr($filter_search, 3));
            }
            else {
                $search = $this->_db->quote('%' . $this->_db->escape($filter_search, true) . '%');
                $query->where('(a.name LIKE ' . $search . ' OR a.username LIKE ' . $search . ')');
            }
        }

        $order_col = $this->state->get('list.ordering', 'a.username');
        $order_dir = $this->state->get('list.direction', 'asc');

        $query->group('a.id');
        $query->order($this->_db->escape($order_col . ' ' . $order_dir));

        return $query;
    }


    /**
     * Method to get a store id based on model configuration state.
     *
     * This is necessary because the model is used by the component and
     * different modules that might need different sets of data or different
     * ordering requirements.
     *
     * @param     string    $id    A prefix for the store id.
     * @return    string           A store id.
     */
    protected function getStoreId($id = '')
    {
        // Compile the store id.
        $id .= ':' . $this->getState('filter.search');
        $id .= ':' . $this->getState('filter.access');

        return parent::getStoreId($id);
    }


    /**
     * Method to auto-populate the model state.
     * Note: Calling getState in this method will result in recursion.
     *
     * @return    void
     */
    protected function populateState($ordering = 'a.username', $direction = 'asc')
    {
        // Initialise variables.
        $app = JFactory::getApplication();

        // Adjust the context to support modal layouts.
        if ($layout = JRequest::getVar('layout')) $this->context .= '.' . $layout;

        // Filter - Search
        $search = JRequest::getCmd('filter_search');
        $this->setState('filter.search', $search);

        // Filter - Access
        $access = JRequest::getUInt('filter_access');
        $this->setState('filter.access', $access);

        // List state information.
        parent::populateState($ordering, $direction);
    }
}
