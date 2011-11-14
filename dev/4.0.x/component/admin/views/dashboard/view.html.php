<?php
/**
* @package   Projectfork
* @copyright Copyright (C) 2006-2011 Tobias Kuhn. All rights reserved.
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.php
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

class ProjectforkViewDashboard extends JView
{
	/**
	 * Display the view
     *
	 */
	public function display($tpl = null)
	{
		if($this->getLayout() !== 'modal') $this->addToolbar();
		parent::display($tpl);
	}


	/**
	 * Add the page title and toolbar.
	 *
	 */
	protected function addToolbar()
	{
		$acl  = ProjectforkHelper::getActions();
		$user = JFactory::getUser();

		JToolBarHelper::title(JText::_('COM_PROJECTFORK_DASHBOARD_TITLE'), 'article.png');

		if ($acl->get('core.admin')) {
			JToolBarHelper::preferences('com_projectfork');
		}
	}
}
?>