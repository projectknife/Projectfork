<?php
/**
 * @package      Projectfork
 * @subpackage   Projects
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


jimport('joomla.application.component.view');


class PFprojectsViewProject extends JViewLegacy
{
    protected $form;
    protected $item;
    protected $state;


    /**
     * Displays the view.
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
     * Adds the page title and toolbar.
     *
     */
    protected function addToolbar()
    {
        JRequest::setVar('hidemainmenu', true);

        $uid         = JFactory::getUser()->get('id');
        $access      = PFprojectsHelper::getActions($this->item->id);
        $checked_out = !($this->item->checked_out == 0 || $this->item->checked_out == $uid);
        $is_new      = ((int) $this->item->id == 0);

        JToolBarHelper::title(JText::_('COM_PROJECTFORK_PAGE_' . ($checked_out ? 'VIEW_PROJECT' : ($is_new ? 'ADD_PROJECT' : 'EDIT_PROJECT'))), 'article-add.png');

        // Build the actions for new and existing records
        // For new records, check the create permission.
        if ($is_new) {
            JToolBarHelper::apply('project.apply');
            JToolBarHelper::save('project.save');
            JToolBarHelper::save2new('project.save2new');
            JToolBarHelper::cancel('project.cancel');
        }
        else {
            // Can't save the record if it's checked out.
            if (!$checked_out) {
                if ($access->get('core.edit') || ($access->get('core.edit.own') && $this->item->created_by == $uid)) {
                    JToolBarHelper::apply('project.apply');
                    JToolBarHelper::save('project.save');
                    JToolBarHelper::save2new('project.save2new');
                }
            }

            JToolBarHelper::save2copy('project.save2copy');
            JToolBarHelper::cancel('project.cancel', 'JTOOLBAR_CLOSE');
        }
    }
}
