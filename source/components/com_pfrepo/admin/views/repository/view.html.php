<?php
/**
 * @package      Projectfork
 * @subpackage   Repository
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


jimport('joomla.application.component.view');


class PFrepoViewRepository extends JViewLegacy
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
        $user  = JFactory::getUser();
        $state = $this->get('State');

        JToolBarHelper::title(JText::_('COM_PROJECTFORK_REPO_TITLE'), 'article.png');

        if ($state->get('filter.project') && $this->items['directory']->id > 1) {
            $access = PFrepoHelper::getActions('directory', $this->items['directory']->id);

            if ($access->get('core.create')) {
                JToolBarHelper::custom('directory.add', 'new.png', 'new_f2.png', 'JTOOLBAR_ADD_DIRECTORY', false);
                JToolBarHelper::custom('file.add', 'upload.png', 'upload_f2.png', 'JTOOLBAR_ADD_FILE', false);
                JToolBarHelper::custom('note.add', 'copy.png', 'html_f2.png', 'JTOOLBAR_ADD_NOTE', false);
            }

            if ($access->get('core.delete')) {
                JToolBarHelper::divider();
                JToolBarHelper::deleteList('', 'repository.delete','JTOOLBAR_DELETE');
            }
        }

        if ($user->authorise('core.admin')) {
            JToolBarHelper::preferences('com_pfrepo');
        }
    }
}
