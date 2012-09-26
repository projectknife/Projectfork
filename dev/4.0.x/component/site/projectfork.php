<?php
/**
 * @package      Projectfork
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


// Include dependancies
jimport('joomla.application.component.controller');
jimport('joomla.application.component.helper');

// Load the backend language file into the frontend
JFactory::getLanguage()->load('com_projectfork', JPATH_ADMINISTRATOR);
JFactory::getLanguage()->load('com_projectfork', JPATH_ADMINISTRATOR . '/components/com_projectfork');

// Register classes to autoload
JLoader::register('ProjectforkHelper',            JPATH_ADMINISTRATOR . '/components/com_projectfork/helpers/projectfork.php');
JLoader::register('ProjectforkHelperAccess',      JPATH_ADMINISTRATOR . '/components/com_projectfork/helpers/access.php');
JLoader::register('ProjectforkHelperRoute',       JPATH_BASE . '/components/com_projectfork/helpers/route.php');
JLoader::register('ProjectforkHelperToolbar',     JPATH_BASE . '/components/com_projectfork/helpers/toolbar.php');
JLoader::register('ProjectforkHelperContextMenu', JPATH_BASE . '/components/com_projectfork/helpers/contextmenu.php');

// Add include paths
JHtml::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_projectfork/helpers/html');
JForm::addRulePath(JPATH_ADMINISTRATOR . '/components/com_projectfork/models/rules');

JLoader::registerPrefix('Projectfork', JPATH_SITE . '/components/com_projectfork/libraries/projectfork');


$controller = JControllerLegacy::getInstance('Projectfork');
$controller->execute(JRequest::getCmd('task'));
$controller->redirect();