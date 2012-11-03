<?php
/**
 * @package      Projectfork
 * @subpackage   Milestones
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


jimport('joomla.application.component.modelitem');


/**
 * Component Milestone Model
 *
 */
class PFmilestonesModelMilestone extends JModelItem
{
    /**
     * Model context string.
     *
     * @var    string
     */
    protected $_context = 'com_pfmilestones.milestone';


    /**
     * Method to get item data.
     *
     * @param     integer    The id of the item.
     *
     * @return    mixed      Menu item data object on success, false on failure.
     */
    public function &getItem($pk = null)
    {
        // Initialise variables.
        $pk = (!empty($pk)) ? $pk : (int) $this->getState('milestone.id');

        if ($this->_item === null) $this->_item = array();

        if (!isset($this->_item[$pk])) {

            try {
                $db    = $this->getDbo();
                $query = $db->getQuery(true);

                $query->select($this->getState(
                        'item.select',
                        'a.id, a.asset_id, a.project_id, a.title, a.alias, a.description AS text, '
                        . 'a.created, a.created_by, a.modified_by, a.checked_out, a.checked_out_time, '
                        . 'a.attribs, a.access, a.state, a.ordering, a.start_date, a.end_date'
                    )
                );
                $query->from('#__pf_milestones AS a');

                // Join on project table.
                $query->select('p.title AS project_title, p.alias AS project_alias');
                $query->join('LEFT', '#__pf_projects AS p on p.id = a.project_id');

                // Join on tasks table.
                $query->select('COUNT(DISTINCT t.id) AS tasks');
                $query->join('LEFT', '#__pf_tasks AS t on t.milestone_id = a.id');

                // Join on task lists table.
                $query->select('COUNT(DISTINCT l.id) AS lists');
                $query->join('LEFT', '#__pf_task_lists AS l on l.milestone_id = a.id');

                // Join on user table.
                $query->select('u.name AS author');
                $query->join('LEFT', '#__users AS u on u.id = a.created_by');

                $query->where('a.id = ' . (int) $pk);

                // Filter by published state.
                $published = $this->getState('filter.published');
                $archived  = $this->getState('filter.archived');

                if (is_numeric($published)) {
                    $query->where('(a.state = ' . (int) $published . ' OR a.state =' . (int) $archived . ')');
                }

                $db->setQuery($query);

                $data = $db->loadObject();

                if ($error = $db->getErrorMsg()) throw new Exception($error);

                if (empty($data)) {
                    return JError::raiseError(404, JText::_('COM_PROJECTFORK_ERROR_MILESTONE_NOT_FOUND'));
                }

                // Check for published state if filter set.
                if (((is_numeric($published)) || (is_numeric($archived))) && (($data->state != $published) && ($data->state != $archived))) {
                    return JError::raiseError(404, JText::_('COM_PROJECTFORK_ERROR_MILESTONE_NOT_FOUND'));
                }

                // Generate slugs
                $data->slug = $data->id.':' . $data->alias;
                $data->project_slug = $data->project_id.':' . $data->project_alias;

                // Convert parameter fields to objects.
                $registry = new JRegistry;
                $registry->loadString($data->attribs);

                $data->params = clone $this->getState('params');
                $data->params->merge($registry);

                // Compute selected asset permissions.
                // Technically guest could edit an article, but lets not check that to improve performance a little.
                if (!JFactory::getUser()->get('guest')) {
                    $uid    = JFactory::getUser()->get('id');
                    $access = PFmilestonesHelper::getActions($data->id);

                    // Check general edit permission first.
                    if ($access->get('core.edit')) {
                        $data->params->set('access-edit', true);
                    }
                    elseif (!empty($uid) && $access->get('core.edit.own')) {
                        // Now check if edit.own is available.
                        // Check for a valid user and that they are the owner.
                        if ($uid == $data->created_by) {
                            $data->params->set('access-edit', true);
                        }
                    }
                }

                // Compute view access permissions.
                if ($access = $this->getState('filter.access')) {
                    // If the access filter has been set, we already know this user can view.
                    $data->params->set('access-view', true);
                }
                else {
                    // If no access filter is set, the layout takes some responsibility for display of limited information.
                    $levels = JFactory::getUser()->getAuthorisedViewLevels();

                    $data->params->set('access-view', in_array($data->access, $levels));
                }

                $this->_item[$pk] = $data;
            }
            catch (JException $e)
            {
                if ($e->getCode() == 404) {
                    // Need to go thru the error handler to allow Redirect to work.
                    JError::raiseError(404, $e->getMessage());
                }
                else {
                    $this->setError($e);
                    $this->_item[$pk] = false;
                }
            }
        }

        return $this->_item[$pk];
    }


    /**
     * Method to auto-populate the model state.
     * Note. Calling getState in this method will result in recursion.
     *
     * @return    void
     */
    protected function populateState()
    {
        $app = JFactory::getApplication('site');

        // Load state from the request.
        $pk = JRequest::getInt('id');
        $this->setState('milestone.id', $pk);

        // Load the parameters.
        $params = $app->getParams();
        $this->setState('params', $params);

        // Adjust the state filter based on permissions
        $access = PFmilestonesHelper::getActions();
        if (!$access->get('core.edit.state') && !$access->get('core.edit')) {
            $this->setState('filter.published', 1);
            $this->setState('filter.archived', 2);
        }

        // Set the layout
        $this->setState('layout', JRequest::getCmd('layout'));
    }
}
