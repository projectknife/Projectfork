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


class ProjectforkViewMilestones extends JViewLegacy
{
    protected $items;
    protected $pagination;
    protected $state;
    protected $authors;
    protected $nulldate;
    protected $sidebar;


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

        // Get database null date
        $this->nulldate = JFactory::getDbo()->getNullDate();

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
        $access = ProjectforkHelperAccess::getActions(NULL, 0, true);

        JToolBarHelper::title(JText::_('COM_PROJECTFORK_MILESTONES_TITLE'), 'article.png');

        if ($access->get('milestone.create')) {
            JToolBarHelper::addNew('milestone.add');
        }

        if ($access->get('milestone.edit') || $access->get('milestone.edit.own')) {
            JToolBarHelper::editList('milestone.edit');
        }

        if ($access->get('milestone.edit.state')) {
            JToolBarHelper::divider();
            JToolBarHelper::publish('milestones.publish', 'JTOOLBAR_PUBLISH', true);
            JToolBarHelper::unpublish('milestones.unpublish', 'JTOOLBAR_UNPUBLISH', true);
            JToolBarHelper::divider();
            JToolBarHelper::archiveList('milestones.archive');
            JToolBarHelper::checkin('milestones.checkin');
        }

        if ($this->state->get('filter.published') == -2 && $access->get('milestone.delete')) {
            JToolBarHelper::deleteList('', 'milestones.delete','JTOOLBAR_EMPTY_TRASH');
            JToolBarHelper::divider();
        }
        elseif ($access->get('milestone.edit.state')) {
            JToolBarHelper::trash('milestones.trash');
            JToolBarHelper::divider();
        }

        // Deal with Joomla 3 sidebar
        if (version_compare(JVERSION, '3.0.0', 'ge')) {
            ProjectforkHelper::addSubmenu($this->getName());
            $this->sidebar = JHtmlSidebar::render();
        }
    }
}
