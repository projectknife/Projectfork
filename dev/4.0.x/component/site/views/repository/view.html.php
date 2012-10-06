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
 * Repository view class.
 *
 */
class ProjectforkViewRepository extends JViewLegacy
{
    protected $pageclass_sfx;
    protected $items;
    protected $nulldate;
    protected $params;
    protected $state;
    protected $toolbar;
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
        $this->state      = $this->get('State');
        $this->params     = $this->state->params;
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
            $this->params->def('page_heading', JText::_('COM_PROJECTFORK_REPO_TITLE'));
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
        $dir    = $this->items['directory'];
        $access = ProjectforkHelperAccess::getActions('directory', $dir->id);

        if ($dir->id > 1) {
            $create_dir  = $access->get('directory.create');
            $create_note = $access->get('note.create');
            $create_file = $access->get('file.create');

            $items = array();
            $items[] = array('text'    => 'COM_PROJECTFORK_ACTION_NEW_FILE',
                             'task'    => 'fileform.add',
                             'options' => array('access' => $create_file));

            $items[] = array('text'    => 'COM_PROJECTFORK_ACTION_NEW_DIRECTORY',
                             'task'    => 'directoryform.add',
                             'options' => array('access' => $create_dir));

            $items[] = array('text'    => 'COM_PROJECTFORK_ACTION_NEW_NOTE',
                             'task'    => 'noteform.add',
                             'options' => array('access' => $create_note));

            ProjectforkHelperToolbar::dropdownButton($items);

            $items = array();
            $items[] = array(
                'text' => 'COM_PROJECTFORK_ACTION_DELETE',
                'task' => $this->getName() . '.delete',
                'options' => array('access' => ($access->get('directory.delete') || $access->get('file.delete') || $access->get('note.delete'))));

            if (count($items)) {
                ProjectforkHelperToolbar::listButton($items);
            }
        }

        ProjectforkHelperToolbar::filterButton();

        return ProjectforkHelperToolbar::render();
    }
}
