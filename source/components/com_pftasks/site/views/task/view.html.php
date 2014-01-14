<?php
/**
 * @package      pkg_projectfork
 * @subpackage   com_pftasks
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2013 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


jimport('joomla.application.component.view');


/**
 * HTML Task View class for the Projectfork component
 *
 */
class PFtasksViewTask extends JViewLegacy
{
	protected $item;
	protected $params;
	protected $print;
	protected $state;
	protected $user;
    protected $toolbar;


	function display($tpl = null)
	{
		// Initialise variables.
		$app		= JFactory::getApplication();
		$user		= JFactory::getUser();
		$userId		= $user->get('id');
		$dispatcher	= JDispatcher::getInstance();

		$this->item	 = $this->get('Item');
		$this->print = JRequest::getBool('print');
		$this->state = $this->get('State');
		$this->user  = $user;
        $this->toolbar = $this->getToolbar();

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseWarning(500, implode("\n", $errors));
			return false;
		}

        // Check the view access.
		if (!$this->item->params->get('access-view')) {
		    JError::raiseWarning(403, JText::_('JERROR_ALERTNOAUTHOR'));
			return false;
		}

        // Set active project
        if (!PFApplicationHelper::setActiveProject($this->item->project_id)) {
            JError::raiseWarning(403, JText::_('JERROR_ALERTNOAUTHOR'));
            return false;
        }

		// Merge item params.
		$this->params = $this->state->get('params');
		$active	      = $app->getMenu()->getActive();
		$temp	      = clone ($this->params);

		// Check to see which parameters should take priority
		if ($active) {
			$currentLink = $active->link;

			if (strpos($currentLink, 'view=task') && (strpos($currentLink, '&id='.(string) $this->item->id))) {
				$this->item->params->merge($temp);
				// Load layout from active query (in case it is an alternative menu item)
				if (isset($active->query['layout'])) $this->setLayout($active->query['layout']);
			}
			else {
				// Merge the menu item params with the milestone params so that the milestone params take priority
				$temp->merge($this->item->params);
				$this->item->params = $temp;

				// Check for alternative layouts (since we are not in a menu item)
				if ($layout = $this->item->params->get('task_layout')) $this->setLayout($layout);
			}
		}
		else {
			// Merge so that item params take priority
			$temp->merge($this->item->params);
			$this->item->params = $temp;

			// Check for alternative layouts (since we are not in a menu item)
			if ($layout = $this->item->params->get('task_layout')) $this->setLayout($layout);
		}

		$offset = $this->state->get('list.offset');

        // Fake some content item properties to avoid plugin issues
        $this->item->introtext = '';
        $this->item->fulltext  = '';

		// Process the content plugins.
		JPluginHelper::importPlugin('content');
		$results = $dispatcher->trigger('onContentPrepare', array ('com_pftasks.task', &$this->item, &$this->params, $offset));

		$this->item->event = new stdClass();
		$results = $dispatcher->trigger('onContentAfterTitle', array('com_pftasks.task', &$this->item, &$this->params, $offset));
		$this->item->event->afterDisplayTitle = trim(implode("\n", $results));

		$results = $dispatcher->trigger('onContentBeforeDisplay', array('com_pftasks.task', &$this->item, &$this->params, $offset));
		$this->item->event->beforeDisplayContent = trim(implode("\n", $results));

		$results = $dispatcher->trigger('onContentAfterDisplay', array('com_pftasks.task', &$this->item, &$this->params, $offset));
		$this->item->event->afterDisplayContent = trim(implode("\n", $results));


		// Escape strings for HTML output
		$this->pageclass_sfx = htmlspecialchars($this->item->params->get('pageclass_sfx'));

		$this->_prepareDocument();

		parent::display($tpl);
	}


	/**
	 * Prepares the document
     *
	 */
	protected function _prepareDocument()
	{
		$app	 = JFactory::getApplication();
		$menus	 = $app->getMenu();
        $menu    = $menus->getActive();
		$pathway = $app->getPathway();
		$title   = null;

		// Because the application sets a default page title,
		// we need to get it from the menu item itself
		if ($menu) {
			$this->params->def('page_heading', $this->params->get('page_title', $menu->title));
		}
		else {
			$this->params->def('page_heading', JText::_('COM_PROJECTFORK_TASK_TITLE'));
		}

		$title = $this->params->get('page_title', '');

		$id = (int) @$menu->query['id'];

		// If the menu item does not concern this item
		if($menu && ($menu->query['option'] != 'com_pftasks' || $menu->query['view'] != 'task' || $id != $this->item->id)) {
			// If this is not a single milestone menu item, set the page title to the milestone title
			if($this->item->title) $title = $this->item->title;

            $pid    = $this->item->project_id;
            $palias = $this->item->project_alias;

			$path   = array(array('title' => $this->item->title, 'link' => ''));
            $path[] = array('title' => $this->item->project_title, 'link' => JRoute::_("index.php?option=com_projectfork&view=dashboard&id=$pid:$palias"));

			$path = array_reverse($path);

			foreach($path as $item)
			{
				$pathway->addItem($item['title'], $item['link']);
			}
		}

		// Check for empty title and add site name if param is set
		if (empty($title)) {
			$title = $app->getCfg('sitename');
		}
		elseif ($app->getCfg('sitename_pagetitles', 0) == 1) {
			$title = JText::sprintf('JPAGETITLE', $app->getCfg('sitename'), $title);
		}
		elseif ($app->getCfg('sitename_pagetitles', 0) == 2) {
			$title = JText::sprintf('JPAGETITLE', $title, $app->getCfg('sitename'));
		}
		if (empty($title)) {
			$title = $this->item->title;
		}

		$this->document->setTitle($title);


		if ($this->params->get('robots'))      $this->document->setMetadata('robots', $this->params->get('robots'));
		if ($app->getCfg('MetaAuthor') == '1') $this->document->setMetaData('author', $this->item->author);
		if ($this->print)                      $this->document->setMetaData('robots', 'noindex, nofollow');
	}


    /**
     * Generates the toolbar for the top of the view
     *
     * @return    string    Toolbar with buttons
     */
    protected function getToolbar()
    {
        $access = PFtasksHelper::getActions($this->item->id);
        $uid    = JFactory::getUser()->get('id');

        $slug = $this->item->id . ':' . $this->item->alias;

        PFToolbar::button(
            'COM_PROJECTFORK_ACTION_EDIT',
            '',
            false,
            array(
                'access' => ($access->get('core.edit') || $access->get('core.edit.own') && $uid == $this->item->created_by),
                'href' => JRoute::_(PFtasksHelperRoute::getTasksRoute() . '&task=taskform.edit&id=' . $slug)
            )
        );


        return PFToolbar::render();
    }
}
