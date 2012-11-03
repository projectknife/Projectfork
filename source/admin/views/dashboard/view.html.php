<?php
/**
 * @package      Projectfork
 * @subpackage   Dashboard
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


jimport('joomla.application.component.view');


class ProjectforkViewDashboard extends JViewLegacy
{
    /**
     * The list of available components
     *
     * @var    array
     */
    protected $components;

    /**
     * The current user object
     *
     * @var    object
     */
    protected $user;


    /**
     * Display the view
     *
     */
    public function display($tpl = null)
    {
        $this->components = PFapplicationHelper::getComponents();
        $this->user       = JFactory::getUser();

        if ($this->getLayout() !== 'modal') $this->addToolbar();

        parent::display($tpl);
    }


    /**
     * Add the page title and toolbar.
     *
     */
    protected function addToolbar()
    {
        JToolBarHelper::title(JText::_('COM_PROJECTFORK_DASHBOARD_TITLE'), 'article.png');

        if (JFactory::getUser()->authorise('core.admin')) {
            JToolBarHelper::preferences('com_projectfork');
        }
    }
}
