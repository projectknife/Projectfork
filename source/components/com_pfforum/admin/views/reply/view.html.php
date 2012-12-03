<?php
/**
 * @package      Projectfork
 * @subpackage   Forum
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


jimport('joomla.application.component.view');


class PFforumViewReply extends JViewLegacy
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
        $access      = PFforumHelper::getReplyActions($this->item->id, $this->state->get('topic_id'));

        JToolBarHelper::title(JText::_('COM_PROJECTFORK_PAGE_' . ($checked_out ? 'VIEW_REPLY' : ($is_new ? 'ADD_REPLY' : 'EDIT_REPLY'))), 'article-add.png');

        // Build the actions for new and existing records.
        // For new records, check the create permission.
        if ($is_new && $this->state->get('topic_id')) {
            JToolBarHelper::apply('reply.apply');
            JToolBarHelper::save('reply.save');
            JToolBarHelper::save2new('reply.save2new');
            JToolBarHelper::cancel('reply.cancel');
        }
        else {
            // Can't save the record if it's checked out.
            if (!$checked_out) {
                if ($access->get('core.edit') || ($access->get('core.edit.own') && $this->item->created_by == $uid)) {
                    JToolBarHelper::apply('reply.apply');
                    JToolBarHelper::save('reply.save');
                    JToolBarHelper::save2new('reply.save2new');
                }
            }

            // JToolBarHelper::save2copy('reply.save2copy');
            JToolBarHelper::cancel('reply.cancel', 'JTOOLBAR_CLOSE');
        }
    }
}
