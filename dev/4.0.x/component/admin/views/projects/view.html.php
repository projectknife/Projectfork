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


class ProjectforkViewProjects extends JViewLegacy
{
    protected $items;
    protected $pagination;
    protected $state;
    protected $authors;
    protected $nulldate;


    /**
     * Displays the view.
     *
     */
    public function display($tpl = null)
    {
        // Get data from model
        $this->items      = $this->get('Items');
        $this->pagination = $this->get('Pagination');
        $this->state      = $this->get('State');
        $this->authors    = $this->get('Authors');
        $this->nulldate   = JFactory::getDbo()->getNullDate();

        // Check for errors
        if (count($errors = $this->get('Errors'))) {
            JError::raiseError(500, implode("\n", $errors));
            return false;
        }

        if ($this->getLayout() !== 'modal') $this->addToolbar();

        parent::display($tpl);
    }


    /**
     * Adds the page title and toolbar.
     *
     */
    protected function addToolbar()
    {
        $access = ProjectforkHelper::getActions();
        $user   = JFactory::getUser();

        JToolBarHelper::title(JText::_('COM_PROJECTFORK_PROJECTS_TITLE'), 'article.png');

        if ($access->get('project.create')) {
            JToolBarHelper::addNew('project.add');
        }

        if ($access->get('project.edit') || $access->get('project.edit.own')) {
            JToolBarHelper::editList('project.edit');
        }

        if ($access->get('project.edit.state')) {
            JToolBarHelper::divider();
            JToolBarHelper::publish('projects.publish', 'JTOOLBAR_PUBLISH', true);
            JToolBarHelper::unpublish('projects.unpublish', 'JTOOLBAR_UNPUBLISH', true);
            JToolBarHelper::divider();
            JToolBarHelper::archiveList('projects.archive');
            JToolBarHelper::checkin('projects.checkin');
        }

        if ($this->state->get('filter.published') == -2 && $access->get('project.delete')) {
            JToolBarHelper::deleteList('', 'projects.delete','JTOOLBAR_EMPTY_TRASH');
            JToolBarHelper::divider();
        }
        elseif ($access->get('project.edit.state')) {
            JToolBarHelper::trash('projects.trash');
            JToolBarHelper::divider();
        }
    }
}
