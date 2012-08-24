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


class ProjectforkViewComment extends JView
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
        $is_new      = ($this->item->id == 0);
        $checked_out = !($this->item->checked_out == 0 || $this->item->checked_out == $uid);
        $access      = ProjectforkHelperAccess::getActions('comment', $this->item->id);


        JToolBarHelper::title(JText::_('COM_PROJECTFORK_PAGE_' . ($checked_out ? 'VIEW_COMMENT' : ($is_new ? 'ADD_COMMENT' : 'EDIT_COMMENT'))), 'article-add.png');

        // Built the actions for new and existing records.
        // For new records, check the create permission.
        if ($is_new) {
            JToolBarHelper::apply('comment.apply');
            JToolBarHelper::save('comment.save');
            JToolBarHelper::save2new('comment.save2new');
            JToolBarHelper::cancel('comment.cancel');
        }
        else {
            // Can't save the record if it's checked out.
            if (!$checked_out) {
                if ($access->get('comment.edit') || ($access->get('comment.edit.own') && $this->item->created_by == $uid)) {
                    JToolBarHelper::apply('comment.apply');
                    JToolBarHelper::save('comment.save');
                    JToolBarHelper::save2new('comment.save2new');
                }
            }

            JToolBarHelper::save2copy('comment.save2copy');
            JToolBarHelper::cancel('comment.cancel', 'JTOOLBAR_CLOSE');
        }
    }
}
