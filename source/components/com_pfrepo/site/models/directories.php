<?php
/**
 * @package      Projectfork
 * @subpackage   Repository
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


jimport('joomla.application.component.modellist');
jimport('joomla.application.component.helper');


/**
 * This models supports retrieving lists of directory folders.
 *
 */
class PFrepoModelDirectories extends JModelList
{

    /**
     * Constructor.
     *
     * @param    array          $config    An optional associative array of configuration settings.
     * @see      jcontroller
     */
    public function __construct($config = array())
    {
        // Set field filter
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                'a.id', 'a.project_id', 'project_title',
                'a.title', 'a.description', 'a.created',
                'a.created_by', 'a.modified',
                'a.modified_by', 'a.checked_out',
                'a.checked_out_time', 'a.attribs',
                'a.access', 'access_level',
                'a.context, a.lft'
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
        $db    = $this->getDbo();
        $query = $db->getQuery(true);
        $user  = JFactory::getUser();

        // Select the required fields from the table.
        $query->select(
            $this->getState(
                'list.select',
                'a.id, a.project_id, a.title, a.alias, a.description, a.checked_out, '
                . 'a.checked_out_time, a.created, a.access, a.created_by, a.parent_id, '
                . 'a.lft, a.rgt, a.level, a.path, a.protected, a.attribs'
            )
        );
        $query->from('#__pf_repo_dirs AS a');

        // Do not include the root node
        $query->where('a.alias != ' . $db->quote('root'));

        // Join over the users for the checked out user.
        $query->select('uc.name AS editor');
        $query->join('LEFT', '#__users AS uc ON uc.id = a.checked_out');

        // Join over the asset groups.
        $query->select('ag.title AS access_level')
              ->join('LEFT', '#__viewlevels AS ag ON ag.id = a.access');

        // Join over the users for the author.
        $query->select('ua.name AS author_name');
        $query->join('LEFT', '#__users AS ua ON ua.id = a.created_by');

        // Join over the projects for the project title.
        $query->select('p.title AS project_title, p.alias AS project_alias');
        $query->join('LEFT', '#__pf_projects AS p ON p.id = a.project_id');

        // Join over the label refs for label count
        $query->select('COUNT(DISTINCT lbl.id) AS label_count');
        $query->join('LEFT', '#__pf_ref_labels AS lbl ON (lbl.item_id = a.id AND lbl.item_type = ' . $db->quote('directory') . ')');

        // Implement View Level Access
        if (!$user->authorise('core.admin')) {
            $groups = implode(',', $user->getAuthorisedViewLevels());
            $query->where('a.access IN (' . $groups . ')');
        }

        // Filter by parent_id
        $parent_id = $this->getState('filter.parent_id');
        $project   = $this->getState('filter.project');

        if (((!is_numeric($parent_id) || $parent_id == '0') && empty($search)) || !is_numeric($project)) {
            $this->setState('filter.parent_id', '1');
            $parent_id = '1';
        }

        if (is_numeric($parent_id)) {
            $query->where('a.parent_id = ' . $db->quote($parent_id));
        }

        // Filter labels
        if (count($this->getState('filter.labels'))) {
            $labels = $this->getState('filter.labels');

            JArrayHelper::toInteger($labels);

            if (count($labels) > 1) {
                $labels = implode(', ', $labels);
                $query->where('lbl.label_id IN (' . $labels . ')');
            }
            else {
                $labels = implode(', ', $labels);
                $query->where('lbl.label_id = ' . $db->quote((int) $labels));
            }
        }

        // Filter fields
        $filters = array();
        $filters['a.project_id'] = array('INT-NOTZERO', $this->getState('filter.project'));
        $filters['a.created_by'] = array('INT-NOTZERO', $this->getState('filter.author'));
        $filters['a']            = array('SEARCH',      $this->getState('filter.search'));

        // Apply Filter
        PFQueryHelper::buildFilter($query, $filters);

        // Add the list ordering clause.
        $order_col = $this->state->get('list.ordering', 'a.title');
        $order_dir = $this->state->get('list.direction', 'desc');

        if ($order_col != 'a.lft') {
            $order_col = $order_col .  ' ' . $order_dir . ', a.lft';
        }

        $query->order($db->escape($order_col . ' ' . $order_dir))
              ->group('a.id');

        return $query;
    }


    /**
     * Method to get a list of items.
     * Overriden to inject convert the attribs field into a JParameter object.
     *
     * @return    mixed    $items    An array of objects on success, false on failure.
     */
    public function getItems()
    {
        $items  = parent::getItems();
        $labels = $this->getInstance('Labels', 'PFModel');

        // Get the global params
        $global_params = JComponentHelper::getParams('com_pfrepo', true);

        foreach ($items as $i => &$item)
        {
            // Convert the parameter fields into objects.
            $params = new JRegistry;
            $params->loadString($item->attribs);

            $items[$i]->params = clone $this->getState('params');

            // Create slugs
            $items[$i]->slug         = $items[$i]->alias ? ($items[$i]->id . ':' . $items[$i]->alias) : $items[$i]->id;
            $items[$i]->project_slug = $items[$i]->project_alias ? ($items[$i]->project_id . ':' . $items[$i]->project_alias) : $items[$i]->project_id;

            // Get the labels
            if ($items[$i]->label_count > 0) {
                $items[$i]->labels = $labels->getConnections('directory', $items[$i]->id);
            }
        }

        return $items;
    }


    /**
     * Method to auto-populate the model state.
     * Note. Calling getState in this method will result in recursion.
     *
     * @return    void
     */
    protected function populateState($ordering = 'a.title', $direction = 'ASC')
    {
        $app    = JFactory::getApplication();
        $access = PFrepoHelper::getActions();

        // Adjust the context to support modal layouts.
        $layout = JRequest::getCmd('layout');

        // View Layout
        $this->setState('layout', $layout);
        if ($layout) $this->context .= '.' . $layout;

        // Params
        $value = $app->getParams();
        $this->setState('params', $value);

        // Filter - Search
        $search = JRequest::getString('filter_search', '');
        $this->setState('filter.search', $search);

        // Filter - Project
        $project = PFApplicationHelper::getActiveProjectId('filter_project');
        $this->setState('filter.project', $project);

        // Filter - Author
        $author = $app->getUserStateFromRequest($this->context . '.filter.author', 'filter_author', '');
        $this->setState('filter.author', $author);

        // Filter - Parent folder
        $parent_id = JRequest::getCmd('filter_parent_id', '');
        $this->setState('filter.parent_id', $parent_id);

        // Filter - Labels
        $labels = JRequest::getVar('filter_label', array());
        $this->setState('filter.labels', $labels);

        // Do not allow to filter by author if no project is selected
        if (!is_numeric($project) || intval($project) == 0) {
            $this->setState('filter.author', '');
            $this->setState('filter.labels', array());
            $author = '';
            $labels = array();
        }

        if (!is_array($labels)) {
            $labels = array();
        }

        // Filter - Is set
        $this->setState('filter.isset', (!empty($search) || is_numeric($author) || count($labels)));

        // Call parent method
        parent::populateState($ordering, $direction);
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
        $id .= ':' . $this->getState('filter.project');
        $id .= ':' . $this->getState('filter.author');
        $id .= ':' . $this->getState('filter.search');
        $id .= ':' . $this->getState('filter.parent_id');

        return parent::getStoreId($id);
    }
}
