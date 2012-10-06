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


/**
 * Milestone Form View Class for Projectfork component
 *
 */
class ProjectforkViewMilestoneForm extends JViewLegacy
{
    protected $form;
    protected $item;
    protected $return_page;
    protected $state;
    protected $toolbar;
    protected $pageclass_sfx;
    protected $params;


    public function display($tpl = null)
    {
        $this->state       = $this->get('State');
        $this->item        = $this->get('Item');
        $this->form        = $this->get('Form');
        $this->return_page = $this->get('ReturnPage');
        $this->params      = $this->state->params;
        $this->toolbar     = $this->getToolbar();

        // Permission check.
        if ($this->item->id <= 0) {
            $access = ProjectforkHelperAccess::getActions(NULL, 0, true);
            $authorised = $access->get('milestone.create');
        }
        else {
            $authorised = $this->item->params->get('access-edit');
        }

        if ($authorised !== true) {
            JError::raiseError(403, JText::_('JERROR_ALERTNOAUTHOR'));
            return false;
        }

        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
            JError::raiseWarning(500, implode("\n", $errors));
            return false;
        }

        //Escape strings for HTML output
        $this->pageclass_sfx = htmlspecialchars($this->state->params->get('pageclass_sfx'));

        // Prepare the document
        $this->_prepareDocument();

        // Display the view
        parent::display($tpl);
    }


    /**
     * Prepares the document
     *
     */
    protected function _prepareDocument()
    {
        $app     = JFactory::getApplication();
        $menu    = $app->getMenu()->getActive();
        $pathway = $app->getPathway();
        $title   = null;

        $def_title = JText::_('COM_PROJECTFORK_PAGE_' . ($this->item->id > 0 ? 'EDIT' : 'ADD') . '_MILESTONE');

        // Because the application sets a default page title,
        // we need to get it from the menu item itself
        if ($menu) {
            if (strpos($menu->link, 'view=milestones') !== false) {
                $this->params->def('page_heading', $def_title);
            }
            else {
                $this->params->def('page_heading', $this->params->get('page_title', $menu->title));
            }
        }
        else {
            $this->params->def('page_heading', $def_title);
        }

        $title = $this->params->def('page_title', $def_title);

        if ($app->getCfg('sitename_pagetitles', 0) == 1) {
            $title = JText::sprintf('JPAGETITLE', $app->getCfg('sitename'), $title);
        }
        elseif ($app->getCfg('sitename_pagetitles', 0) == 2) {
            $title = JText::sprintf('JPAGETITLE', $title, $app->getCfg('sitename'));
        }

        $this->document->setTitle($title);

        $pathway = $app->getPathWay();
        $pathway->addItem($title, '');

        if ($this->params->get('menu-meta_description')) {
            $this->document->setDescription($this->params->get('menu-meta_description'));
        }

        if ($this->params->get('menu-meta_keywords')) {
            $this->document->setMetadata('keywords', $this->params->get('menu-meta_keywords'));
        }

        if ($this->params->get('robots')) {
            $this->document->setMetadata('robots', $this->params->get('robots'));
        }
    }


    /**
     * Generates the toolbar for the top of the view
     *
     * @return    string    Toolbar with buttons
     */
    protected function getToolbar()
    {
        $options = array();

        if ($this->item->id) {
            $access = ProjectforkHelperAccess::getActions('milestone', $this->item->id);
        }
        else {
            $access = ProjectforkHelperAccess::getActions(null, 0, true);
        }

        $create_list = $access->get('tasklist.create');
        $create_task = $access->get('task.create');

        $options[] = array(
            'text' => 'JSAVE',
            'task' => $this->getName() . '.save');

        $options[] = array(
            'text' => 'COM_PROJECTFORK_ACTION_2NEW',
            'task' => $this->getName() . '.save2new');

        $options[] = array(
            'text' => 'COM_PROJECTFORK_ACTION_2COPY',
            'task' => $this->getName() . '.save2copy',
            'options' => array('access' => ($this->item->id > 0)));

        if ($create_list || $create_task) {
            $options[] = array('text' => 'divider');
        }

        $options[] = array(
            'text' => 'COM_PROJECTFORK_ACTION_2TASKLIST',
            'task' => $this->getName() . '.save2tasklist',
            'options' => array('access' => $create_list));

        $options[] = array(
            'text' => 'COM_PROJECTFORK_ACTION_2TASK',
            'task' => $this->getName() . '.save2task',
            'options' => array('access' => $create_task));

        ProjectforkHelperToolbar::dropdownButton($options, array('icon' => 'icon-white icon-ok'));

        ProjectforkHelperToolbar::button(
            'JCANCEL',
            $this->getName() . '.cancel',
            false,
            array('class' => '', 'icon' => '')
        );

        return ProjectforkHelperToolbar::render();
    }
}
