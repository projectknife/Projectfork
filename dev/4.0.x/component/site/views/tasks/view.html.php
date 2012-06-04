<?php
/**
* @package   Projectfork
* @copyright Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.php
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

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.application.component.view');


class ProjectforkViewTasks extends JView
{
    /**
	 * Display the view
     *
	 */
	public function display($tpl = null)
	{
	    $app   = JFactory::getApplication();
        $state = $this->get('State');

	    // Check for layout override
		$active	= $app->getMenu()->getActive();
		if (isset($active->query['layout']) && (JRequest::getCmd('layout') == '')) {
			$this->setLayout($active->query['layout']);
		}

        // Set list limit to 0 if default layout
        if($this->getLayout() == '' || $this->getLayout() == 'default') {
            $state->set('list.limit', 0);
        }

        $null_date  = JFactory::getDbo()->getNullDate();
        $user       = JFactory::getUser();
	    $items      = $this->get('Items');
        $pagination = $this->get('Pagination');
        $state		= $this->get('State');
        $milestones = $this->get('Milestones');
        $lists      = $this->get('TaskLists');
        $authors    = $this->get('Authors');
        $assigned   = $this->get('AssignedUsers');
        $states     = $this->get('PublishedStates');
        $priorities = $this->get('Priorities');
		$params		= $state->params;
        $actions    = $this->getActions();
        $toolbar    = $this->getToolbar();
        $canDo      = ProjectforkHelper::getActions();
        $menu       = new ProjectforkHelperContextMenu();


        // Escape strings for HTML output
		$this->pageclass_sfx = htmlspecialchars($params->get('pageclass_sfx'));


        // Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

        // Check for empty search result
        if((count($items) == 0) && ($state->get('filter.search') != '' || $state->get('filter.author') != ''
            || $state->get('filter.assigned') != '' || $state->get('filter.priority') != ''
            || $state->get('filter.tasklist') != '' || $state->get('filter.milestone') != '')
          ) {
            $app->enqueueMessage(JText::_('COM_PROJECTFORK_EMPTY_SEARCH_RESULT'));
        }


        // Assign references
        $this->assignRef('items',      $items);
        $this->assignRef('pagination', $pagination);
        $this->assignRef('params',     $params);
        $this->assignRef('state',      $state);
        $this->assignRef('nulldate',   $null_date);
        $this->assignRef('actions',    $actions);
        $this->assignRef('toolbar',    $toolbar);
        $this->assignRef('canDo',      $canDo);
        $this->assignRef('menu',       $menu);
        $this->assignRef('milestones', $milestones);
        $this->assignRef('tasklists',  $lists);
        $this->assignRef('authors',    $authors);
        $this->assignRef('states',     $states);
        $this->assignRef('assigned',   $assigned);
        $this->assignRef('priorities', $priorities);


        // Prepare the document
        $this->prepareDocument();


        // Display the view
		parent::display($tpl);
	}


    /**
	 * Prepares the document
     *
	 */
	protected function prepareDocument()
	{
		$app		= JFactory::getApplication();
		$menus		= $app->getMenu();
		$pathway	= $app->getPathway();
		$title		= null;

		// Because the application sets a default page title,
		// we need to get it from the menu item itself
		$menu = $menus->getActive();

		if ($menu) {
			$this->params->def('page_heading', $this->params->get('page_title', $menu->title));
		}
		else {
			$this->params->def('page_heading', JText::_('COM_PROJECTFORK_TASKS'));
		}


        // Set the page title
		$title = $this->params->get('page_title', '');

		if (empty($title)) {
			$title = $app->getCfg('sitename');
		}
		elseif ($app->getCfg('sitename_pagetitles', 0) == 1) {
			$title = JText::sprintf('JPAGETITLE', $app->getCfg('sitename'), $title);
		}
		elseif ($app->getCfg('sitename_pagetitles', 0) == 2) {
			$title = JText::sprintf('JPAGETITLE', $title, $app->getCfg('sitename'));
		}

		$this->document->setTitle($title);


        // Set crawler behavior info
		if ($this->params->get('robots')) {
			$this->document->setMetadata('robots', $this->params->get('robots'));
		}


        // Set page description
        if($this->params->get('menu-meta_description')) {
            $this->document->setDescription($desc);
        }


        // Set page keywords
        if($this->params->get('menu-meta_keywords')) {
            $this->document->setMetadata('keywords', $keywords);
        }


		// Add feed links
		if ($this->params->get('show_feed_link', 1)) {
			$link = '&format=feed&limitstart=';
			$attribs = array('type' => 'application/rss+xml', 'title' => 'RSS 2.0');
			$this->document->addHeadLink(JRoute::_($link . '&type=rss'), 'alternate', 'rel', $attribs);
			$attribs = array('type' => 'application/atom+xml', 'title' => 'Atom 1.0');
			$this->document->addHeadLink(JRoute::_($link . '&type=atom'), 'alternate', 'rel', $attribs);
		}
	}


    /**
	 * Generates the toolbar for the top of the view
     *
     * @return    string    Toolbar with buttons
	 */
    protected function getToolbar()
    {
        $canDo = ProjectforkHelper::getActions();
		$user  = JFactory::getUser();
        $tb    = new ProjectforkHelperToolbar();


        $canCreateList = $canDo->get('core.create') || $canDo->get('tasklist.create');
        $canCreateTask = $canDo->get('core.create') || $canDo->get('task.create');

        if($canCreateTask && $canCreateList) {
            $items = array();
            $items['tasklistform.add'] = array('text' => 'COM_PROJECTFORK_ACTION_NEW_TASKLIST');

            $tb->dropdownButton($items, 'COM_PROJECTFORK_ACTION_NEW', 'taskform.add', false);
        }
        else {
            if($canCreateList) {
                $tb->button('COM_PROJECTFORK_ACTION_NEW_TASKLIST', 'tasklistform.add');
            }
            if($canCreateTask) {
                $tb->button('COM_PROJECTFORK_ACTION_NEW_TASK', 'taskform.add');
            }
        }

        return $tb->__toString();
    }


    /**
	 * Generates select options for the bulk action menu
     *
     * @return    array    The available options
	 */
    protected function getActions()
    {
        $canDo   = ProjectforkHelper::getActions();
		$user    = JFactory::getUser();
        $state	 = $this->get('State');
        $options = array();


        if($canDo->get('core.edit.state') || $canDo->get('task.edit.state')) {
            $options[] = JHtml::_('select.option', 'tasks.publish', JText::_('COM_PROJECTFORK_ACTION_PUBLISH'));
            $options[] = JHtml::_('select.option', 'tasks.unpublish', JText::_('COM_PROJECTFORK_ACTION_UNPUBLISH'));
            $options[] = JHtml::_('select.option', 'tasks.archive', JText::_('COM_PROJECTFORK_ACTION_ARCHIVE'));
            $options[] = JHtml::_('select.option', 'tasks.checkin', JText::_('COM_PROJECTFORK_ACTION_CHECKIN'));
        }
        if($state->get('filter.published') == -2 &&
           ($canDo->get('core.delete') || $canDo->get('task.delete'))
          ) {
            $options[] = JHtml::_('select.option', 'tasks.delete', JText::_('COM_PROJECTFORK_ACTION_DELETE'));
        }
        elseif ($canDo->get('core.edit.state') || $canDo->get('task.edit.state')) {
			$options[] = JHtml::_('select.option', 'tasks.trash', JText::_('COM_PROJECTFORK_ACTION_TRASH'));
		}


        return $options;
    }
}