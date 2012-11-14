<?php
/**
 * @package      Projectfork
 * @subpackage   Forum
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


jimport('joomla.application.component.view');


/**
 * Reply list view class.
 *
 */
class PFforumViewReplies extends JViewLegacy
{
    protected $pageclass_sfx;
    protected $items;
    protected $topic;
    protected $pagination;
    protected $params;
    protected $state;
    protected $toolbar;
    protected $authors;
    protected $access;
    protected $menu;
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
        $user   = JFactory::getUser();
        $active = $app->getMenu()->getActive();

        // Check if the provided topic exists and if we have access
        $this->state = $this->get('State');
        $this->topic = $this->get('Topic');

        if (intval($this->state->get('filter.topic') == 0)) {
            JError::raiseWarning(403, JText::_('JERROR_ALERTNOAUTHOR'));
            return;
        }
        else {
            if ($this->topic === false) {
                JError::raiseError(500, $topic->getError());
                return;
            }

            if (!$user->authorise('core.admin', 'com_pfforum')) {
                if (!in_array($this->topic->access, $user->getAuthorisedViewLevels())) {
                    JError::raiseWarning(403, JText::_('JERROR_ALERTNOAUTHOR'));
                    return;
                }
            }
        }

        $this->items      = $this->get('Items');
        $this->pagination = $this->get('Pagination');
        $this->authors    = $this->get('Authors');
        $this->params     = $this->state->params;
        $this->access     = PFforumHelper::getActions($this->topic->id);
        $this->menu       = new PFMenuContext();

        $this->toolbar       = $this->getToolbar();
        $this->sort_options  = $this->getSortOptions();
        $this->order_options = $this->getOrderOptions();

        // Escape strings for HTML output
        $this->pageclass_sfx = htmlspecialchars($this->params->get('pageclass_sfx'));

        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
            JError::raiseError(500, implode("\n", $errors));
            return;
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
     * @return    void
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
            $this->params->def('page_heading', JText::_('COM_PROJECTFORK_REPLIES'));
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
        $access = PFforumHelper::getActions($this->topic->id);
        $state  = $this->get('State');

        $opts = array('access' => $access->get('core.create'));

        PFToolbar::button(
            'COM_PROJECTFORK_ACTION_NEW',
            'replyform.add',
            false,
            $opts
        );

        $options = array();
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
        $options[] = JHtml::_('select.option', 'a.created', JText::_('COM_PROJECTFORK_ORDER_CREATE_DATE'));
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
