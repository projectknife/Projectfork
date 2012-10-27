<?php
/**
 * @package      Projectfork
 * @subpackage   Tasks
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


jimport('joomla.application.component.modelitem');


/**
 * Projectfork Component Task Model
 *
 */
class PFtasksModelTask extends JModelItem
{
    /**
     * Model context string.
     *
     * @var    string
     */
    protected $_context = 'com_pftasks.task';


    /**
     * Method to get item data.
     *
     * @param     integer    The id of the item.
     * @return    mixed      Menu item data object on success, false on failure.
     */
    public function &getItem($pk = null)
    {
        // Initialise variables.
        $pk = (!empty($pk)) ? $pk : (int) $this->getState($this->getName() . '.id');

        if ($this->_item === null) $this->_item = array();

        if (!isset($this->_item[$pk])) {

            try {
                $db    = $this->getDbo();
                $query = $db->getQuery(true);

                $query->select($this->getState(
                        'item.select',
                        'a.id, a.asset_id, a.project_id, a.milestone_id, a.list_id, a.title, a.alias, a.description AS text, '
                        . 'a.created, a.created_by, a.modified_by, a.checked_out, a.checked_out_time, '
                        . 'a.attribs, a.access, a.state, a.ordering, a.start_date, a.end_date'
                    )
                );
                $query->from('#__pf_tasks AS a');

                // Join on project table.
                $query->select('p.title AS project_title, p.alias AS project_alias');
                $query->join('LEFT', '#__pf_projects AS p on p.id = a.project_id');

                // Join on milestone table.
                $query->select('m.title AS milestone_title, m.alias AS milestone_alias');
                $query->join('LEFT', '#__pf_milestones AS m on m.id = a.milestone_id');

                // Join on task lists table.
                $query->select('l.title AS list_title, l.alias AS list_alias');
                $query->join('LEFT', '#__pf_task_lists AS l on l.id = a.list_id');

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
                    return JError::raiseError(404, JText::_('COM_PROJECTFORK_ERROR_TASK_NOT_FOUND'));
                }

                // Check for published state if filter set.
                if (((is_numeric($published)) || (is_numeric($archived))) && (($data->state != $published) && ($data->state != $archived))) {
                    return JError::raiseError(404, JText::_('COM_PROJECTFORK_ERROR_TASK_NOT_FOUND'));
                }

                // Generate slugs
                $data->slug           = $data->alias           ? ($data->id . ':' . $data->alias)                     : $data->id;
                $data->project_slug   = $data->project_alias   ? ($data->project_id . ':' . $data->project_alias)     : $data->project_id;
                $data->milestone_slug = $data->milestone_alias ? ($data->milestone_id . ':' . $data->milestone_alias) : $data->milestone_id;
                $data->list_slug      = $data->list_alias      ? ($data->list_id . ':' . $data->list_alias)           : $data->list_id;

                // Convert parameter fields to objects.
                $registry = new JRegistry;
                $registry->loadString($data->attribs);

                $data->params = clone $this->getState('params');
                $data->params->merge($registry);

                // Compute selected asset permissions.
                // Technically guest could edit an article, but lets not check that to improve performance a little.
                if (!JFactory::getUser()->get('guest')) {
                    $uid    = JFactory::getUser()->get('id');
                    $access = PFtasksHelper::getActions($data->id);

                    // Check general edit permission first.
                    if ($access->get('core.edit')) {
                        $data->params->set('access-edit', true);
                    }
                    // Now check if edit.own is available.
                    elseif (!empty($uid) && $access->get('core.edit.own')) {
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
                    $groups = JFactory::getUser()->getAuthorisedViewLevels();
                    $data->params->set('access-view', in_array($data->access, $groups));
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
        // Load state from the request.
        $pk = JRequest::getInt('id');
        $this->setState($this->getName() . '.id', $pk);

        $offset = JRequest::getUInt('limitstart');
        $this->setState('list.offset', $offset);

        // Load the parameters.
        $params = JFactory::getApplication('site')->getParams();
        $this->setState('params', $params);

        $access = PFtasksHelper::getActions();
        if (!$access->get('core.edit.state') && !$access->get('core.edit')) {
            $this->setState('filter.published', 1);
            $this->setState('filter.archived', 2);
        }
    }
}
