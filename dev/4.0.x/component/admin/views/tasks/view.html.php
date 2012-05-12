<?php
/**
* @package   Projectfork
* @copyright Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL, see license.txt
*
* This file is part of Projectfork.
*
* Projectfork is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
*
* Projectfork is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with Projectfork. If not, see <http://www.gnu.org/licenses/gpl.html>.
**/


defined('_JEXEC') or die;

jimport('joomla.application.component.view');


class ProjectforkViewTasks extends JView
{
    protected $items;
	protected $pagination;
	protected $state;
	protected $authors;
    protected $assigned;
    protected $tasklists;
    protected $milestones;
    protected $nulldate;


	/**
	 * Display the view
     *
	 */
	public function display($tpl = null)
	{
		// Get data from model
        $this->items	  = $this->get('Items');
		$this->pagination = $this->get('Pagination');
		$this->state	  = $this->get('State');
		$this->authors	  = $this->get('Authors');
		$this->assigned	  = $this->get('AssignedUsers');
		$this->tasklists  = $this->get('Tasklists');
		$this->milestones = $this->get('Milestones');

        // Get database null date
        $this->nulldate = JFactory::getDbo()->getNullDate();


	    // Check for errors
		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		if($this->getLayout() !== 'modal') $this->addToolbar();

		parent::display($tpl);
	}


	/**
	 * Add the page title and toolbar.
	 *
	 */
	protected function addToolbar()
	{
		$canDo = ProjectforkHelper::getActions();
		$user  = JFactory::getUser();

		JToolBarHelper::title(JText::_('COM_PROJECTFORK_TASKS_TITLE'), 'article.png');


        if($canDo->get('core.create') || $canDo->get('task.create')) {
            JToolBarHelper::addNew('task.add');
        }

        if($canDo->get('core.edit') || $canDo->get('core.edit.own') ||
           $canDo->get('task.edit') || $canDo->get('task.edit.own')
          ) {
            JToolBarHelper::editList('task.edit');
        }

        if($canDo->get('core.edit.state') || $canDo->get('task.edit.state')) {
            JToolBarHelper::divider();
            JToolBarHelper::publish('tasks.publish', 'JTOOLBAR_PUBLISH', true);
		    JToolBarHelper::unpublish('tasks.unpublish', 'JTOOLBAR_UNPUBLISH', true);
		    JToolBarHelper::divider();
		    JToolBarHelper::archiveList('tasks.archive');
		    JToolBarHelper::checkin('tasks.checkin');
        }

        if($this->state->get('filter.published') == -2 &&
            ($canDo->get('core.delete') || $canDo->get('task.delete'))
          ) {
            JToolBarHelper::deleteList('', 'tasks.delete','JTOOLBAR_EMPTY_TRASH');
            JToolBarHelper::divider();
        }
        elseif ($canDo->get('core.edit.state') || $canDo->get('task.edit.state')) {
			JToolBarHelper::trash('tasks.trash');
			JToolBarHelper::divider();
		}
	}
}
