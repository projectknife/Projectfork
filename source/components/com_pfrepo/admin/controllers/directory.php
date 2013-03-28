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


jimport('joomla.application.component.controllerform');


/**
 * Repository Directory Controller Class
 *
 */
class PFrepoControllerDirectory extends JControllerForm
{
    /**
     * The URL view list variable.
     *
     * @var    string
     */
    protected $view_list = 'repository';


    /**
     * Method to check if you can add a new record.
     *
     * @param     array      $data    An array of input data.
     *
     * @return    boolean
     */
    protected function allowAdd($data = array())
    {
        $user = JFactory::getUser();

        $project = JArrayHelper::getValue($data, 'project_id', JRequest::getInt('filter_project'), 'int');
        $parent  = JArrayHelper::getValue($data, 'parent_id', JRequest::getInt('filter_parent_id'), 'int');

        if (!$project || $parent <= 1) return false;

        // Validate access on the target parent directory
        if (!$user->authorise('core.create', 'com_pfrepo.directory.'. $parent)) {
            return false;
        }

        return parent::allowAdd($data);
    }


    /**
     * Method override to check if you can edit an existing record.
     *
     * @param     array      $data    An array of input data.
     * @param     string     $key     The name of the key for the primary key.
     *
     * @return    boolean
     */
    protected function allowEdit($data = array(), $key = 'id')
    {
        $user  = JFactory::getUser();
        $uid   = $user->get('id');
        $id    = (int) isset($data[$key]) ? $data[$key] : 0;
        $owner = (int) isset($data['created_by']) ? $data['created_by'] : 0;

        // Check general edit permission first.
        if ($user->authorise('core.edit', 'com_pfrepo.directory.' . $id)) {
            return true;
        }

        // Fallback on edit.own.
        if ($user->authorise('core.edit.own', 'com_pfrepo.directory.' . $id)) {
            // Now test the owner is the user.
            if (!$owner && $id) {
                $record = $this->getModel()->getItem($id);

                if (empty($record)) return false;

                $owner = $record->created_by;
            }

            if ($owner == $uid) return true;
        }

        // Fall back to the component permissions.
        return parent::allowEdit($data, $key);
    }


    /**
     * Gets the URL arguments to append to an item redirect.
     *
     * @param     integer    $id         The primary key id for the item.
     * @param     string     $url_var    The name of the URL variable for the id.
     *
     * @return    string                 The arguments to append to the redirect URL.
     */
    protected function getRedirectToItemAppend($id = null, $url_var = 'id')
    {
        $tmpl    = JRequest::getCmd('tmpl');
        $layout  = JRequest::getCmd('layout', 'edit');
        $project = JRequest::getUint('filter_project');
        $parent  = JRequest::getUint('filter_parent_id');
        $append  = '';

        // Setup redirect info.
        if ($project) $append .= '&filter_project=' . $project;
        if ($parent)  $append .= '&filter_parent_id=' . $parent;
        if ($id)      $append .= '&' . $url_var . '=' . $id;
        if ($tmpl)    $append .= '&tmpl=' . $tmpl;
        if ($layout)  $append .= '&layout=' . $layout;

        return $append;
    }


    /**
     * Gets the URL arguments to append to a list redirect.
     *
     * @return    string    The arguments to append to the redirect URL.
     */
    protected function getRedirectToListAppend()
    {
        $tmpl    = JRequest::getCmd('tmpl');
        $project = JRequest::getUint('filter_project');
        $parent  = JRequest::getUint('filter_parent_id');
        $append  = '';

        // Setup redirect info.
        if ($project) $append .= '&filter_project=' . $project;
        if ($parent)  $append .= '&filter_parent_id=' . $parent;
        if ($tmpl)    $append .= '&tmpl=' . $tmpl;

        return $append;
    }
}
