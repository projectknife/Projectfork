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


class ProjectforkViewDashboard extends JView
{
    /**
     * Display the view
     *
     */
    public function display($tpl = null)
    {
        if ($this->getLayout() !== 'modal') $this->addToolbar();

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
