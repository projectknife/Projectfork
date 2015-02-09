<?php
/**
 * @package      pkg_projectfork
 * @subpackage   com_pftasks
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2013 Tobias Kuhn. All rights reserved.
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

        if ($this->_item === null) {
            $this->_item = array();
        }

        // Check if cached
        if (isset($this->_item[$pk])) {
            return $this->_item[$pk];
        }

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
            $item = $db->loadObject();

            // Check for error
            if ($error = $db->getErrorMsg()) throw new Exception($error);

            // Check if we have a record
            if (empty($item)) {
                return JError::raiseError(404, JText::_('COM_PROJECTFORK_ERROR_TASK_NOT_FOUND'));
            }

			// get tasks' labels
			$item->labels = null;
			$model_labels = $this->getInstance('Labels', 'PFModel');
			$item->labels = $model_labels->getConnections('com_pftasks.task', $item->id);
			
            // Check for published state if filter set.
            if (((is_numeric($published)) || (is_numeric($archived))) && (($item->state != $published) && ($item->state != $archived))) {
                return JError::raiseError(404, JText::_('COM_PROJECTFORK_ERROR_TASK_NOT_FOUND'));
            }

            // Convert parameter fields to objects.
            $registry = new JRegistry;
            $registry->loadString($item->attribs);

            $item->params = clone $this->getState('params');
            $item->params->merge($registry);


            // Get assigned users
            $ref = JModelLegacy::getInstance('UserRefs', 'PFusersModel');

            $item->users = $ref->getItems('com_pftasks.task', $item->id);


            // Get the attachments
            if (PFApplicationHelper::exists('com_pfrepo')) {
                $attachments = $this->getInstance('Attachments', 'PFrepoModel');
                $item->attachments = $attachments->getItems('com_pftasks.task', $item->id);
                $item->attachment  = $item->attachments;
            }
            else {
                $item->attachments = array();
                $item->attachment  = array();
            }


            // Generate slugs
            $item->slug           = $item->alias           ? ($item->id . ':' . $item->alias)                     : $item->id;
            $item->project_slug   = $item->project_alias   ? ($item->project_id . ':' . $item->project_alias)     : $item->project_id;
            $item->milestone_slug = $item->milestone_alias ? ($item->milestone_id . ':' . $item->milestone_alias) : $item->milestone_id;
            $item->list_slug      = $item->list_alias      ? ($item->list_id . ':' . $item->list_alias)           : $item->list_id;

            // Compute selected asset permissions.
            $user   = JFactory::getUser();
            $uid    = $user->get('id');
            $access = PFtasksHelper::getActions($item->id);

            $view_access = true;

            if ($item->access && !$user->authorise('core.admin')) {
                $view_access = in_array($item->access, $user->getAuthorisedViewLevels());
            }

            $item->params->set('access-view', $view_access);

            if (!$view_access) {
                $item->params->set('access-edit', false);
                $item->params->set('access-change', false);
            }
            else {
                // Check general edit permission first.
                if ($access->get('core.edit')) {
                    $item->params->set('access-edit', true);
                }
                elseif (!empty($uid) &&  $access->get('core.edit.own')) {
                    // Check for a valid user and that they are the owner.
                    if ($uid == $item->created_by) {
                        $item->params->set('access-edit', true);
                    }
                }

                // Check edit state permission.
                $item->params->set('access-change', $access->get('core.edit.state'));
            }

            $this->_item[$pk] = $item;
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
