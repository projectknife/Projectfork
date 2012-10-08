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


class ProjectforkViewTopic extends JViewLegacy
{
    protected $form;
    protected $item;
    protected $state;


    /**
     * Display the view
     *
     * @return    void
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
     * @return    void
     */
    protected function addToolbar()
    {
        JRequest::setVar('hidemainmenu', true);

        $uid         = JFactory::getUser()->get('id');
        $is_new      = ($this->item->id == 0);
        $checked_out = !($this->item->checked_out == 0 || $this->item->checked_out == $uid);
        $access      = ProjectforkHelperAccess::getActions('topic', $this->item->id);

        JToolBarHelper::title(JText::_('COM_PROJECTFORK_PAGE_' . ($checked_out ? 'VIEW_TOPIC' : ($is_new ? 'ADD_TOPIC' : 'EDIT_TOPIC'))), 'article-add.png');

        // Build the actions for new and existing records.
        // For new records, check the create permission.
        if ($is_new) {
            JToolBarHelper::apply('topic.apply');
            JToolBarHelper::save('topic.save');
            JToolBarHelper::save2new('topic.save2new');
            JToolBarHelper::cancel('topic.cancel');
        }
        else {
            // Can't save the record if it's checked out.
            if (!$checked_out) {
                if ($access->get('topic.edit') || ($access->get('topic.edit.own') && $this->item->created_by == $uid)) {
                    JToolBarHelper::apply('topic.apply');
                    JToolBarHelper::save('topic.save');
                    JToolBarHelper::save2new('topic.save2new');
                }
            }

            JToolBarHelper::save2copy('topic.save2copy');
            JToolBarHelper::cancel('topic.cancel', 'JTOOLBAR_CLOSE');
        }
    }
}
