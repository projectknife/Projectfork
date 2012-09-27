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


class ProjectforkViewMilestone extends JViewLegacy
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
        $is_new      = ($this->item->id == 0);
        $checked_out = !($this->item->checked_out == 0 || $this->item->checked_out == $uid);
        $access      = ProjectforkHelperAccess::getActions('milestone', $this->item->id);

        JToolBarHelper::title(JText::_('COM_PROJECTFORK_PAGE_' . ($checked_out ? 'VIEW_MILESTONE' : ($is_new ? 'ADD_MILESTONE' : 'EDIT_MILESTONE'))), 'article-add.png');

        // Built the actions for new and existing records.
        // For new records, check the create permission.
        if ($is_new) {
            JToolBarHelper::apply('milestone.apply');
            JToolBarHelper::save('milestone.save');
            JToolBarHelper::save2new('milestone.save2new');
            JToolBarHelper::cancel('milestone.cancel');
        }
        else {
            // Can't save the record if it's checked out.
            if (!$checked_out) {
                if ($access->get('milestone.edit') || ($access->get('milestone.edit.own') && $this->item->created_by == $uid)) {
                    JToolBarHelper::apply('milestone.apply');
                    JToolBarHelper::save('milestone.save');
                    JToolBarHelper::save2new('milestone.save2new');
                }
            }

            JToolBarHelper::save2copy('milestone.save2copy');
            JToolBarHelper::cancel('milestone.cancel', 'JTOOLBAR_CLOSE');
        }
    }
}
