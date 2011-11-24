<?php
/**
* @package   Projectfork
* @copyright Copyright (C) 2006-2011 Tobias Kuhn. All rights reserved.
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL, see license.txt
*
* This file is part of Projectfork.
*
* Projectfork is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
*
* Projectfork is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with Projectfork. If not, see <http://www.gnu.org/licenses/gpl.html>.
**/

defined('_JEXEC') or die;

jimport('joomla.application.component.view');

class ProjectforkViewProjectform extends JView
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
		JToolBarHelper::title(JText::_('COM_PROJECTFORK_PAGE_'.($checkedOut ? 'VIEW_PROJECT' : ($isNew ? 'ADD_PROJECT' : 'EDIT_PROJECT'))), 'article-add.png');

		// Built the actions for new and existing records.

		// For new records, check the create permission.
		if ($isNew) {
			JToolBarHelper::apply('projectform.apply');
			JToolBarHelper::save('projectform.save');
			JToolBarHelper::save2new('projectform.save2new');
			JToolBarHelper::cancel('projectform.cancel');
		}
		else {
			// Can't save the record if it's checked out.
			if (!$checkedOut) {
				if($this->item->created_by == $userId) {
					JToolBarHelper::apply('projectform.apply');
					JToolBarHelper::save('projectform.save');
                    JToolBarHelper::save2new('projectform.save2new');
				}
			}

			JToolBarHelper::save2copy('projectform.save2copy');
			JToolBarHelper::cancel('projectform.cancel', 'JTOOLBAR_CLOSE');
		}
	}
}
?>