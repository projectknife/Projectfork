<?php
/**
 * @package      Projectfork
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


jimport('joomla.application.component.modellist');
jimport('joomla.application.component.helper');


/**
 * This models supports retrieving lists of milestones.
 *
 */
class ProjectforkModelRepository extends JModelList
{

    /**
     * Constructor.
     *
     * @param    array          $config    An optional associative array of configuration settings.
     * @see      jcontroller
     */
    public function __construct($config = array())
    {
        // Register dependencies
        JLoader::register('ProjectforkHelper',       JPATH_ADMINISTRATOR . '/components/com_projectfork/helpers/projectfork.php');
        JLoader::register('ProjectforkHelperAccess', JPATH_ADMINISTRATOR . '/components/com_projectfork/helpers/access.php');

        // Set field filter
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                'a.id', 'a.title', 'a.alias', 'a.created', 'a.created_by',
                'a.modified', 'a.modified_by', 'a.checked_out',
                'a.checked_out_time', 'a.attribs', 'a.access',
                'access_level'
            );
        }

        parent::__construct($config);
    }


    /**
     * Method to get an array of data items.
     *
     * @return    mixed    An array of data items on success, false on failure.
     */
    public function getItems()
    {
        // Get a storage key.
        $store = $this->getStoreId();

        // Try to load the data from internal storage.
        if (isset($this->cache[$store])) {
            return $this->cache[$store];
        }

        // Load the items
        $items  = array();
        $parent = (int) $this->getState('filter.parent_id', 1);
        $dir    = $this->getInstance('DirectoryForm', 'ProjectforkModel', $config = array('ignore_request' => true));
        $dirs   = $this->getInstance('Directories', 'ProjectforkModel', $config = array());
        $notes  = $this->getInstance('Notes', 'ProjectforkModel', $config = array());
        $files  = $this->getInstance('Files', 'ProjectforkModel', $config = array());

        $items['directory']   = $dir->getItem($parent);
        $items['directories'] = $dirs->getItems();
        $items['notes']       = $notes->getItems();
        $items['files']       = $files->getItems();

        // Add the items to the internal cache.
        $this->cache[$store] = $items;

        return $this->cache[$store];
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
        $access = ProjectforkHelperAccess::getActions(NULL, 0, true);

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
        $project = $app->getUserStateFromRequest('com_projectfork.project.active.id', 'filter_project', '');
        $this->setState('filter.project', $project);
        ProjectforkHelper::setActiveProject($project);

        // Filter - Parent id
        $parent_id = JRequest::getUInt('filter_parent_id', '');

        // Get the path
        $path = str_replace(':', '-', JRequest::getVar('path'));

        if (!$parent_id && !empty($path)  && $project > 0) {
            // No parent folder given. Try to find it from the path
             $dir = $this->getInstance('DirectoryForm', 'ProjectforkModel', $config = array('ignore_request' => true));
             $item = $dir->getItemFromProjectPath($project, $path);

             if ($item) {
                $parent_id = $item->id;
                JRequest::setVar('filter_parent_id', $parent_id);
             }
        }

        if (!$parent_id  && $project > 0) {
            // If no parent folder is given, find the repo dir of the project
            $params = ProjectforkHelper::getProjectParams();
            $repo = (int) $params->get('repo_dir');

            if ($repo) {
                $parent_id = $repo;
            }
        }
        elseif ($project === '0') {
            $parent_id = 1;
        }
        elseif ($parent_id && empty($project)) {
            // If a folder is selected, but no project, find the project id of the folder
            $dir  = $this->getInstance('DirectoryForm', 'ProjectforkModel', $config = array('ignore_request' => true));
            $item = $dir->getItem((int) $parent_id);

            if ($item->id > 0) {
                if ($item->parent_id == '1') {
                    $project = $item->project_id;
                    ProjectforkHelper::setActiveProject($project);
                    $this->setState('filter.project', $project);
                }
                else {
                    $parent_id = 1;
                }
            }
            else {
                $parent_id = 1;
            }
        }

        $this->setState('filter.parent_id',  $parent_id);

        JRequest::setVar('filter_parent_id', $parent_id);
        JRequest::setVar('filter_project',   $project);

        // Filter - Is set
        $this->setState('filter.isset', (!empty($search)));

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
        $id .= ':' . $this->getState('filter.search');
        $id .= ':' . $this->getState('filter.parent_id');

        return parent::getStoreId($id);
    }
}
