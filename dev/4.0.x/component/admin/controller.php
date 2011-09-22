<?php
// no direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.controller');


class ProjectforkController extends JController
{
	/**
	 * @var		string	The default view.
	 * @since	1.6
	 */
	protected $default_view = 'dashboard';

	public function display($cachable = false, $urlparams = false)
	{
		require_once JPATH_COMPONENT.'/helpers/projectfork.php';

		// Load the submenu.
		ProjectforkHelper::addSubmenu(JRequest::getCmd('view', 'dashboard'));
        
		parent::display();

		return $this;
	}
}
?>