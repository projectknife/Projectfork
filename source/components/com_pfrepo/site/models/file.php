<?php
/**
 * @package      pkg_projectfork
 * @subpackage   com_pfrepo
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2013 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


jimport('joomla.application.component.modelitem');


/**
 * Projectfork Component File Model
 *
 */
class PFrepoModelFile extends JModelItem
{
    /**
     * Model context string.
     *
     * @var    string
     */
    protected $_context = 'com_pfrepo.file';


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
                        'a.id, a.asset_id, a.project_id, a.title, a.alias, a.description, '
                        . 'a.created, a.created_by, a.modified_by, a.checked_out, a.checked_out_time, '
                        . 'a.attribs, a.access, a.file_name, a.file_extension, a.file_size, a.dir_id'
                    )
                );
                $query->from('#__pf_repo_files AS a');

                // Join on project table.
                $query->select('p.title AS project_title, p.alias AS project_alias');
                $query->join('LEFT', '#__pf_projects AS p on p.id = a.project_id');

                // Join on directories table.
                $query->select('d.title AS dir_title, d.alias AS dir_alias, d.path');
                $query->join('LEFT', '#__pf_repo_dirs AS d on d.id = a.dir_id');

                // Join on user table.
                $query->select('u.name AS author');
                $query->join('LEFT', '#__users AS u on u.id = a.created_by');

                $query->where('a.id = ' . (int) $pk);

                $db->setQuery($query);
                $data = $db->loadObject();

                if ($error = $db->getErrorMsg()) throw new Exception($error);

                if (empty($data)) {
                    return JError::raiseError(404, JText::_('COM_PROJECTFORK_ERROR_FILE_NOT_FOUND'));
                }

                // Generate slugs
                $data->slug         = $data->alias         ? ($data->id . ':' . $data->alias)                 : $data->id;
                $data->project_slug = $data->project_alias ? ($data->project_id . ':' . $data->project_alias) : $data->project_id;
                $data->dir_slug     = $data->dir_alias     ? ($data->dir_id . ':' . $data->dir_alias)         : $data->dir_id;

                // Convert parameter fields to objects.
                $registry = new JRegistry;
                $registry->loadString($data->attribs);

                $params = $this->getState('params');

                if ($params) {
                    $data->params = clone $this->getState('params');
                    $data->params->merge($registry);
                }
                else {
                    $data->params = $registry;
                }

                // Get the pyhsical location
                $data->physical_path = PFrepoHelper::getFilePath($data->file_name, $data->dir_id);

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
        $pk = JRequest::getUint('id');
        $this->setState($this->getName() . '.id', $pk);

        // Load the parameters.
        $params = JFactory::getApplication('site')->getParams();
        $this->setState('params', $params);
    }
}
