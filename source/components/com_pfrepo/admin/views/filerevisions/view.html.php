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


class PFrepoViewFileRevisions extends JViewLegacy
{
    /**
     * File head revision record
     *
     * @var    object
     */
    protected $item;

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
     * Indicates whether the site is running Joomla 2.5 or not
     *
     * @var    boolean
     */
    protected $is_j25;


    /**
     * Display the view
     *
     * @return    void
     */
    public function display($tpl = null)
    {
        $this->item    = $this->get('Item');
        $this->items   = $this->get('Items');
        $this->state   = $this->get('State');
        $this->authors = $this->get('Authors');
        $this->is_j25  = version_compare(JVERSION, '3', 'lt');

        if ($this->state->get('list.direction') == 'desc') {
            array_unshift($this->items, $this->item);
        }
        else {
            $this->items[] = $this->item;
        }

        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
            JError::raiseError(500, implode("\n", $errors));
            return false;
        }

        if (!$this->item || empty($this->item->id)) {
            JError::raiseError(404, JText::_('COM_PROJECTFORK_ERROR_FILE_NOT_FOUND'));
            return false;
        }

        // Check access
        $user = JFactory::getUser();

        if (!$user->authorise('core.admin', 'com_pfrepo')) {
            $levels = $user->getAuthorisedViewLevels();

            if (!in_array($this->item->access, $levels)) {
                JError::raiseWarning(403, JText::_('JERROR_ALERTNOAUTHOR'));
                return false;
            }
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
        $id     = $this->item->id;
        $author = $this->item->created_by;
        $asset  = 'com_pfrepo.file.' . $id;
        $user   = JFactory::getUser();
        $uid    = $user->get('id');

        $title = JText::_('COM_PROJECTFORK_PAGE_VIEW_FILE_REVISIONS')
               . ': ' . $this->escape($this->item->title);

        $link = 'index.php?option=com_pfrepo&view=repository'
              . '&filter_project=' . (int) $this->item->project_id
              . '&filter_parent_id=' . (int) $this->item->dir_id;

        JToolBarHelper::title($title, 'article-add.png');

        JToolBarHelper::back('JTOOLBAR_BACK', $link);

        if ($user->authorise('core.edit', $asset) || ($user->authorise('core.edit.own', $asset) && $author == $uid)) {
            JToolBarHelper::custom('file.edit', 'edit', 'edit', 'JTOOLBAR_EDIT', false);
        }
    }


    /**
     * Adds the page side bar for Joomla 3.0 and higher
     *
     * @return    void
     */
    protected function addSidebar()
    {
        $link = 'index.php?option=com_pfrepo&view=filerevisions'
              . '&filter_project=' . (int) $this->item->project_id
              . '&filter_parent_id=' . (int) $this->item->dir_id
              . '&id=' . (int) $this->item->id;

        JHtmlSidebar::setAction($link);

        JHtmlSidebar::addFilter(
            JText::_('JOPTION_SELECT_AUTHOR'),
            'filter_author_id',
            JHtml::_('select.options', $this->authors, 'value', 'text', $this->state->get('filter.author_id'))
        );
    }
}
