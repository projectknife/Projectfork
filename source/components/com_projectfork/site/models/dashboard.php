<?php
/**
 * @package      Projectfork
 * @subpackage   Dashboard
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


jimport('joomla.application.component.modelitem');


/**
 * Projectfork Component Dashboard Model
 *
 */
class ProjectforkModelDashboard extends JModelItem
{
    /**
     * Model context string.
     *
     * @var    string
     */
    protected $_context = 'com_projectfork.dashboard';


    /**
     * Method to get the data of a project.
     *
     * @param     integer    The id of the item.
     *
     * @return    mixed      Item data object on success, false on failure.
     */
    public function &getItem($pk = null)
    {
        // Initialise variables.
        $pk = (!empty($pk)) ? $pk : (int) $this->getState('filter.project');

        if ($this->_item === null) $this->_item = array();

        if (!$pk) {
           $this->_item[$pk] = null;
           return $this->_item[$pk];
        }

        if (!isset($this->_item[$pk])) {
            try {
                $db    = $this->getDbo();
                $query = $db->getQuery(true);

                $query->select($this->getState(
                        'item.select',
                        'a.id, a.asset_id, a.title, a.alias, a.description AS text, '
                        . 'a.created, a.created_by, a.modified_by, a.checked_out, a.checked_out_time, '
                        . 'a.attribs, a.access, a.state, a.start_date, a.end_date'
                    )
                );
                $query->from('#__pf_projects AS a');

                // Join on user table.
                $query->select('u.name AS author')
                      ->join('LEFT', '#__users AS u on u.id = a.created_by')
                      ->where('a.id = ' . (int) $pk);

                $db->setQuery((string) $query);
                $data = $db->loadObject();

                if ($error = $db->getErrorMsg()) throw new Exception($error);

                if (empty($data)) {
                    return JError::raiseError(404, JText::_('COM_PROJECTFORK_ERROR_PROJECT_NOT_FOUND'));
                }

                // Convert parameter fields to objects.
                $registry = new JRegistry;
                $registry->loadString($data->attribs);

                $data->params = clone $this->getState('params');
                $data->params->merge($registry);

                // Get the attachments
                if (PFApplicationHelper::exists('com_pfrepo')) {
                    $attachments = $this->getInstance('Attachments', 'PFrepoModel');
                    $data->attachments = $attachments->getItems('com_pfprojects.project', $data->id);
                }
                else {
                    $data->attachments = array();
                }

                // Compute selected asset permissions.
                $user = JFactory::getUser();

                // Technically guest could edit the item, but lets not check that to improve performance a little.
                if (!$user->get('guest')) {
                    $uid    = $user->get('id');
                    $access = PFprojectsHelper::getActions($data->id);

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
                    $user   = JFactory::getUser();
                    $groups = $user->getAuthorisedViewLevels();

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
     * Get the return URL.
     *
     * @return    string    The return URL.
     */
    public function getReturnPage()
    {
        return base64_encode($this->getState('return_page'));
    }


    /**
     * Method to auto-populate the model state.
     * Note. Calling getState in this method will result in recursion.
     *
     * @return    void
     */
    protected function populateState()
    {
        $app = JFactory::getApplication();

        $id     = JRequest::getVar('id', null);
        $filter = JRequest::getVar('filter_project', null);

        if (!is_null($filter)) {
            $project = PFApplicationHelper::getActiveProjectId('filter_project');
        }
        elseif (!is_null($id)) {
            $project = PFApplicationHelper::getActiveProjectId('id');
            $this->setState('project.request', true);
        }
        else {
            $project = PFApplicationHelper::getActiveProjectId();
        }

        $this->setState('filter.project', $project);

        $return = JRequest::getVar('return', null, 'default', 'base64');
        $this->setState('return_page', base64_decode($return));

        // Load the parameters.
        $params = $app->getParams();
        $this->setState('params', $params);

        $this->setState('layout', JRequest::getCmd('layout'));
    }
}
