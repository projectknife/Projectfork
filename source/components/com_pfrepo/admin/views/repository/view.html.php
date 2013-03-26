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
 * Repository View Class
 *
 */
class PFrepoViewRepository extends JViewLegacy
{
    /**
     * List of items to display
     *
     * @var    array
     */
    protected $items;

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
     * Sql "null" date (0000-00-00 00:00:00)
     *
     * @var    string
     */
    protected $nulldate;

    /**
     * JPagination instance object
     *
     * @var    object
     */
    protected $pagination;

    /**
     * Indicates whether the site is running Joomla 2.5 or not
     *
     * @var    boolean
     */
    protected $is_j25;


    /**
     * Displays the view.
     *
     */
    public function display($tpl = null)
    {
        // Get data from model
        $this->state    = $this->get('State');
        $this->items    = $this->get('Items');
        $this->authors  = $this->get('Authors');
        $this->nulldate = JFactory::getDbo()->getNullDate();
        $this->is_j25   = version_compare(JVERSION, '3', 'lt');

        // Check for errors
        if (count($errors = $this->get('Errors'))) {
            JError::raiseError(500, implode("\n", $errors));
            return false;
        }

        // Set the pagination object
        if ($this->items['directory']->id == '1') {
            $this->pagination = $this->get('Pagination');
        }
        else {
            $this->pagination = null;
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
     * Adds the page title and toolbar.
     *
     */
    protected function addToolbar()
    {
        $user  = JFactory::getUser();
        $state = $this->get('State');

        JToolBarHelper::title(JText::_('COM_PROJECTFORK_REPO_TITLE'), 'article.png');

        if ($state->get('filter.project') && $this->items['directory']->id > 1) {
            $access = PFrepoHelper::getActions('directory', $this->items['directory']->id);

            if ($access->get('core.create')) {
                JToolBarHelper::custom('directory.add', 'new.png', 'new_f2.png', 'JTOOLBAR_ADD_DIRECTORY', false);
                JToolBarHelper::custom('file.add', 'upload.png', 'upload_f2.png', 'JTOOLBAR_ADD_FILE', false);
                JToolBarHelper::custom('note.add', 'copy.png', 'html_f2.png', 'JTOOLBAR_ADD_NOTE', false);
            }

            if ($access->get('core.delete')) {
                JToolBarHelper::divider();
                JToolBarHelper::deleteList('', 'repository.delete','JTOOLBAR_DELETE');
            }
        }

        if ($user->authorise('core.admin')) {
            JToolBarHelper::preferences('com_pfrepo');
        }
    }


    /**
     * Adds the page side bar for Joomla 3.0 and higher
     *
     * @return    void
     */
    protected function addSidebar()
    {
        JHtmlSidebar::setAction('index.php?option=com_pfrepo&view=repository');

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
            'a.title'      => JText::_('JGLOBAL_TITLE'),
            'access_level' => JText::_('JGRID_HEADING_ACCESS'),
            'author_name'  => JText::_('JAUTHOR'),
            'a.created'    => JText::_('JDATE'),
            'a.id'         => JText::_('JGRID_HEADING_ID')
        );
    }
}
