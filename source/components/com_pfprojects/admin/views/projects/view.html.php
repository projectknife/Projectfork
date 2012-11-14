<?php
/**
 * @package      Projectfork
 * @subpackage   Projects
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


jimport('joomla.application.component.view');


class PFprojectsViewProjects extends JViewLegacy
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
        $user = JFactory::getUser();

        JToolBarHelper::title(JText::_('COM_PROJECTFORK_PROJECTS_TITLE'), 'article.png');

        if ($user->authorise('core.create')) {
            JToolBarHelper::addNew('project.add');
        }

        if ($user->authorise('core.edit') || $user->authorise('core.edit.own')) {
            JToolBarHelper::editList('project.edit');
        }

        if ($user->authorise('core.edit.state')) {
            JToolBarHelper::divider();
            JToolBarHelper::publish('projects.publish', 'JTOOLBAR_PUBLISH', true);
            JToolBarHelper::unpublish('projects.unpublish', 'JTOOLBAR_UNPUBLISH', true);
            JToolBarHelper::divider();
            JToolBarHelper::archiveList('projects.archive');
            JToolBarHelper::checkin('projects.checkin');
        }

        if ($this->state->get('filter.published') == -2 && $user->authorise('core.delete')) {
            JToolBarHelper::deleteList('', 'projects.delete','JTOOLBAR_EMPTY_TRASH');
            JToolBarHelper::divider();
        }
        elseif ($user->authorise('core.edit.state')) {
            JToolBarHelper::trash('projects.trash');
            JToolBarHelper::divider();
        }

        if ($user->authorise('core.admin')) {
            JToolBarHelper::preferences('com_pfprojects');
        }
    }
}
