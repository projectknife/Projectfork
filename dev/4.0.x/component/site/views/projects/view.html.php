<?php
/**
* @package   Projectfork
* @copyright Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
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

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.application.component.view');


class ProjectforkViewProjects extends JView
{
    /**
	 * Display the view
     *
	 */
	public function display($tpl = null)
	{
	    $items      = $this->get('Items');
        $pagination = $this->get('Pagination');
        $state		= $this->get('State');
		$params		= $state->params;
        $null_date  = JFactory::getDbo()->getNullDate();
        $user       = JFactory::getUser();
        $actions    = $this->getActions();
        $toolbar    = $this->getToolbar();
        $canDo      = ProjectforkHelper::getActions();


        // Escape strings for HTML output
		$this->pageclass_sfx = htmlspecialchars($params->get('pageclass_sfx'));

        // Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}


        $this->assignRef('items',      $items);
        $this->assignRef('pagination', $pagination);
        $this->assignRef('params',     $params);
        $this->assignRef('state',      $state);
        $this->assignRef('nulldate',   $null_date);
        $this->assignRef('actions',    $actions);
        $this->assignRef('toolbar',    $toolbar);
        $this->assignRef('user',       $user);
        $this->assignRef('canDo',      $canDo);


		parent::display($tpl);
	}


    public function getToolbar()
    {
        $canDo = ProjectforkHelper::getActions();
		$user  = JFactory::getUser();
        $tb    = new ProjectforkHelperToolbar();

        if($canDo->get('core.create') || $canDo->get('project.create')) {
            $tb->button('COM_PROJECTFORK_ACTION_NEW', 'project.add');
        }

        return $tb->__toString();
    }


    public function getActions()
    {
        $canDo   = ProjectforkHelper::getActions();
		$user    = JFactory::getUser();
        $state	 = $this->get('State');
        $options = array();

        if($canDo->get('core.edit.state') || $canDo->get('project.edit.state')) {
            $options[] = JHtml::_('select.option', 'projects.publish', JText::_('COM_PROJECTFORK_ACTION_PUBLISH'));
            $options[] = JHtml::_('select.option', 'projects.unpublish', JText::_('COM_PROJECTFORK_ACTION_UNPUBLISH'));
            $options[] = JHtml::_('select.option', 'projects.archive', JText::_('COM_PROJECTFORK_ACTION_ARCHIVE'));
            $options[] = JHtml::_('select.option', 'projects.checkin', JText::_('COM_PROJECTFORK_ACTION_CHECKIN'));
        }
        if($state->get('filter.published') == -2 &&
           ($canDo->get('core.delete') || $canDo->get('project.delete'))
          ) {
            $options[] = JHtml::_('select.option', 'projects.delete', JText::_('COM_PROJECTFORK_ACTION_DELETE'));
        }
        elseif ($canDo->get('core.edit.state') || $canDo->get('project.edit.state')) {
			$options[] = JHtml::_('select.option', 'projects.trash', JText::_('COM_PROJECTFORK_ACTION_TRASH'));
		}

        return $options;
    }
}