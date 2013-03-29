<?php
/**
 * @package      pkg_projectfork
 * @subpackage   com_pfrepo
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2013 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


jimport('joomla.application.component.view');


/**
 * Repository view class.
 *
 */
class PFrepoViewRepository extends JViewLegacy
{
    /**
     * CSS page class suffix
     *
     * @var    string    
     */
    protected $pageclass_sfx;

    /**
     * List of items to display
     *
     * @var    array    
     */
    protected $items;

    /**
     * Sql "null" date (0000-00-00 00:00:00)
     *
     * @var    string    
     */
    protected $nulldate;

    /**
     * Model parameters
     *
     * @var    object    
     */
    protected $params;

    /**
     * Model state object
     *
     * @var    object    
     */
    protected $state;

    /**
     * Toolbar html code
     *
     * @var    string    
     */
    protected $toolbar;

    /**
     * Object holding user permissions
     *
     * @var    object    
     */
    protected $access;

    /**
     * Context menu instance
     *
     * @var    object    
     */
    protected $menu;

    /**
     * JPagination instance object
     *
     * @var    object    
     */
    protected $pagination;

    /**
     * Select list sorting options
     *
     * @var    array    
     */
    protected $sort_options;

    /**
     * Select list ordering options
     *
     * @var    array    
     */
    protected $order_options;


    /**
     * Display the view
     *
     * @return    void    
     */
    public function display($tpl = null)
    {
        $app    = JFactory::getApplication();
        $active = $app->getMenu()->getActive();

        $this->items    = $this->get('Items');
        $this->state    = $this->get('State');
        $this->params   = $this->state->params;
        $this->access   = PFrepoHelper::getActions();
        $this->nulldate = JFactory::getDbo()->getNullDate();
        $this->menu     = new PFMenuContext();

        $this->toolbar       = $this->getToolbar();
        $this->sort_options  = $this->getSortOptions();
        $this->order_options = $this->getOrderOptions();

        // Escape strings for HTML output
        $this->pageclass_sfx = htmlspecialchars($this->params->get('pageclass_sfx'));

        // Set the pagination object
        if ($this->items['directory']->id == '1') {
            $this->pagination = $this->get('Pagination');
        }
        else {
            $this->pagination = null;
        }

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
        $access = PFrepoHelper::getActions('directory', $dir->id);

        if ($dir->id > 1) {
            $items = array();
            $items[] = array('text'    => 'COM_PROJECTFORK_ACTION_NEW_FILE',
                             'task'    => 'fileform.add',
                             'options' => array('access' => $access->get('core.create')));

            $items[] = array('text'    => 'COM_PROJECTFORK_ACTION_NEW_DIRECTORY',
                             'task'    => 'directoryform.add',
                             'options' => array('access' => $access->get('core.create')));

            $items[] = array('text'    => 'COM_PROJECTFORK_ACTION_NEW_NOTE',
                             'task'    => 'noteform.add',
                             'options' => array('access' => $access->get('core.create')));

            PFToolbar::dropdownButton($items);

            $items = array();
            $items[] = array(
                'text' => 'COM_PROJECTFORK_ACTION_DELETE',
                'task' => $this->getName() . '.delete',
                'options' => array('access' => $access->get('core.delete')));

            if (count($items)) {
                PFToolbar::listButton($items);
            }
        }

        PFToolbar::filterButton($this->state->get('filter.isset'));

        return PFToolbar::render();
    }


    /**
     * Generates the table sort options
     *
     * @return    array    HTML list options
     */
    protected function getSortOptions()
    {
        $options = array();

        $options[] = JHtml::_('select.option', '', JText::_('COM_PROJECTFORK_ORDER_SELECT'));
        $options[] = JHtml::_('select.option', 'a.title', JText::_('COM_PROJECTFORK_ORDER_TITLE'));
        $options[] = JHtml::_('select.option', 'a.created', JText::_('COM_PROJECTFORK_ORDER_CREATE_DATE'));
        $options[] = JHtml::_('select.option', 'a.modified', JText::_('COM_PROJECTFORK_ORDER_EDIT_DATE'));
        $options[] = JHtml::_('select.option', 'a.created_by', JText::_('COM_PROJECTFORK_ORDER_AUTHOR'));

        return $options;
    }


    /**
     * Generates the table order options
     *
     * @return    array    HTML list options
     */
    protected function getOrderOptions()
    {
        $options = array();

        $options[] = JHtml::_('select.option', '', JText::_('COM_PROJECTFORK_ORDER_SELECT_DIR'));
        $options[] = JHtml::_('select.option', 'ASC', JText::_('COM_PROJECTFORK_ORDER_ASC'));
        $options[] = JHtml::_('select.option', 'DESC', JText::_('COM_PROJECTFORK_ORDER_DESC'));

        return $options;
    }
}
