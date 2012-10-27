<?php
/**
 * @package      Projectfork
 * @subpackage   Milestones
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


jimport('joomla.application.component.controller');


class PFmilestonesController extends JControllerLegacy
{
    /**
     * The default view
     *
     * @var    string
     */
    protected $default_view = 'milestones';


    public function display($cachable = false, $urlparams = false)
    {
        PFmilestonesHelper::addSubmenu(JFactory::getApplication()->input->get('view', $this->default_view));
        parent::display();

        return $this;
    }
}
