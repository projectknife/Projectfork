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

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.controller');


class ProjectforkController extends JController
{
	/**
	 * @var    string    The default view
	 */
	protected $default_view = 'dashboard';


	public function display($cachable = false, $urlparams = false)
	{
		require_once JPATH_COMPONENT.'/helpers/projectfork.php';

        JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');

		// Load the submenu.
		ProjectforkHelper::addSubmenu(JRequest::getCmd('view', 'dashboard'));

		parent::display();

		return $this;
	}


    public function activate()
    {
        $data = array();
        $data['id'] = JRequest::GetInt('id');

        $model = $this->getModel('project');
        $app   = JFactory::getApplication();

        $model->activate($data);

        $return = base64_decode(JRequest::getVar('return'));
        $app->redirect($return);

        return $this;
    }
}
