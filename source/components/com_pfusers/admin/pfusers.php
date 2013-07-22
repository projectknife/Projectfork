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


// Access check
if (!JFactory::getUser()->authorise('core.manage', 'com_pfusers')) {
	return JError::raiseWarning(403, JText::_('JERROR_ALERTNOAUTHOR'));
}

$format = JRequest::getVar('format', 'html');

if ($format == 'html') {
    JFactory::getApplication()->redirect('index.php?option=com_users&view=users');
    jexit();
}

// Include dependencies
jimport('joomla.application.component.controller');
jimport('joomla.application.component.helper');

$controller = JControllerLegacy::getInstance('PFusers');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();

