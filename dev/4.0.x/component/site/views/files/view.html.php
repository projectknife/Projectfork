<?php
// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

class ProjectforkViewFiles extends JView
{
	/**
	 * Display the view
	 */
	public function display($tpl = null)
	{
		$this->addToolbar();
		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @since	1.6
	 */
	protected function addToolbar()
	{
		JRequest::setVar('hidemainmenu', true);
	}
}
?>