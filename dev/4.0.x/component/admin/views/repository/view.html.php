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


class ProjectforkViewRepository extends JViewLegacy
{
    protected $items;
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
        $this->state      = $this->get('State');
        $this->items      = $this->get('Items');
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
        $access = ProjectforkHelperAccess::getActions(NULL, 0, true);
        $user   = JFactory::getUser();
        $state  = $this->state;

        JToolBarHelper::title(JText::_('COM_PROJECTFORK_REPO_TITLE'), 'article.png');

        if ($state->get('filter.project')) {
            if ($access->get('directory.create')) {
                JToolBarHelper::custom('directory.add', 'new.png', 'new_f2.png', 'JTOOLBAR_ADD_DIRECTORY', false);
            }

            if ($access->get('note.create')) {
                JToolBarHelper::custom('note.add', 'html.png', 'html_f2.png', 'JTOOLBAR_ADD_NOTE', false);
            }

            if ($access->get('file.create')) {
                JToolBarHelper::addNew('file.add');
            }

            if ($access->get('directory.delete') || $access->get('note.delete') || $access->get('file.delete')) {
                JToolBarHelper::divider();
                JToolBarHelper::deleteList('', 'repository.delete','JTOOLBAR_DELETE');
            }
        }
    }
}
