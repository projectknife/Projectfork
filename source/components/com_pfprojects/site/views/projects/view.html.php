<?php
/**
 * @package      pkg_projectfork
 * @subpackage   com_pfprojects
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2013 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


jimport('joomla.application.component.view');


/**
 * Project list view class.
 *
 */
class PFprojectsViewProjects extends JViewLegacy
{
    protected $pageclass_sfx;
    protected $items;
    protected $pagination;
    protected $params;
    protected $state;
    protected $access;
    protected $toolbar;
    protected $sort_options;
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

        $this->items      = $this->get('Items');
        $this->pagination = $this->get('Pagination');
        $this->state      = $this->get('State');
        $this->params     = $this->state->get('params');
        $this->access     = PFprojectsHelper::getActions();

        $this->toolbar       = $this->getToolbar();
        $this->sort_options  = $this->getSortOptions();
        $this->order_options = $this->getOrderOptions();

        // Escape strings for HTML output
        $this->pageclass_sfx = htmlspecialchars($this->params->get('pageclass_sfx'));

        // Check for errors
        if (count($errors = $this->get('Errors'))) {
            JError::raiseError(500, implode("\n", $errors));
            return false;
        }

        // Check for empty filter result
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
            $this->params->def('page_heading', JText::_('COM_PROJECTFORK_PROJECTS'));
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
        $access  = PFprojectsHelper::getActions();
        $state   = $this->get('State');
        $options = array();

        PFToolbar::button(
            'COM_PROJECTFORK_ACTION_NEW',
            'form.add',
            false,
            array('access' => $access->get('core.create'))
        );


        if ($access->get('core.edit.state')) {
            $options[] = array('text' => 'COM_PROJECTFORK_ACTION_PUBLISH',   'task' => $this->getName() . '.publish');
            $options[] = array('text' => 'COM_PROJECTFORK_ACTION_UNPUBLISH', 'task' => $this->getName() . '.unpublish');
            $options[] = array('text' => 'COM_PROJECTFORK_ACTION_ARCHIVE',   'task' => $this->getName() . '.archive');
            $options[] = array('text' => 'COM_PROJECTFORK_ACTION_CHECKIN',   'task' => $this->getName() . '.checkin');
        }

        if ($state->get('filter.published') == -2 && $access->get('core.delete')) {
            $options[] = array('text' => 'COM_PROJECTFORK_ACTION_DELETE', 'task' => $this->getName() . '.delete');
        }
        elseif ($access->get('core.edit.state')) {
            $options[] = array('text' => 'COM_PROJECTFORK_ACTION_TRASH', 'task' => $this->getName() . '.trash');
        }

        if (count($options)) {
            PFToolbar::listButton($options);
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
        $options[] = JHtml::_('select.option', 'category_title, a.title', JText::_('COM_PROJECTFORK_ORDER_TITLE'));
        $options[] = JHtml::_('select.option', 'a.end_date', JText::_('COM_PROJECTFORK_ORDER_DEADLINE'));
        $options[] = JHtml::_('select.option', 'author_name', JText::_('COM_PROJECTFORK_ORDER_AUTHOR'));

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
