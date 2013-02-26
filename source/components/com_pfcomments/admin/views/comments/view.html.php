<?php
/**
 * @package      Projectfork
 * @subpackage   Comments
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2013 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


jimport('joomla.application.component.view');


class PFcommentsViewComments extends JViewLegacy
{
    /**
     * A list of topics
     *
     * @var    array
     */
    protected $items;

    /**
     * JPagination instance
     *
     * @var    object
     */
    protected $pagination;

    /**
     * State object
     *
     * @var    object
     */
    protected $state;

    /**
     * A list of authors
     *
     * @var    array
     */
    protected $authors;

    /**
     * A list of context options
     *
     * @var    array
     */
    protected $contexts;

    /**
     * A list of context item options
     *
     * @var    array
     */
    protected $cntxt_items;

    /**
     * Sql "null" date (0000-00-00 00:00:00)
     *
     * @var    string
     */
    protected $nulldate;

    /**
     * Indicates whether the site is running Joomla 2.5 or not
     *
     * @var    boolean
     */
    protected $is_j25;


    /**
     * Display the view
     *
     * @param    string    $tpl    A template suffix
     * @retun    void
     */
    public function display($tpl = null)
    {
        // Get data from model
        $this->items       = $this->get('Items');
        $this->pagination  = $this->get('Pagination');
        $this->state       = $this->get('State');
        $this->authors     = $this->get('Authors');
        $this->contexts    = $this->get('Contexts');
        $this->cntxt_items = $this->get('ContextItems');
        $this->nulldate    = JFactory::getDbo()->getNullDate();
        $this->is_j25      = version_compare(JVERSION, '3', 'lt');

        // Check for errors
        if (count($errors = $this->get('Errors'))) {
            JError::raiseError(500, implode("\n", $errors));
            return false;
        }

        if ($this->getLayout() !== 'modal') {
            $this->addToolbar();

            // Add the sidebar (Joomla 3 and up)
            if (!$this->is_j25) {
                $this->addSidebar();
                $this->sidebar = JHtmlSidebar::render();
            }
        }

        parent::display($tpl);
    }


    /**
     * Add the page title and toolbar.
     *
     * @return    void
     */
    protected function addToolbar()
    {
        $user = JFactory::getUser();

        JToolBarHelper::title(JText::_('COM_PROJECTFORK_COMMENTS_TITLE'), 'article.png');

        if ($user->authorise('core.create', 'com_pfcomments') && is_numeric($this->state->get('filter.item_id')) && $this->state->get('filter.context')) {
            JToolBarHelper::addNew('comment.add');
        }

        if ($user->authorise('core.edit', 'com_pfcomments') || $user->authorise('core.edit.own', 'com_pfcomments')) {
            JToolBarHelper::editList('comment.edit');
        }

        if ($user->authorise('core.edit.state', 'com_pfcomments')) {
            JToolBarHelper::divider();
            JToolBarHelper::publish('comments.publish', 'JTOOLBAR_PUBLISH', true);
            JToolBarHelper::unpublish('comments.unpublish', 'JTOOLBAR_UNPUBLISH', true);
            JToolBarHelper::divider();
            JToolBarHelper::archiveList('comments.archive');
            JToolBarHelper::checkin('comments.checkin');
        }

        if ($this->state->get('filter.published') == -2 && $user->authorise('core.delete', 'com_pfcomments')) {
            JToolBarHelper::deleteList('', 'comments.delete','JTOOLBAR_EMPTY_TRASH');
            JToolBarHelper::divider();
        }
        elseif ($user->authorise('core.edit.state', 'com_pfcomments')) {
            JToolBarHelper::trash('comments.trash');
            JToolBarHelper::divider();
        }

        if ($user->authorise('core.admin')) {
            JToolBarHelper::preferences('com_pfcomments');
        }
    }


    /**
     * Adds the page side bar for Joomla 3.0 and higher
     *
     * @return    void
     */
    protected function addSidebar()
    {
        JHtmlSidebar::setAction('index.php?option=com_pfcomments&view=comments');

        JHtmlSidebar::addFilter(
            JText::_('JOPTION_SELECT_PUBLISHED'),
            'filter_published',
            JHtml::_('select.options', JHtml::_('jgrid.publishedOptions'), 'value', 'text', $this->state->get('filter.published'), true)
        );

        JHtmlSidebar::addFilter(
            JText::_('JOPTION_SELECT_CONTEXT'),
            'filter_context',
            JHtml::_('select.options', $this->contexts, 'value', 'text', $this->state->get('filter.context'), true)
        );

        if ((int) $this->state->get('filter.project') > 0) {
            if ($this->state->get('filter.context') != '') {
                JHtmlSidebar::addFilter(
                    JText::_('JOPTION_SELECT_CONTEXT_ITEM'),
                    'filter_item_id',
                    JHtml::_('select.options', $this->cntxt_items, 'value', 'text', $this->state->get('filter.item_id'), true)
                );
            }

            JHtmlSidebar::addFilter(
                JText::_('JOPTION_SELECT_AUTHOR'),
                'filter_author_id',
                JHtml::_('select.options', $this->authors, 'value', 'text', $this->state->get('filter.author_id'))
            );
        }
    }


    /**
     * Returns an array of fields the table can be sorted by.
     * Requires Joomla 3.0 or higher
     *
     * @return    array    Array containing the field name to sort by as the key and display text as value
     */
    protected function getSortFields()
    {
        return array(
            'a.state'       => JText::_('JSTATUS'),
            'a.title'       => JText::_('JGLOBAL_TITLE'),
            'a.context'     => JText::_('JGRID_HEADING_CONTEXT'),
            'author_name'   => JText::_('JAUTHOR'),
            'a.created'     => JText::_('JDATE'),
            'a.id'          => JText::_('JGRID_HEADING_ID')
        );
    }
}
