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


/**
 * Dashboard Helper Class
 *
 */
abstract class PFrepoHelperDashboard
{
    /**
     * Returns a list of buttons for the frontend
     *
     * @return    array
     */
    public static function getSiteButtons()
    {
        $user = JFactory::getUser();
        $app  = JFactory::getApplication();
        $pid  = (int) $app->getUserState('com_projectfork.project.active.id');

        $buttons = array();

        if (!$pid || defined('PFDEMO')) return $buttons;

        // Get the project root dir
        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->select('attribs')
              ->from('#__pf_projects')
              ->where('id = ' . $pid);

        $db->setQuery($query);
        $project_attribs = $db->loadResult();

        $project_params = new JRegistry;
        $project_params->loadString($project_attribs);

        $repo_dir = (int) $project_params->get('repo_dir');
        if (!$repo_dir) return $buttons;

        // Get the access of the dir
        $query->clear()
              ->select('access')
              ->from('#__pf_repo_dirs')
              ->where('id = ' . $repo_dir);

        $db->setQuery($query);
        $access = (int) $db->loadResult();

        // Check viewing access
        if (!in_array($access, $user->getAuthorisedViewLevels()) && !$user->authorise('core.admin')) {
            return $buttons;
        }

        // Check permission
        if (!$user->authorise('core.create', 'com_pfrepo.directory.' . $repo_dir)) {
            return $buttons;
        }

		$buttons[] = array(
            'title' => 'MOD_PF_DASH_BUTTONS_ADD_FILE',
            'link'  => PFrepoHelperRoute::getRepositoryRoute($pid, $repo_dir) . '&task=fileform.add',
            'icon'  => JHtml::image('com_projectfork/projectfork/header/icon-48-repoform.add.png', JText::_('MOD_PF_DASH_BUTTONS_ADD_FILE'), null, true)
        );

        return $buttons;
    }


    /**
     * Returns a list of buttons for the backend
     *
     * @return    array
     */
    public static function getAdminButtons()
    {
        $user    = JFactory::getUser();
        $buttons = array();

        if ($user->authorise('core.manage', 'com_pfrepo')) {
            $buttons[] = array(
                'title' => 'COM_PROJECTFORK_SUBMENU_REPO',
                'link'  => 'index.php?option=com_pfrepo',
                'icon'  => JHtml::image('com_projectfork/projectfork/header/icon-48-repo.png', JText::_('COM_PROJECTFORK_SUBMENU_REPO'), null, true)
            );
        }

        return $buttons;
    }
}