<?php
/**
 * @package      pkg_projectfork
 * @subpackage   com_pfprojects
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2013 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


JLoader::register('PFprojectsControllerProject', JPATH_ADMINISTRATOR . '/components/com_pfprojects/controllers/project.json.php');


/**
 * Projectfork Project Form Controller
 *
 */
class PFprojectsControllerForm extends PFprojectsControllerProject
{
    /**
     * Method to check if you can add a new record.
     *
     * @param     array      $data    An array of input data.
     *
     * @return    boolean
     */
    protected function allowAdd($data = array())
    {
        return JFactory::getUser()->authorise('core.create', 'com_pfprojects');
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
        // Get form input
        $id = (int) isset($data[$key]) ? $data[$key] : 0;

        $user  = JFactory::getUser();
        $uid   = $user->get('id');
        $asset = 'com_pfprojects.project.' . $id;

        // Check if the user has viewing access when not a super admin
        if (!$user->authorise('core.admin')) {
            $db    = JFactory::getDbo();
            $query = $db->getQuery(true);

            $query->select('access')
                  ->from('#__pf_projects')
                  ->where('id = ' . $id);

            $db->setQuery($query);
            $lvl = $db->loadResult();

            if (!in_array($lvl, $user->getAuthorisedViewLevels())) {
                return false;
            }
        }

        // Check edit permission first
        if ($user->authorise('core.edit', $asset)) {
            return true;
        }

        // Fall back on edit.own.
        // First test if the permission is available.
        if (!$user->authorise('core.edit.own', $asset)) {
            return false;
        }

        // Now test the owner is the user.
        $owner = (int) isset($data['created_by']) ? (int) $data['created_by'] : 0;

        if (!$owner && $id) {
            // Need to do a lookup from the model.
            $record = $this->getModel()->getItem($id);

            if (empty($record)) return false;

            $owner = $record->created_by;
        }

        // If the owner matches 'me' then do the test.
        return ($owner == $uid && $uid > 0);
    }
}
