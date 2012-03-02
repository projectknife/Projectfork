<?php
// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

class ProjectforkViewTask extends JView
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
		$this->form	 = $this->get('Form');
		$this->item	 = $this->get('Item');
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

		$user		= JFactory::getUser();
		$userId		= $user->get('id');
		$isNew		= ($this->item->id == 0);
		$checkedOut	= !($this->item->checked_out == 0 || $this->item->checked_out == $userId);
		JToolBarHelper::title(JText::_('COM_PROJECTFORK_PAGE_'.($checkedOut ? 'VIEW_TASK' : ($isNew ? 'ADD_TASK' : 'EDIT_TASK'))), 'article-add.png');

		// Build the actions for new and existing records.
		// For new records, check the create permission.
		if ($isNew) {
			JToolBarHelper::apply('task.apply');
			JToolBarHelper::save('task.save');
			JToolBarHelper::save2new('task.save2new');
			JToolBarHelper::cancel('task.cancel');
		}
		else {
			// Can't save the record if it's checked out.
			if (!$checkedOut) {
				if($this->item->created_by == $userId) {
					JToolBarHelper::apply('task.apply');
					JToolBarHelper::save('task.save');
                    JToolBarHelper::save2new('task.save2new');
				}
			}

			JToolBarHelper::save2copy('task.save2copy');
			JToolBarHelper::cancel('task.cancel', 'JTOOLBAR_CLOSE');
		}
	}
}