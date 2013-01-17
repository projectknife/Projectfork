<?php
/**
 * @package      Projectfork
 * @subpackage   Dashboard
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


jimport('joomla.application.component.view');


class ProjectforkViewDashboard extends JViewLegacy
{
    protected $params;
    protected $state;
    protected $modules;
    protected $item;
    protected $pageclass_sfx;
    protected $toolbar;


	function display($tpl = null)
	{
	    $this->state   = $this->get('State');
        $this->item    = $this->get('Item');
        $this->params  = $this->state->params;
        $this->modules = JFactory::getDocument()->loadRenderer('modules');
        $this->toolbar = $this->getToolbar();

        $dispatcher	= JDispatcher::getInstance();

        // Escape strings for HTML output
		$this->pageclass_sfx = htmlspecialchars($this->params->get('pageclass_sfx'));

        // Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

        // Process the content plugins.
        if ($this->item) {
            // Fake content item
            PFObjectHelper::toContentItem($this->item);

    		// Import plugins
    		JPluginHelper::importPlugin('content');
            $context = 'com_pfprojects.project';

            // Trigger events
    		$results = $dispatcher->trigger('onContentPrepare', array ($context, &$this->item, &$this->params, 0));

    		$this->item->event = new stdClass();
    		$results = $dispatcher->trigger('onContentAfterTitle', array($context, &$this->item, &$this->params, 0));
    		$this->item->event->afterDisplayTitle = trim(implode("\n", $results));

    		$results = $dispatcher->trigger('onContentBeforeDisplay', array($context, &$this->item, &$this->params, 0));
    		$this->item->event->beforeDisplayContent = trim(implode("\n", $results));

    		$results = $dispatcher->trigger('onContentAfterDisplay', array($context, &$this->item, &$this->params, 0));
    		$this->item->event->afterDisplayContent = trim(implode("\n", $results));
        }

        // Prepare the document
        $this->prepareDocument();

        // Display
		parent::display($tpl);
	}


    /**
	 * Prepares the document
     *
	 */
	protected function prepareDocument()
	{
		$app	 = JFactory::getApplication();
		$menu    = $app->getMenu()->getActive();
		$pathway = $app->getPathway();
		$title	 = null;

		// Because the application sets a default page title,
		// we need to get it from the menu item itself
		if ($menu) {
			$this->params->def('page_heading', $this->params->get('page_title', $menu->title));
		}
		else {
			$this->params->def('page_heading', JText::_('COM_PROJECTFORK_DASHBOARD_TITLE'));
		}


        // Set the page title
		$title = $this->params->get('page_title', '');

		if (empty($title)) {
			$title = $app->getCfg('sitename');
		}
		elseif ($app->getCfg('sitename_pagetitles', 0) == 1) {
			$title = JText::sprintf('JPAGETITLE', $app->getCfg('sitename'), $title);
		}
		elseif ($app->getCfg('sitename_pagetitles', 0) == 2) {
			$title = JText::sprintf('JPAGETITLE', $title, $app->getCfg('sitename'));
		}

		$this->document->setTitle($title);

        // Set crawler behavior info
		if ($this->params->get('robots')) {
			$this->document->setMetadata('robots', $this->params->get('robots'));
		}

        // Set page description
        if($this->params->get('menu-meta_description')) {
            $this->document->setDescription($desc);
        }

        // Set page keywords
        if($this->params->get('menu-meta_keywords')) {
            $this->document->setMetadata('keywords', $keywords);
        }

		// Add feed links
		if ($this->params->get('show_feed_link', 1)) {
			// Add RSS link
            $link    = '&format=feed&limitstart=';
			$attribs = array('type' => 'application/rss+xml', 'title' => 'RSS 2.0');

			$this->document->addHeadLink(JRoute::_($link . '&type=rss'), 'alternate', 'rel', $attribs);

            // Add atom link
			$attribs = array('type' => 'application/atom+xml', 'title' => 'Atom 1.0');

			$this->document->addHeadLink(JRoute::_($link . '&type=atom'), 'alternate', 'rel', $attribs);
		}
	}


    /**
     * Generates the toolbar for the top of the view
     *
     * @return    string    Toolbar with buttons
     */
    protected function getToolbar()
    {
        $id = (isset($this->item->id) ? $this->item->id : null);

        $access = PFprojectsHelper::getActions($id);
        $uid    = JFactory::getUser()->get('id');

        if ($id) {
            $slug = $this->item->id . ':' . $this->item->alias;

            PFToolbar::button(
                'COM_PROJECTFORK_ACTION_EDIT',
                '',
                false,
                array(
                    'access' => ($access->get('core.edit') || $access->get('core.edit.own') && $uid == $this->item->created_by),
                    'href' => JRoute::_(PFprojectsHelperRoute::getProjectsRoute() . '&task=form.edit&id=' . $slug)
                )
            );
        }


        return PFToolbar::render();
    }
}
