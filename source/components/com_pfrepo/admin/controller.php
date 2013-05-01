<?php
/**
 * @package      pkg_projectfork
 * @subpackage   com_pfrepo
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2013 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


jimport('joomla.application.component.controller');


/**
 * Repository Main Controller Class
 *
 */
class PFrepoController extends JControllerLegacy
{
    /**
     * The default view
     *
     * @var    string
     */
    protected $default_view = 'repository';


    /**
     * Method to display a view.
     *
     * @param     boolean        If true, the view output will be cached
     * @param     array          An array of safe url parameters
     *
     * @return    jcontroller    This object to support chaining.
     */
    public function display($cachable = false, $urlparams = false)
    {
        $view    = JRequest::getCmd('view', $this->default_view);
        $layout  = JRequest::getCmd('layout');
        $id      = JRequest::getUint('id');

        // Check form edit access
        if ($layout == 'edit' && !$this->checkEditId('com_pfrepo.edit.' . $view, $id)) {
            $this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_UNHELD_ID', $id));
            $this->setMessage($this->getError(), 'error');
            $this->setRedirect(JRoute::_('index.php?option=com_pfrepo&view=' . $this->default_view, false));

            return false;
        }

        // Add the sub-menu
        PFrepoHelper::addSubmenu($view);

        // Display the view
        parent::display($cachable, $urlparams);

        return $this;
    }
}
