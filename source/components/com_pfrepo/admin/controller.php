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

        // Inject default view if not set
        if (empty($view)) {
            JRequest::setVar('view', $this->default_view);
            $view = $this->default_view;
        }

        if ($view == $this->default_view) {
            $parent_id = JRequest::getUInt('filter_parent_id');
            $project   = PFApplicationHelper::getActiveProjectId('filter_project');

            if ($parent_id && $project === "") {
                $this->setRedirect('index.php?option=com_pfrepo&view=' . $this->default_view);
                return $this;
            }
            elseif ($parent_id > 1 && $project > 0) {
                // Check if the folder belongs to the project
                $db    = JFactory::getDbo();
                $query = $db->getQuery(true);

                $query->select('project_id')
                      ->from('#__pf_repo_dirs')
                      ->where('id = ' . (int) $parent_id);

                $db->setQuery($query);
                $pid = $db->loadResult();

                if ($pid != $project) {
                    // No match, redirect to the project root dir
                    $query->clear();
                    $query->select('id, path')
                          ->from('#__pf_repo_dirs')
                          ->where('parent_id = 1')
                          ->where('project_id = ' . (int) $project);

                    $db->setQuery($query, 0, 1);
                    $dir = $db->loadObject();

                    if ($dir) {
                        $this->setRedirect('index.php?option=com_pfrepo&view=' . $this->default_view . '&filter_project=' . $project . '&filter_parent_id=' . $dir->id);
                        return $this;
                    }
                }
            }
        }

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
