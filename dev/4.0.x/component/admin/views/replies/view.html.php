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


class ProjectforkViewReplies extends JViewLegacy
{
    /**
     * A list of replies
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
     *
     * @var    string
     */
    protected $nulldate;

    protected $sidebar;


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
     * Add the page title and toolbar.
     *
     * @return    void
     */
    protected function addToolbar()
    {
        $access = ProjectforkHelperAccess::getActions('topic', (int) $this->state->get('filter.topic'));
        $user   = JFactory::getUser();

        JToolBarHelper::title(JText::_('COM_PROJECTFORK_REPLIES_TITLE'), 'article.png');

        if ($access->get('reply.create')) {
            JToolBarHelper::addNew('reply.add');
        }

        if ($access->get('reply.edit')) {
            JToolBarHelper::editList('reply.edit');
        }

        if ($access->get('reply.edit.state')) {
            JToolBarHelper::divider();
            JToolBarHelper::publish('replies.publish', 'JTOOLBAR_PUBLISH', true);
            JToolBarHelper::unpublish('replies.unpublish', 'JTOOLBAR_UNPUBLISH', true);
            JToolBarHelper::divider();
            JToolBarHelper::archiveList('replies.archive');
            JToolBarHelper::checkin('replies.checkin');
        }

        if ($this->state->get('filter.published') == -2 && $access->get('reply.delete')) {
            JToolBarHelper::deleteList('', 'replies.delete','JTOOLBAR_EMPTY_TRASH');
            JToolBarHelper::divider();
        }
        elseif ($access->get('reply.edit.state')) {
            JToolBarHelper::trash('replies.trash');
            JToolBarHelper::divider();
        }

        // Deal with Joomla 3 sidebar
        if (version_compare(JVERSION, '3.0.0', 'ge')) {
            ProjectforkHelper::addSubmenu($this->getName());
            $this->sidebar = JHtmlSidebar::render();
        }
    }
}
