<?php
/**
 * @package      Projectfork
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


class ProjectforkHelper
{
    /**
     * The component name
     *
     * @var    string
     */
    public static $extension = 'com_projectfork';


    /**
     * Configure the Linkbar.
     *
     * @param     string    $view    The name of the active view.
     *
     * @return    void
     */
    public static function addSubmenu($view)
    {
        if (version_compare(JVERSION, '3.0.0', 'ge')) {
            $views = array('dashboard', 'projects', 'categories', 'milestones', 'tasklists', 'tasks',
                'timesheet', 'repository', 'topics', 'replies', 'comments', '');

            if (!in_array($view, $views)) {
                return;
            }
        }

        JSubMenuHelper::addEntry(
            JText::_('COM_PROJECTFORK_SUBMENU_DASHBOARD'),
            'index.php?option=com_projectfork&view=dashboard',
            ($view == 'dashboard')
        );

        JSubMenuHelper::addEntry(
            JText::_('COM_PROJECTFORK_SUBMENU_PROJECTS'),
            'index.php?option=com_projectfork&view=projects',
            ($view == 'projects')
        );

        if ($view == 'projects' || $view == 'categories') {
                JSubMenuHelper::addEntry(
                JText::_('COM_PROJECTFORK_SUBMENU_CATEGORIES'),
                'index.php?option=com_categories&extension=com_projectfork',
                ($view == 'categories')
            );
        }

        JSubMenuHelper::addEntry(
            JText::_('COM_PROJECTFORK_SUBMENU_MILESTONES'),
            'index.php?option=com_projectfork&view=milestones',
            ($view == 'milestones')
        );

        JSubMenuHelper::addEntry(
            JText::_('COM_PROJECTFORK_SUBMENU_TASKLISTS'),
            'index.php?option=com_projectfork&view=tasklists',
            ($view == 'tasklists')
        );

        JSubMenuHelper::addEntry(
            JText::_('COM_PROJECTFORK_SUBMENU_TASKS'),
            'index.php?option=com_projectfork&view=tasks',
            ($view == 'tasks')
        );

        JSubMenuHelper::addEntry(
            JText::_('COM_PROJECTFORK_SUBMENU_TIME_TRACKING'),
            'index.php?option=com_projectfork&view=timesheet',
            ($view == 'timesheet')
        );

        JSubMenuHelper::addEntry(
            JText::_('COM_PROJECTFORK_SUBMENU_REPO'),
            'index.php?option=com_projectfork&view=repository',
            ($view == 'repository')
        );

        JSubMenuHelper::addEntry(
            JText::_('COM_PROJECTFORK_SUBMENU_DISCUSSIONS'),
            'index.php?option=com_projectfork&view=topics',
            ($view == 'topics')
        );

        if ($view == 'replies') {
            $topic  = JRequest::getUint('filter_topic', 0);
            $append = '';

            if ($append) $append .= '&filter_topic=' . $topic;

            $class::addEntry(
                JText::_('COM_PROJECTFORK_SUBMENU_REPLIES'),
                'index.php?option=com_projectfork&view=replies' . $append,
                ($view == 'replies')
            );
        }

        JSubMenuHelper::addEntry(
            JText::_('COM_PROJECTFORK_SUBMENU_COMMENTS'),
            'index.php?option=com_projectfork&view=comments',
            ($view == 'comments')
        );
    }


    /**
     * Method to get the Projectfork config settings merged into
     * the project settings
     *
     * @param     integer    $id        Optional project id. If not provided, will use the currently active project
     *
     * @return    object     $params    The config settings
     */
    public function getProjectParams($id = 0)
    {
        static $cache = array();

        $project = ($id > 0) ? (int) $id : ProjectforkHelper::getActiveProjectId();

        if (array_key_exists($project, $cache)) {
            return $cache[$project];
        }

        $params = JComponentHelper::GetParams('com_projectfork');

        // Get the project parameters if they exist
        if ($project) {
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);

            $query->select('attribs')
                  ->from('#__pf_projects')
                  ->where('id = ' . $db->quote($project));

            $db->setQuery((string) $query);
            $attribs = $db->loadResult();

            if (!empty($attribs)) {
                $registry = new JRegistry();
                $registry->loadString($attribs);

                $params->merge($registry);
            }
        }

        $cache[$project] = $params;

        return $cache[$project];
    }


    /**
     * Calculates and returns all available actions for the given asset
     *
     * @deprecated
     *
     * @param         string     $asset_name    Optional asset item name
     * @param         integer    $asset_id      Optional asset id
     *
     * @return        object
     */
    public static function getActions($asset_name = NULL, $asset_id = 0)
    {
        JLoader::register('ProjectforkHelperAccess', JPATH_ADMINISTRATOR . '/components/com_projectfork/helpers/access.php');

        $actions = ProjectforkHelperAccess::getActions($asset_name, $asset_id);

        return $actions;
    }


    /**
     * Method to get the changes between two item objects
     *
     * @param     object    $old        The old item object
     * @param     object    $new        The new/updated item object
     * @param     array     $prop       The field/comparison method pairs
     *
     * @return    array     $changes    The changed field values
     */
    public static function getItemChanges($old, $new, $props)
    {
        $changes   = array();
        $old_props = get_object_vars($old);
        $new_props = get_object_vars($new);

        foreach($props AS $prop)
        {
            if (!is_array($prop)) {
                $prop = array($prop, 'NE');
            }

            if (count($prop) != 2) continue;

            list($name, $cmp) = $prop;

            if (!array_key_exists($name, $new_props) || !array_key_exists($name, $old_props)) {
                continue;
            }

            switch (strtoupper($cmp))
            {
                case 'NE-SQLDATE':
                    // Not equal, not sql null date
                    if ($new->$name != $old->$name && $new->$name != JFactory::getDbo()->getNullDate()) {
                        $changes[$name] = $new->$name;
                    }
                    break;

                case 'NE':
                default:
                    // Default, not equal
                    if ($new->$name != $old->$name) {
                        $changes[$name] = $new->$name;
                    }
                    break;
            }
        }

        return $changes;
    }


    /**
     * Sets the currently active project for the user.
     * The active project serves as a global data filter.
     *
     * @param     int        $id    The project id
     *
     * @return    boolean           True on success, False on error
     **/
    public static function setActiveProject($id = 0)
    {
        static $model = null;

        if (!$model) {
            if (JFactory::getApplication()->isSite()) {
                JLoader::register('ProjectforkModelProjectForm', JPATH_BASE . '/components/com_projectfork/models/projectform.php');
                $model = new ProjectforkModelProjectForm(array('ignore_request' => true));
            }
            else {
                JLoader::register('ProjectforkModelProject', JPATH_ADMINISTRATOR . '/components/com_projectfork/models/project.php');
                $model = new ProjectforkModelProject(array('ignore_request' => true));
            }
        }

        $current = self::getActiveProjectId();

        if ($current == $id) {
            return true;
        }

        $result = $model->setActive(array('id' => (int) $id));

        if (!$result) {
            JFactory::getApplication()->enqueueMessage($model->getErrorMsg(), 'error');
        }
        else {
            if ($id) {
                $title = self::getActiveProjectTitle();
                $msg = JText::sprintf('COM_PROJECTFORK_INFO_NEW_ACTIVE_PROJECT', '"' . $title . '"');
                JFactory::getApplication()->enqueueMessage($msg);
            }
        }

        return $result;
    }


    /**
     * Returns the currently active project ID of the user.
     *
     * @param     string    $request    The name of the variable passed in a request.
     *
     * @return    int                   The project id
     **/
    public function getActiveProjectId($request = NULL)
    {
        $app = JFactory::getApplication();

        $old_state = $app->getUserState('com_projectfork.project.active.id');
        $cur_state = (!is_null($old_state)) ? $old_state : '';

        if (!empty($request)) {
            $new_state = JRequest::getVar($request, null, 'default');

            if (!is_null($new_state)) {
                $result = self::setActiveProject($new_state);

                if (!$result) {
                    return $cur_state;
                }

                return $new_state;
            }
        }

        return $cur_state;
    }


    /**
     * Returns the currently active project title of the user.
     *
     * @param     string    $alt    Alternative value of no project is set
     *
     * @return    string            The project title
     **/
    public function getActiveProjectTitle($alt = '')
    {
        if ($alt) $alt = JText::_($alt);

        $title = JFactory::getApplication()->getUserState('com_projectfork.project.active.title', $alt);

        return $title;
    }
}
