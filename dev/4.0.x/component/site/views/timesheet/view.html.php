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


class ProjectforkViewTimesheet extends JView
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
    protected $tasks;
    protected $access;
    protected $menu;
    protected $total_time_billable;
    protected $total_time_unbillable;
    protected $total_billable;
    protected $total_estimated_time;
    protected $total_estimated_cost;

    /**
     * Display the view
     *
     */
    public function display($tpl = null)
    {
        $app     = JFactory::getApplication();
        $state   = $this->get('State');
        $layout  = $this->getLayout();
        $project = (int) $state->get('filter.project');
        $active  = $app->getMenu()->getActive();

        // Check for layout override
        if (isset($active->query['layout']) && (JRequest::getCmd('layout') == '')) {
            $this->setLayout($active->query['layout']);
        }

        $this->items      = $this->get('Items');
        $this->pagination = $this->get('Pagination');
        $this->state      = $this->get('State');
        $this->authors    = $this->get('Authors');
        $this->tasks      = $this->get('Tasks');
        $this->params     = $this->state->params;
        $this->actions    = $this->getActions();
        $this->toolbar    = $this->getToolbar();
        $this->access     = ProjectforkHelper::getActions(NULL, 0, true);
        $this->nulldate   = JFactory::getDbo()->getNullDate();
        $this->menu       = new ProjectforkHelperContextMenu();

        // Escape strings for HTML output
        $this->pageclass_sfx = htmlspecialchars($this->params->get('pageclass_sfx'));

        // Get project total values
        $this->total_billable        = $this->get('ProjectCost');
        $this->total_time_billable   = $this->get('BillableProjectTime');
        $this->total_time_unbillable = $this->get('UnbillableProjectTime');
        $this->total_estimated_time  = $this->get('ProjectEstimatedTime');
        $this->total_estimated_cost  = $this->get('ProjectEstimatedCost');
        $this->total_time = ($this->total_time_billable + $this->total_time_unbillable);

        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
            JError::raiseError(500, implode("\n", $errors));
            return false;
        }

        // Check for empty search result
        if ((count($this->items) == 0) && $this->state->get('filter.isset')) {
            $app->enqueueMessage(JText::_('COM_PROJECTFORK_EMPTY_SEARCH_RESULT'));
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
            $this->params->def('page_heading', JText::_('COM_PROJECTFORK_TIMESHEET_TITLE'));
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

        if ($access->get('time.create')) {
            $tb->button('COM_PROJECTFORK_ACTION_NEW', 'timeform.add');
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

        if ($access->get('time.edit.state')) {
            $options[] = JHtml::_('select.option', 'timesheet.publish', JText::_('COM_PROJECTFORK_ACTION_PUBLISH'));
            $options[] = JHtml::_('select.option', 'timesheet.unpublish', JText::_('COM_PROJECTFORK_ACTION_UNPUBLISH'));
            $options[] = JHtml::_('select.option', 'timesheet.archive', JText::_('COM_PROJECTFORK_ACTION_ARCHIVE'));
            $options[] = JHtml::_('select.option', 'timesheet.checkin', JText::_('COM_PROJECTFORK_ACTION_CHECKIN'));
        }
        if ($state->get('filter.published') == -2 && $access->get('time.delete')) {
            $options[] = JHtml::_('select.option', 'timesheet.delete', JText::_('COM_PROJECTFORK_ACTION_DELETE'));
        }
        elseif ($access->get('time.edit.state')) {
            $options[] = JHtml::_('select.option', 'timesheet.trash', JText::_('COM_PROJECTFORK_ACTION_TRASH'));
        }

        return $options;
    }
}
