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
     * The available buttons for rendering
     *
     * @var    array
     */
    protected $buttons;

    protected $modules;


    /**
     * Display the view
     *
     */
    public function display($tpl = null)
    {
        $this->components = PFapplicationHelper::getComponents();
        $this->user       = JFactory::getUser();
        $this->buttons    = $this->getButtons();
        $this->modules    = JFactory::getDocument()->loadRenderer('modules');

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


    protected function getButtons()
    {
        $components = PFapplicationHelper::getComponents();
        $buttons    = array();

        foreach ($components AS $component)
        {
            if (!PFApplicationHelper::enabled($component->element)) {
                continue;
            }

            $helper = JPATH_ADMINISTRATOR . '/components/' . $component->element . '/helpers/dashboard.php';
            $class  = str_replace('com_pf', 'PF', $component->element) . 'HelperDashboard';

            if (!JFile::exists($helper)) {
                continue;
            }

            JLoader::register($class, $helper);

            if (class_exists($class)) {
                if (in_array('getAdminButtons', get_class_methods($class))) {
                    $com_buttons = (array) call_user_func(array($class, 'getAdminButtons'));

                    $buttons[$component->element] = array();

                    foreach ($com_buttons AS $button)
                    {
                        $buttons[$component->element][] = $button;
                    }
                }
            }
        }

        return $buttons;
    }
}
