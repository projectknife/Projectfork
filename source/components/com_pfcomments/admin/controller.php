<?php
/**
 * @package      Projectfork
 * @subpackage   Comments
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


jimport('joomla.application.component.controller');


class PFcommentsController extends JControllerLegacy
{
    /**
     * The default view
     *
     * @var    string
     */
    protected $default_view = 'comments';


    public function display($cachable = false, $urlparams = false)
    {
        PFcommentsHelper::addSubmenu(JFactory::getApplication()->input->get('view', $this->default_view));
        parent::display();

        return $this;
    }
}
