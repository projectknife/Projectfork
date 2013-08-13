<?php
/**
 * @package      pkg_projectfork
 * @subpackage   com_pfmilestones
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2013 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


jimport('joomla.application.component.view');


/**
 * HTML Milestone View class for the Projectfork component
 *
 */
class PFmilestonesViewMilestone extends JViewLegacy
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
        $dispatcher	= JDispatcher::getInstance();
		$user		= JFactory::getUser();

		$uid   = $user->get('id');
		$item  = $this->get('Item');
		$state = $this->get('State');
        $print = JRequest::getBool('print');

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseWarning(500, implode("\n", $errors));
			return false;
		}

        // Set active project
        if (!PFApplicationHelper::setActiveProject($item->project_id)) {
            return false;
        }

		// Merge milestone params. If this is single-milestone view, menu params override milestone params
		// Otherwise, milestone params override menu item params
		$params = $state->get('params');
		$active	= $app->getMenu()->getActive();
		$temp	= clone ($params);

		// Check to see which parameters should take priority
		if ($active) {
			$current_link = $active->link;

			if (strpos($current_link, 'view=milestone') && (strpos($current_link, '&id='.(string) $item->id))) {
				$item->params->merge($temp);

				// Load layout from active query (in case it is an alternative menu item)
				if (isset($active->query['layout'])) $this->setLayout($active->query['layout']);
			}
			else {
				// Merge the menu item params with the milestone params so that the milestone params take priority
				$temp->merge($item->params);
				$item->params = $temp;

				// Check for alternative layouts (since we are not in a single-milestone menu item)
				if ($layout = $item->params->get('milestone_layout')) $this->setLayout($layout);
			}
		}
		else {
			// Merge so that milestone params take priority
			$temp->merge($item->params);
			$item->params = $temp;

			// Check for alternative layouts (since we are not in a single-milestone menu item)
			if ($layout = $item->params->get('milestone_layout')) $this->setLayout($layout);
		}

		$offset = $state->get('list.offset');

		// Check the view access to the milestone (the model has already computed the values).
		if ($item->params->get('access-view') != true && (($item->params->get('show_noauth') != true &&  $user->get('guest') ))) {
		    JError::raiseWarning(403, JText::_('JERROR_ALERTNOAUTHOR'));
			return false;
		}


        // Fake some content item properties to avoid plugin issues
        PFObjectHelper::toContentItem($item);

		// Process the content plugins.
		JPluginHelper::importPlugin('content');
		$results = $dispatcher->trigger('onContentPrepare', array ('com_pfmilestones.milestone', &$item, &$params, $offset));

		$item->event = new stdClass();
		$results = $dispatcher->trigger('onContentAfterTitle', array('com_pfmilestones.milestone', &$item, &$params, $offset));
		$item->event->afterDisplayTitle = trim(implode("\n", $results));

		$results = $dispatcher->trigger('onContentBeforeDisplay', array('com_pfmilestones.milestone', &$item, &$params, $offset));
		$item->event->beforeDisplayContent = trim(implode("\n", $results));

		$results = $dispatcher->trigger('onContentAfterDisplay', array('com_pfmilestones.milestone', &$item, &$params, $offset));
		$item->event->afterDisplayContent = trim(implode("\n", $results));

		// Escape strings for HTML output
		$this->pageclass_sfx = htmlspecialchars($item->params->get('pageclass_sfx'));

        // Assign references
        $this->assignRef('params', $params);
        $this->assignRef('state',  $state);
        $this->assignRef('user',   $user);
        $this->assignRef('item',   $item);
        $this->assignRef('print',  $print);

        $this->toolbar = $this->getToolbar();

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
			$this->params->def('page_heading', JText::_('COM_PROJECTFORK_MILESTONE'));
		}

		$title = $this->params->get('page_title', '');
		$id    = (int) @$menu->query['id'];

		// If the menu item does not concern this item
		if($menu && ($menu->query['option'] != 'com_pfmilestones' || $menu->query['view'] != 'milestone' || $id != $this->item->id))
        {
			// If this is not a single milestone menu item, set the page title to the milestone title
			if ($this->item->title) $title = $this->item->title;

            $pid    = $this->item->project_id;
            $palias = $this->item->project_alias;

			$path   = array(array('title' => $this->item->title,
                                  'link'  => '')
                                 );
            $path[] = array('title' => $this->item->project_title,
                            'link'  => JRoute::_("index.php?option=com_projectfork&view=dashboard&id=$pid:$palias")
                           );

			$path = array_reverse($path);

			foreach ($path as $item)
			{
				$pathway->addItem($item['title'], $item['link']);
			}
		}

		// Check for empty title and add site name if param is set
		if (empty($title))
        {
			$title = $app->getCfg('sitename');
		}
		elseif ($app->getCfg('sitename_pagetitles', 0) == 1)
        {
			$title = JText::sprintf('JPAGETITLE', $app->getCfg('sitename'), $title);
		}
		elseif ($app->getCfg('sitename_pagetitles', 0) == 2)
        {
			$title = JText::sprintf('JPAGETITLE', $title, $app->getCfg('sitename'));
		}

		if (empty($title))
        {
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
        $access = PFmilestonesHelper::getActions($this->item->id);
        $uid    = JFactory::getUser()->get('id');

        if ($this->item->id) {
            $slug = $this->item->id . ':' . $this->item->alias;

            PFToolbar::button(
                'COM_PROJECTFORK_ACTION_EDIT',
                '',
                false,
                array(
                    'access' => ($access->get('core.edit') || $access->get('core.edit.own') && $uid == $this->item->created_by),
                    'href' => JRoute::_(PFmilestonesHelperRoute::getMilestonesRoute() . '&task=form.edit&id=' . $slug)
                )
            );
        }

        if (PFApplicationHelper::enabled('com_pftasks')) {
            PFToolbar::button(
                JText::sprintf('JGRID_HEADING_TASKLISTS_AND_TASKS', intval($this->item->lists), intval($this->item->tasks)),
                '',
                false,
                array(
                    'href' => JRoute::_(PFtasksHelperRoute::getTasksRoute($this->item->project_id, $this->item->id)),
                    'icon' => 'icon-chevron-right',
                    'class'=> ''
                )
            );
            ;
        }


        return PFToolbar::render();
    }
}
