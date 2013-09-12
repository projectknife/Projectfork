<?php
/**
 * @package      pkg_projectfork
 * @subpackage   com_pfusers
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2013 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


jimport('joomla.application.component.view');
jimport('projectfork.library');

/**
 * User Reference view class.
 *
 */
class PFusersViewUserRef extends JViewLegacy
{
    protected $items;


    /**
     * Generates a list of JSON items.
     *
     * @return    void
     */
    public function display($tpl = null)
    {
        $user   = JFactory::getUser();
        $access = JRequest::getUInt('filter_access');

        // No access if not logged in
        if ($user->id == 0) {
            JError::raiseError(403, JText::_('JERROR_ALERTNOAUTHOR'));
            return false;
        }

        // Check Access for non-admins
        if (!$user->authorise('core.admin')) {
            $allowed = PFAccessHelper::getGroupsByAccessLevel($access, true);
            $groups  = $user->getAuthorisedGroups();

            $can_access = false;

            foreach ($groups AS $group)
            {
                if (in_array($group, $allowed)) {
                    $can_access = true;
                    break;
                }
            }

            if (!$can_access) {
                JError::raiseError(403, JText::_('JERROR_ALERTNOAUTHOR'));
                return false;
            }
        }

        $this->items = $this->get('Items');

        // Check for errors.
		if (count($errors = $this->get('Errors'))) {
            JError::raiseWarning(500, implode("\n", $errors));
			return false;
		}

        parent::display($tpl);
    }
}
