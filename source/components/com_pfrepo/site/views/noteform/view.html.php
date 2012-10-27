<?php
/**
 * @package      Projectfork
 * @subpackage   Repository
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


jimport('joomla.application.component.view');


/**
 * Note Form View Class for Projectfork component
 *
 */
class PFrepoViewNoteForm extends JViewLegacy
{
    protected $form;
    protected $item;
    protected $return_page;
    protected $state;
    protected $toolbar;


    public function display($tpl = null)
    {
        // Initialise variables.
        $app  = JFactory::getApplication();
        $user = JFactory::getUser();

        // Get model data.
        $this->state       = $this->get('State');
        $this->item        = $this->get('Item');
        $this->form        = $this->get('Form');
        $this->return_page = $this->get('ReturnPage');
        $this->toolbar     = $this->getToolbar();


        // Permission check.
        if (empty($this->item->id)) {
            $access = PFrepoHelper::getActions('note');
            $authorised = $access->get('core.create');
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

        // Create a shortcut to the parameters.
        $params = &$this->state->params;


        //Escape strings for HTML output
        $this->pageclass_sfx = htmlspecialchars($params->get('pageclass_sfx'));


        $this->params = $params;
        $this->user   = $user;


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
        $menus   = $app->getMenu();
        $pathway = $app->getPathway();
        $title   = null;

        // Because the application sets a default page title,
        // we need to get it from the menu item itself
        $menu = $menus->getActive();

        if ($menu) {
            $this->params->def('page_heading', $this->params->get('page_title', $menu->title));
        }
        else {
            $this->params->def('page_heading', JText::_('COM_PROJECTFORK_FORM_EDIT_NOTE'));
        }

        $title = $this->params->def('page_title', JText::_('COM_PROJECTFORK_FORM_EDIT_NOTE'));

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

        $options[] = array(
            'text' => 'JSAVE',
            'task' => $this->getName() . '.save');

        $options[] = array(
            'text' => 'COM_PROJECTFORK_ACTION_2NEW',
            'task' => $this->getName() . '.save2new');

        PFToolbar::dropdownButton($options, array('icon' => 'icon-white icon-ok'));

        PFToolbar::button(
            'JCANCEL',
            $this->getName() . '.cancel',
            false,
            array('class' => '', 'icon' => '')
        );

        return PFToolbar::render();
    }
}
