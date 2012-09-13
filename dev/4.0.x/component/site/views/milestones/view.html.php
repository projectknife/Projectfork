<?php
/**
 * @package      Projectfork
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


jimport('joomla.application.component.view');


/**
 * Milestone list view class.
 *
 */
class ProjectforkViewMilestones extends JViewLegacy
{
    protected $pageclass_sfx;
    protected $items;
    protected $nulldate;
    protected $pagination;
    protected $params;
    protected $state;
    protected $actions;
    protected $toolbar;
    protected $authors;
    protected $access;
    protected $menu;


    /**
     * Display the view
     *
     * @return void
     */
    public function display($tpl = null)
    {
        $app    = JFactory::getApplication();
        $active = $app->getMenu()->getActive();

        $this->items      = $this->get('Items');
        $this->pagination = $this->get('Pagination');
        $this->state      = $this->get('State');
        $this->authors    = $this->get('Authors');
        $this->params     = $this->state->params;
        $this->actions    = $this->getActions();
        $this->toolbar    = $this->getToolbar();
        $this->access     = ProjectforkHelperAccess::getActions(null, 0, true);
        $this->nulldate   = JFactory::getDbo()->getNullDate();
        $this->menu       = new ProjectforkHelperContextMenu();

        // Escape strings for HTML output
        $this->pageclass_sfx = htmlspecialchars($this->params->get('pageclass_sfx'));

        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
            JError::raiseError(500, implode("\n", $errors));
            return false;
        }

        // Check for empty search result
        if ((count($this->items) == 0) && $this->state->get('filter.isset')) {
            $app->enqueueMessage(JText::_('COM_PROJECTFORK_EMPTY_SEARCH_RESULT'));
        }

        // Check for layout override
        if (isset($active->query['layout']) && (JRequest::getCmd('layout') == '')) {
            $this->setLayout($active->query['layout']);
        }

        // Prepare the document
        $this->prepareDocument();

        // Display the view
        parent::display($tpl);
    }


    /**
     * Prepares the document
     *
     */
    protected function prepareDocument()
    {
        $app     = JFactory::getApplication();
        $menus   = $app->getMenu();
        $pathway = $app->getPathway();
        $title   = null;

        // Because the application sets a default page title,
        // we need to get it from the menu item itself
        $menu = $menus->getActive();

        if ($menu) {
            $this->params->def('page_heading', $this->params->get('page_title', $menu->title));
        }
        else {
            $this->params->def('page_heading', JText::_('COM_PROJECTFORK_MILESTONES'));
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
        if ($this->params->get('menu-meta_description')) {
            $this->document->setDescription($desc);
        }


        // Set page keywords
        if ($this->params->get('menu-meta_keywords')) {
            $this->document->setMetadata('keywords', $keywords);
        }


        // Add feed links
        if ($this->params->get('show_feed_link', 1)) {
            $link = '&format=feed&limitstart=';
            $attribs = array('type' => 'application/rss+xml', 'title' => 'RSS 2.0');
            $this->document->addHeadLink(JRoute::_($link . '&type=rss'), 'alternate', 'rel', $attribs);
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
        $access = ProjectforkHelperAccess::getActions(NULL, 0, true);
        $tb     = new ProjectforkHelperToolbar();

        if ($access->get('milestone.create')) {
            $tb->button('COM_PROJECTFORK_ACTION_NEW', 'milestoneform.add');
        }

        return $tb->__toString();
    }


    /**
     * Generates select options for the bulk action menu
     *
     * @return    array    The available options
     */
    protected function getActions()
    {
        $access  = ProjectforkHelperAccess::getActions(NULL, 0, true);
        $state   = $this->get('State');
        $options = array();

        if ($access->get('milestone.edit.state')) {
            $options[] = JHtml::_('select.option', 'milestones.publish', JText::_('COM_PROJECTFORK_ACTION_PUBLISH'));
            $options[] = JHtml::_('select.option', 'milestones.unpublish', JText::_('COM_PROJECTFORK_ACTION_UNPUBLISH'));
            $options[] = JHtml::_('select.option', 'milestones.archive', JText::_('COM_PROJECTFORK_ACTION_ARCHIVE'));
            $options[] = JHtml::_('select.option', 'milestones.checkin', JText::_('COM_PROJECTFORK_ACTION_CHECKIN'));
        }
        if ($state->get('filter.published') == -2 && $access->get('milestone.delete')) {
            $options[] = JHtml::_('select.option', 'milestones.delete', JText::_('COM_PROJECTFORK_ACTION_DELETE'));
        }
        elseif ($access->get('milestone.edit.state')) {
            $options[] = JHtml::_('select.option', 'milestones.trash', JText::_('COM_PROJECTFORK_ACTION_TRASH'));
        }

        return $options;
    }
}
