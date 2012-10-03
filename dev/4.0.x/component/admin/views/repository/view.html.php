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
    protected $pagination;


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

        if ($this->items['directory']->id == '1') {
            $this->pagination = null;
            //$this->pagination = $this->get('Pagination');
        }
        else {
            $this->pagination = null;
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
        $state = $this->get('State');

        if ($state->get('filter.project') && $this->items['directory']->id > 1) {
            $access = ProjectforkHelperAccess::getActions('directory', $this->items['directory']->id);

            JToolBarHelper::title(JText::_('COM_PROJECTFORK_REPO_TITLE'), 'article.png');
            if ($access->get('directory.create')) {
                JToolBarHelper::custom('directory.add', 'new.png', 'new_f2.png', 'JTOOLBAR_ADD_DIRECTORY', false);
            }

            if ($access->get('file.create')) {
                JToolBarHelper::custom('file.add', 'upload.png', 'upload_f2.png', 'JTOOLBAR_ADD_FILE', false);
            }

            if ($access->get('note.create')) {
                JToolBarHelper::custom('note.add', 'html.png', 'html_f2.png', 'JTOOLBAR_ADD_NOTE', false);
            }

            if ($access->get('directory.delete') || $access->get('note.delete') || $access->get('file.delete')) {
                JToolBarHelper::divider();
                JToolBarHelper::deleteList('', 'repository.delete','JTOOLBAR_DELETE');
            }
        }
    }
}
