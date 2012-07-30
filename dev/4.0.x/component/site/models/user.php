<?php
/**
 * @package      Projectfork
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


require_once JPATH_ADMINISTRATOR . '/components/com_users/models/user.php';


/**
 * Projectfork User Model
 * Extends on the backend version of com_users
 *
 */
class ProjectforkModelUser extends UsersModelUser
{
    /**
     * Method to find all projects a user has access to
     *
     * @param              $pk    The user id
     * @return    array           The project IDs
     */
    public function getProjects($pk = NULL)
    {
        $user  = JFactory::getUser($pk);
        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);

        $access = ProjectforkHelperAccess::getActions();
        $groups    = implode(',', $user->getAuthorisedViewLevels());

        $query->select('id')
              ->from('#__pf_projects')
              ->where('access IN(' . $groups . ')');

        if (!$access->get('project.edit.state') && !$access->get('project.edit')) {
            $query->where('state = 1');
        }

        $db->setQuery((string) $query);
        $projects = (array) $db->loadResultArray();

        return $projects;
    }
}
