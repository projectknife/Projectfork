<?php
// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

class ProjectforkViewDashboard extends JView
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
		$acl  = ProjectforkHelper::getActions();
		$user = JFactory::getUser();
        
		JToolBarHelper::title(JText::_('COM_CONTENT_ARTICLES_TITLE'), 'article.png');

		if ($acl->get('core.admin')) {
			JToolBarHelper::preferences('com_projectfork');
			JToolBarHelper::divider();
		}

		JToolBarHelper::help('JHELP_CONTENT_ARTICLE_MANAGER');
	}
}
?>