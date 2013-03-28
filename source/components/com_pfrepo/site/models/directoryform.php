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


// Base this model on the backend version.
require_once JPATH_ADMINISTRATOR . '/components/com_pfrepo/models/directory.php';


/**
 * Repository Directory Form Model
 *
 */
class PFrepoModelDirectoryForm extends PFrepoModelDirectory
{
    /**
     * Method to get item data.
     *
     * @param     integer    $id       The id of the item.
     *
     * @return    mixed      $value    Item data object on success, false on failure.
     */
    public function getItem($id = null)
    {
        $item = parent::getItem($id);

        if ($item === false) return false;

        // Compute selected asset permissions.
        $asset = 'com_pfrepo.directory' . ($item->id > 0 ? '.' . (int) $item->id : '');
        $user  = JFactory::getUser();
        $uid   = (int) $user->get('id');

        $can_edit       = $user->authorise('core.edit', $asset);
        $can_edit_own   = $user->authorise('core.edit.own', $asset);
        $can_edit_own   = ($can_edit_own && $uid == $item->created_by && $uid > 0);
        $can_edit_state = $user->authorise('core.edit.state', $asset);

        $item->params->set('access-edit',   ($can_edit || $can_edit_own));
        $item->params->set('access-change', $can_edit_state);

        return $item;
    }


    /**
     * Get the return URL.
     *
     * @return    string    The return URL.
     */
    public function getReturnPage()
    {
        return base64_encode($this->getState('return_page'));
    }


    /**
     * Method to auto-populate the model state.
     * Note. Calling getState in this method will result in recursion.
     *
     * @return    void    
     */
    protected function populateState()
    {
        $app     = JFactory::getApplication();
        $pk      = JRequest::getUInt('id');
        $parent  = JRequest::getUInt('filter_parent_id');
        $option  = JRequest::getVar('option');
        $return  = JRequest::getVar('return', null, 'default', 'base64');
        $project = PFApplicationHelper::getActiveProjectId();

        // Set primary key
        $this->setState($this->getName() . '.id', $pk);

        // Set return page
        $this->setState('return_page', base64_decode($return));

        // Set params
        $params = $app->getParams();
        $this->setState('params', $params);

        // Set layout
        $this->setState('layout', JRequest::getCmd('layout'));

        // Set parent id
        $this->setState($this->getName() . '.parent_id', $parent);

        // Set project
        $this->setState($this->getName() . '.project', $project);
    }
}
