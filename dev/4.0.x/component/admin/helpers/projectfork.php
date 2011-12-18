<?php
// No direct access
defined('_JEXEC') or die;


class ProjectforkHelper
{
	public static $extension = 'com_projectfork';

	/**
	 * Configure the Linkbar.
	 *
	 * @param	string	$vName	The name of the active view.
	 * @return	void
	 */
	public static function addSubmenu($vName)
	{
		JSubMenuHelper::addEntry(
			JText::_('COM_PROJECTFORK_SUBMENU_DASHBOARD'),
			'index.php?option=com_projectfork&view=dashboard',
			($vName == 'dashboard')
		);
		JSubMenuHelper::addEntry(
			JText::_('COM_PROJECTFORK_SUBMENU_PROJECTS'),
			'index.php?option=com_projectfork&view=projects',
			($vName == 'projects')
        );
        JSubMenuHelper::addEntry(
			JText::_('COM_PROJECTFORK_SUBMENU_MILESTONES'),
			'index.php?option=com_projectfork&view=milestones',
			($vName == 'milestones')
        );
	}


    public static function getActions()
	{
		$user	= JFactory::getUser();
		$result	= new JObject;
		$asset  = 'com_projectfork';

		$actions = array(
		    'core.admin',
            'core.manage',
            'core.create',
            'core.edit',
            'core.edit.own',
            'core.edit.state',
            'core.delete'
		);

		foreach ($actions as $action)
        {
			$result->set($action, $user->authorise($action, $asset));
		}

		return $result;
	}
}
?>