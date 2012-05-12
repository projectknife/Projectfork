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


class ProjectforkViewTasklists extends JView
{
    protected $items;
	protected $pagination;
	protected $state;
	protected $authors;


	/**
	 * Display the view
	 */
	public function display($tpl = null)
	{
	    $this->items	  = $this->get('Items');
		$this->pagination = $this->get('Pagination');
		$this->state	  = $this->get('State');
		$this->authors	  = $this->get('Authors');

	    // Check for errors.
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

		JToolBarHelper::title(JText::_('COM_PROJECTFORK_TASKLISTS_TITLE'), 'article.png');


        if($canDo->get('core.create') || $canDo->get('tasklist.create')) {
            JToolBarHelper::addNew('tasklist.add');
        }

        if($canDo->get('core.edit') || $canDo->get('core.edit.own') ||
           $canDo->get('tasklist.edit') || $canDo->get('tasklist.edit.own')
          ) {
            JToolBarHelper::editList('tasklist.edit');
        }

        if($canDo->get('core.edit.state') || $canDo->get('tasklist.edit.state')) {
            JToolBarHelper::divider();
            JToolBarHelper::publish('tasklists.publish', 'JTOOLBAR_PUBLISH', true);
		    JToolBarHelper::unpublish('tasklists.unpublish', 'JTOOLBAR_UNPUBLISH', true);
		    JToolBarHelper::divider();
		    JToolBarHelper::archiveList('tasklists.archive');
		    JToolBarHelper::checkin('tasklists.checkin');
        }

        if($this->state->get('filter.published') == -2 &&
            ($canDo->get('core.delete') || $canDo->get('tasklist.delete'))
          ) {
            JToolBarHelper::deleteList('', 'tasklists.delete','JTOOLBAR_EMPTY_TRASH');
            JToolBarHelper::divider();
        }
        elseif ($canDo->get('core.edit.state') || $canDo->get('tasklist.edit.state')) {
			JToolBarHelper::trash('tasklists.trash');
			JToolBarHelper::divider();
		}
	}
}
