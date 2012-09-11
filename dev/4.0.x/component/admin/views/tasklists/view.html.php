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


class ProjectforkViewTasklists extends JViewLegacy
{
    protected $items;
    protected $pagination;
    protected $state;
    protected $authors;


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

        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
            JError::raiseError(500, implode("\n", $errors));
            return false;
        }

        if ($this->getLayout() !== 'modal') $this->addToolbar();

        parent::display($tpl);
    }


    /**
     * Add the page title and toolbar.
     *
     */
    protected function addToolbar()
    {
        $access = ProjectforkHelper::getActions();

        JToolBarHelper::title(JText::_('COM_PROJECTFORK_TASKLISTS_TITLE'), 'article.png');

        if ($access->get('tasklist.create')) {
            JToolBarHelper::addNew('tasklist.add');
        }

        if ($access->get('tasklist.edit') || $access->get('tasklist.edit.own')) {
            JToolBarHelper::editList('tasklist.edit');
        }

        if ($access->get('tasklist.edit.state')) {
            JToolBarHelper::divider();
            JToolBarHelper::publish('tasklists.publish', 'JTOOLBAR_PUBLISH', true);
            JToolBarHelper::unpublish('tasklists.unpublish', 'JTOOLBAR_UNPUBLISH', true);
            JToolBarHelper::divider();
            JToolBarHelper::archiveList('tasklists.archive');
            JToolBarHelper::checkin('tasklists.checkin');
        }

        if ($this->state->get('filter.published') == -2 && $access->get('tasklist.delete')) {
            JToolBarHelper::deleteList('', 'tasklists.delete','JTOOLBAR_EMPTY_TRASH');
            JToolBarHelper::divider();
        }
        elseif ($access->get('tasklist.edit.state')) {
            JToolBarHelper::trash('tasklists.trash');
            JToolBarHelper::divider();
        }
    }
}
