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


/**
 * Group users view class.
 *
 */
class PFusersViewGroupUsers extends JViewLegacy
{
    protected $items;


    /**
     * Generates a list of JSON items.
     *
     * @return    void
     */
    public function display($tpl = null)
    {
        $this->items = $this->get('Items');

        $user = JFactory::getUser();

        if (!$user->authorise('core.admin', 'com_pfprojects') && !$user->authorise('core.manage', 'com_pfprojects')) {
            JError::raiseError(403, JText::_('JERROR_ALERTNOAUTHOR'));
            return false;
        }

        // Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseWarning(500, implode("\n", $errors));
			return false;
		}

        parent::display($tpl);
    }
}
