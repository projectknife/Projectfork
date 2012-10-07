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
 * Task list view class.
 *
 */
class ProjectforkViewTasks extends JViewLegacy
{
    protected $pageclass_sfx;
    protected $items;
    protected $nulldate;
    protected $pagination;
    protected $params;
    protected $state;
    protected $milestones;
    protected $lists;
    protected $assigned;
    protected $actions;
    protected $toolbar;
    protected $authors;
    protected $access;
    protected $menu;


    /**
     * Display the view
     *
     * @return    void
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

        // Set list limit to 0 if default layout and if a project is selected
        if (($project > 0) && ($layout == '' || $layout == 'default')) {
            // $state->set('list.limit', 0);
        }

        $this->items      = $this->get('Items');
        $this->pagination = $this->get('Pagination');
        $this->state      = $this->get('State');
        $this->milestones = $this->get('Milestones');
        $this->lists      = $this->get('TaskLists');
        $this->authors    = $this->get('Authors');
        $this->assigned   = $this->get('AssignedUsers');
        $this->params     = $this->state->params;
        $this->toolbar    = $this->getToolbar();
        $this->access     = ProjectforkHelper::getActions(NULL, 0, true);
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

        // Prepare the document
        $this->prepareDocument();

        // Display the view
        parent::display($tpl);
    }


    /**
     * Prepares the document
     *
     * @return    void
     */
    protected function prepareDocument()
    {
        $app     = JFactory::getApplication();
        $menu    = $app->getMenu()->getActive();
        $pathway = $app->getPathway();
        $title   = null;

        // Because the application sets a default page title, we need to get it from the menu item itself
        if ($menu) {
            $this->params->def('page_heading', $this->params->get('page_title', $menu->title));
        }
        else {
            $this->params->def('page_heading', JText::_('COM_PROJECTFORK_TASKS'));
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
            $link    = '&format=feed&limitstart=';
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
        $access = ProjectforkHelperAccess::getActions(null, 0, true);
        $state  = $this->get('State');

        $create_list = $access->get('tasklist.create');
        $create_task = $access->get('task.create');

        $items = array();
        $items[] = array('text'    => 'COM_PROJECTFORK_ACTION_NEW_TASK',
                         'task'    => 'taskform.add',
                         'options' => array('access' => $create_task));

        $items[] = array('text'    => 'COM_PROJECTFORK_ACTION_NEW_TASKLIST',
                         'task'    => 'tasklistform.add',
                         'options' => array('access' => $create_list));

        ProjectforkHelperToolbar::dropdownButton($items);

        $items = array();
        if ($access->get('milestone.edit.state')) {
            $items[] = array('text' => 'COM_PROJECTFORK_ACTION_PUBLISH',   'task' => $this->getName() . '.publish');
            $items[] = array('text' => 'COM_PROJECTFORK_ACTION_UNPUBLISH', 'task' => $this->getName() . '.unpublish');
            $items[] = array('text' => 'COM_PROJECTFORK_ACTION_ARCHIVE',   'task' => $this->getName() . '.archive');
            $items[] = array('text' => 'COM_PROJECTFORK_ACTION_CHECKIN',   'task' => $this->getName() . '.checkin');
        }

        if ($state->get('filter.published') == -2 && $access->get('milestone.delete')) {
            $items[] = array('text' => 'COM_PROJECTFORK_ACTION_DELETE', 'task' => $this->getName() . '.delete');
        }
        elseif ($access->get('milestone.edit.state')) {
            $items[] = array('text' => 'COM_PROJECTFORK_ACTION_TRASH', 'task' => $this->getName() . '.trash');
        }

        if (count($items)) {
            ProjectforkHelperToolbar::listButton($items);
        }

        ProjectforkHelperToolbar::filterButton($this->state->get('filter.isset'));

        return ProjectforkHelperToolbar::render();
    }
}
