<?php
/**
 * @package      Projectfork
 * @subpackage   Tasks
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


jimport('joomla.application.component.view');


class PFtasksViewTasklist extends JViewLegacy
{
    protected $form;
    protected $item;
    protected $state;


    /**
     * Display the view
     *
     */
    public function display($tpl = null)
    {

        // Initialiase variables.
        $this->form  = $this->get('Form');
        $this->item  = $this->get('Item');
        $this->state = $this->get('State');

        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
            JError::raiseError(500, implode("\n", $errors));
            return false;
        }

        $this->addToolbar();

        parent::display($tpl);
    }


    /**
     * Add the page title and toolbar.
     *
     */
    protected function addToolbar()
    {
        JRequest::setVar('hidemainmenu', true);

        $uid         = JFactory::getUser()->get('id');
        $access      = PFtasksHelper::getListActions($this->item->id);
        $checked_out = !($this->item->checked_out == 0 || $this->item->checked_out == $uid);
        $is_new      = ($this->item->id == 0);

        JToolBarHelper::title(JText::_('COM_PROJECTFORK_PAGE_' . ($checked_out ? 'VIEW_TASKLIST' : ($is_new ? 'ADD_TASKLIST' : 'EDIT_TASKLIST'))), 'article-add.png');

        // Built the actions for new and existing records.
        // For new records, check the create permission.
        if ($is_new) {
            JToolBarHelper::apply('tasklist.apply');
            JToolBarHelper::save('tasklist.save');
            JToolBarHelper::save2new('tasklist.save2new');
            JToolBarHelper::cancel('tasklist.cancel');
        }
        else {
            // Can't save the record if it's checked out.
            if (!$checked_out) {
                if ($access->get('core.edit') || ($access->get('core.edit.own') && $this->item->created_by == $uid)) {
                    JToolBarHelper::apply('tasklist.apply');
                    JToolBarHelper::save('tasklist.save');
                    JToolBarHelper::save2new('tasklist.save2new');
                }
            }

            JToolBarHelper::save2copy('tasklist.save2copy');
            JToolBarHelper::cancel('tasklist.cancel', 'JTOOLBAR_CLOSE');
        }
    }
}
