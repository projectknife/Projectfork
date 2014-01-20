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
 * Component main controller
 *
 * @see    JController
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
     * Displays the current view
     *
     * @param     boolean    $cachable    If true, the view output will be cached  (Not Used!)
     * @param     array      $urlparams   An array of safe url parameters and their variable types (Not Used!)
     *
     * @return    JController             A JController object to support chaining.
     */
    public function display($cachable = false, $urlparams = false)
    {
        // Load CSS and JS assets
        JHtml::_('pfhtml.style.bootstrap');
        JHtml::_('pfhtml.style.projectfork');

        JHtml::_('pfhtml.script.jQuery');
        JHtml::_('pfhtml.script.bootstrap');
        JHtml::_('pfhtml.script.projectfork');

        JHtml::_('behavior.tooltip');

        $view      = JRequest::getCmd('view');
        $id        = JRequest::getUInt('id');
        $urlparams = array(
            'id'               => 'INT',
            'cid'              => 'ARRAY',
            'limit'            => 'INT',
            'limitstart'       => 'INT',
            'showall'          => 'INT',
            'return'           => 'BASE64',
            'filter'           => 'STRING',
            'filter_order'     => 'CMD',
            'filter_order_Dir' => 'CMD',
            'filter_search'    => 'STRING',
            'filter_published' => 'CMD'
        );

        // Inject default view if not set
        if (empty($view)) {
            JRequest::setVar('view', $this->default_view);
            $view = $this->default_view;
        }


        if ($view == $this->default_view) {
            $parent_id = JRequest::getUInt('filter_parent_id');
            $project   = PFApplicationHelper::getActiveProjectId('filter_project');

            if ($parent_id && $project === "") {
                $this->setRedirect(JRoute::_(PFrepoHelperRoute::getRepositoryRoute()));
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
                        $this->setRedirect(JRoute::_(PFrepoHelperRoute::getRepositoryRoute($project, $dir->id, $dir->path)));
                        return $this;
                    }
                }
            }
        }

        // Check for directory edit form.
        if ($view == 'directoryform' && !$this->checkEditId('com_pfrepo.edit.directoryform', $id)) {
            // Somehow the person just went to the form - we don't allow that.
            return JError::raiseError(403, JText::sprintf('JLIB_APPLICATION_ERROR_UNHELD_ID', $id));
        }

        // Check for note edit form.
        if ($view == 'noteform' && !$this->checkEditId('com_pfrepo.edit.noteform', $id)) {
            // Somehow the person just went to the form - we don't allow that.
            return JError::raiseError(403, JText::sprintf('JLIB_APPLICATION_ERROR_UNHELD_ID', $id));
        }

        // Check for file edit form.
        if ($view == 'fileform' && !$this->checkEditId('com_pfrepo.edit.fileform', $id)) {
            // Somehow the person just went to the form - we don't allow that.
            return JError::raiseError(403, JText::sprintf('JLIB_APPLICATION_ERROR_UNHELD_ID', $id));
        }

        // Display the view
        parent::display($cachable, $urlparams);

        // Return own instance for chaining
        return $this;
    }
}