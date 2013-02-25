<?php
/**
 * @package      Projectfork
 * @subpackage   Tasks
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2013 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


jimport('joomla.application.component.view');


/**
 * Projectfork Task list List View Class
 *
 */
class PFtasksViewTasklists extends JViewLegacy
{
    /**
     * List of items to display
     *
     * @var    array
     */
    protected $items;

    /**
     * JPagination instance object
     *
     * @var    object
     */
    protected $pagination;

    /**
     * Model state object
     *
     * @var    object
     */
    protected $state;

    /**
     * List of item authors
     *
     * @var    array
     */
    protected $authors;

    /**
     * List of milestones
     *
     * @var    array
     */
    protected $milestones;

    /**
     * Indicates whether the site is running Joomla 2.5 or not
     *
     * @var    boolean
     */
    protected $is_j25;


    /**
     * Display the view.
     *
     */
    public function display($tpl = null)
    {
        $this->items      = $this->get('Items');
        $this->pagination = $this->get('Pagination');
        $this->state      = $this->get('State');
        $this->authors    = $this->get('Authors');
        $this->milestones = $this->get('Milestones');
        $this->is_j25     = version_compare(JVERSION, '3', 'lt');

        // Check for errors.
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
     */
    protected function addToolbar()
    {
        $user = JFactory::getUser();

        JToolBarHelper::title(JText::_('COM_PROJECTFORK_TASKLISTS_TITLE'), 'article.png');

        if ($user->authorise('core.create', 'com_pftasks')) {
            JToolBarHelper::addNew('tasklist.add');
        }

        if ($user->authorise('core.edit', 'com_pftasks') || $user->authorise('core.edit.own', 'com_pftasks')) {
            JToolBarHelper::editList('tasklist.edit');
        }

        if ($user->authorise('core.edit.state', 'com_pftasks')) {
            JToolBarHelper::divider();
            JToolBarHelper::publish('tasklists.publish', 'JTOOLBAR_PUBLISH', true);
            JToolBarHelper::unpublish('tasklists.unpublish', 'JTOOLBAR_UNPUBLISH', true);
            JToolBarHelper::divider();
            JToolBarHelper::archiveList('tasklists.archive');
            JToolBarHelper::checkin('tasklists.checkin');
        }

        if ($this->state->get('filter.published') == -2 && $user->authorise('core.delete', 'com_pftasks')) {
            JToolBarHelper::deleteList('', 'tasklists.delete','JTOOLBAR_EMPTY_TRASH');
            JToolBarHelper::divider();
        }
        elseif ($user->authorise('core.edit.state', 'com_pftasks')) {
            JToolBarHelper::trash('tasklists.trash');
            JToolBarHelper::divider();
        }
    }


    /**
     * Adds the page side bar for Joomla 3.0 and higher
     *
     * @return    void
     */
    protected function addSidebar()
    {
        JHtmlSidebar::setAction('index.php?option=com_pftasks&view=tasklists');

        JHtmlSidebar::addFilter(
            JText::_('JOPTION_SELECT_PUBLISHED'),
            'filter_published',
            JHtml::_('select.options', JHtml::_('jgrid.publishedOptions'), 'value', 'text', $this->state->get('filter.published'), true)
        );

        JHtmlSidebar::addFilter(
            JText::_('JOPTION_SELECT_ACCESS'),
            'filter_access',
            JHtml::_('select.options', JHtml::_('access.assetgroups'), 'value', 'text', $this->state->get('filter.access'))
        );

        if ($this->state->get('filter.project')) {
            JHtmlSidebar::addFilter(
                JText::_('JOPTION_SELECT_AUTHOR'),
                'filter_author_id',
                JHtml::_('select.options', $this->authors, 'value', 'text', $this->state->get('filter.author_id'))
            );

            JHtmlSidebar::addFilter(
                JText::_('JOPTION_SELECT_MILESTONE'),
                'filter_milestone',
                JHtml::_('select.options', $this->milestones, 'value', 'text', $this->state->get('filter.milestone'))
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
            'a.state'         => JText::_('JSTATUS'),
            'a.title'         => JText::_('JGLOBAL_TITLE'),
            'project_title'   => JText::_('JGRID_HEADING_PROJECT'),
            'milestone_title' => JText::_('JGRID_HEADING_MILESTONE'),
            'task_count'      => JText::_('COM_PROJECTFORK_TASKS_TITLE'),
            'access_level'    => JText::_('JGRID_HEADING_ACCESS'),
            'author_name'     => JText::_('JAUTHOR'),
            'a.created'       => JText::_('JDATE'),
            'a.id'            => JText::_('JGRID_HEADING_ID')
        );
    }
}
