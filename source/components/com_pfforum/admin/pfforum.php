<?php
/**
 * @package      Projectfork
 * @subpackage   Forum
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


// Access check
if (!JFactory::getUser()->authorise('core.manage', 'com_pfforum')) {
	return JError::raiseWarning(403, JText::_('JERROR_ALERTNOAUTHOR'));
}

// Include dependencies
jimport('joomla.application.component.controller');
jimport('joomla.application.component.helper');
jimport('projectfork.framework');

// Register classes to autoload
JLoader::register('PFforumHelper', JPATH_ADMINISTRATOR . '/components/com_pfforum/helpers/pfforum.php');
JHtml::addincludepath(JPATH_ADMINISTRATOR . '/components/com_pfforum/helpers/html');

$controller = JControllerLegacy::getInstance('PFforum');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();
