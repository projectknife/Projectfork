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


class ProjectforkViewComments extends JViewLegacy
{
    /**
     * A list of topics
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
     * A list of context options
     *
     * @var    array
     */
    protected $contexts;

    /**
     * A list of context item options
     *
     * @var    array
     */
    protected $cntxt_items;

    /**
     *
     * @var    string
     */
    protected $nulldate;


    /**
     * Display the view
     *
     * @param    string    $tpl    A template suffix
     * @retun    void
     */
    public function display($tpl = null)
    {
        // Get data from model
        $this->items       = $this->get('Items');
        $this->pagination  = $this->get('Pagination');
        $this->state       = $this->get('State');
        $this->authors     = $this->get('Authors');
        $this->contexts    = $this->get('Contexts');
        $this->cntxt_items = $this->get('ContextItems');

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
        $access = ProjectforkHelperAccess::getActions();
        $state  = $this->get('State');

        JToolBarHelper::title(JText::_('COM_PROJECTFORK_COMMENTS_TITLE'), 'article.png');

        if ($access->get('comment.create') && is_numeric($state->get('filter.item_id')) && $state->get('filter.context')) {
            JToolBarHelper::addNew('comment.add');
        }

        if ($access->get('comment.edit') || $access->get('comment.edit.own')) {
            JToolBarHelper::editList('comment.edit');
        }

        if ($access->get('comment.edit.state')) {
            JToolBarHelper::divider();
            JToolBarHelper::publish('comments.publish', 'JTOOLBAR_PUBLISH', true);
            JToolBarHelper::unpublish('comments.unpublish', 'JTOOLBAR_UNPUBLISH', true);
            JToolBarHelper::divider();
            JToolBarHelper::archiveList('comments.archive');
            JToolBarHelper::checkin('comments.checkin');
        }

        if ($this->state->get('filter.published') == -2 && $access->get('comment.delete')) {
            JToolBarHelper::deleteList('', 'comments.delete','JTOOLBAR_EMPTY_TRASH');
            JToolBarHelper::divider();
        }
        elseif ($access->get('comment.edit.state')) {
            JToolBarHelper::trash('comments.trash');
            JToolBarHelper::divider();
        }
    }
}
