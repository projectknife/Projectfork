<?php
/**
 * @package      Projectfork
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


jimport('joomla.application.component.controller');


class ProjectforkController extends JController
{
    /**
     * The default view
     *
     * @var    string    
     */
    protected $default_view = 'dashboard';


    public function display($cachable = false, $urlparams = false)
    {
        JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

        // Load the submenu.
        ProjectforkHelper::addSubmenu(JRequest::getCmd('view', 'dashboard'));

        parent::display();

        return $this;
    }
}
