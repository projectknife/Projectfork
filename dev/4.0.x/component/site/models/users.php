<?php
/**
 * @package      Projectfork
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


// Base this on the backend users model
JLoader::register('UsersModelUsers', JPATH_ADMINISTRATOR . '/components/com_users/models/users.php');


/**
 * This models supports retrieving lists of users.
 * Extends on the backend version of com_users
 *
 */
class ProjectforkModelUsers extends UsersModelUsers
{
    /**
     * Constructor.
     *
     * @param    array          $config    An optional associative array of configuration settings.
     *
     * @see      jcontroller
     */
    public function __construct($config = array())
    {
        JLoader::register('ProjectforkHelper', JPATH_ADMINISTRATOR . '/components/com_projectfork/helpers/projectfork.php');

        parent::__construct($config);
    }


    /**
     * Method to auto-populate the model state.
     * Note. Calling getState in this method will result in recursion.
     *
     * @return    void
     */
    protected function populateState($ordering = null, $direction = null)
    {
        parent::populateState();

        $app    = JFactory::getApplication();
        $user   = JFactory::getUser();
        $model  = $this->getInstance('Projectform', 'ProjectforkModel', array('ignore_request' => true));
        $groups = array();

        // Filter - Project
        $pid = ProjectforkHelper::getActiveProjectId('filter_project');
        $this->setState('filter.project', $pid);

        // Override group filter by active project
        if ($pid) {
            $tmp_groups = $model->getUserGroups($pid);

            // Get group ids
            if (is_array($tmp_groups)) {
                foreach($tmp_groups AS $group)
                {
                    $groups[] = (int) $group;
                }
            }
        }
        else {
            // No active project. Filter by all accessible projects
            if (!$user->authorise('core.admin')) {
                $umodel   = JModel::getInstance('User', 'ProjectforkModel');
                $projects = $umodel->getProjects();

                foreach($projects AS $project)
                {
                    $tmp_groups = $model->getUserGroups($project);

                    if ($tmp_groups !== false) {
                        // Get group ids
                        if (is_array($tmp_groups)) {
                            foreach($tmp_groups AS $group)
                            {
                                $groups[] = (int) $group;
                            }
                        }
                    }
                }
            }
        }

        if (count($groups)) {
            $this->setState('filter.groups', $groups);
        }
        else {
            if (!$user->authorise('core.admin')) {
                $this->setState('filter.groups', array('1'));
            }
        }
    }
}
