<?php
/**
 * @package      Projectfork
 * @subpackage   Timetracking
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2013 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


jimport('joomla.application.component.view');


/**
 * Projectfork Time Tracking List View Class
 *
 */
class PFtimeViewTimesheet extends JViewLegacy
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
     * A list of tasks
     *
     * @var    array
     */
    protected $tasks;

    /**
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
     *
     * @retun    void
     */
    public function display($tpl = null)
    {
        // Get data from model
        $this->items      = $this->get('Items');
        $this->pagination = $this->get('Pagination');
        $this->state      = $this->get('State');
        $this->authors    = $this->get('Authors');
        $this->tasks      = $this->get('Tasks');
        $this->nulldate   = JFactory::getDbo()->getNullDate();
        $this->is_j25     = version_compare(JVERSION, '3', 'lt');

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

        JToolBarHelper::title(JText::_('COM_PROJECTFORK_TIMESHEET_TITLE'), 'article.png');

        if ($user->authorise('core.create', 'com_pftime')) {
            JToolBarHelper::addNew('time.add');
        }

        if ($user->authorise('core.edit', 'com_pftime') || $user->authorise('core.edit.own', 'com_pftime')) {
            JToolBarHelper::editList('time.edit');
        }

        if ($user->authorise('core.edit.state', 'com_pftime')) {
            JToolBarHelper::divider();
            JToolBarHelper::publish('timesheet.publish', 'JTOOLBAR_PUBLISH', true);
            JToolBarHelper::unpublish('timesheet.unpublish', 'JTOOLBAR_UNPUBLISH', true);
            JToolBarHelper::divider();
            JToolBarHelper::archiveList('timesheet.archive');
            JToolBarHelper::checkin('timesheet.checkin');
        }

        if ($this->state->get('filter.published') == -2 && $user->authorise('core.delete', 'com_pftime')) {
            JToolBarHelper::deleteList('', 'timesheet.delete','JTOOLBAR_EMPTY_TRASH');
            JToolBarHelper::divider();
        }
        elseif ($user->authorise('core.edit.state', 'com_pftime')) {
            JToolBarHelper::trash('timesheet.trash');
            JToolBarHelper::divider();
        }

        if ($user->authorise('core.admin')) {
            JToolBarHelper::preferences('com_pftime');
        }
    }


    /**
     * Adds the page side bar for Joomla 3.0 and higher
     *
     * @return    void
     */
    protected function addSidebar()
    {
        JHtmlSidebar::setAction('index.php?option=com_pftime&view=timesheet');

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
                JText::_('COM_PROJECTFORK_OPTION_SELECT_TASK'),
                'filter_task',
                JHtml::_('select.options', $this->tasks, 'value', 'text', $this->state->get('filter.task'))
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
            'a.task_title'  => JText::_('COM_PROJECTFORK_TASK_TITLE'),
            'project_title' => JText::_('JGRID_HEADING_PROJECT'),
            'a.log_date'    => JText::_('JDATE'),
            'a.log_time'    => JText::_('COM_PROJECTFORK_TIME_SPENT_HEADING'),
            'access_level'  => JText::_('JGRID_HEADING_ACCESS'),
            'author_name'   => JText::_('JAUTHOR'),
            'a.id'          => JText::_('JGRID_HEADING_ID')
        );
    }
}
