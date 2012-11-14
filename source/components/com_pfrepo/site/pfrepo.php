<?php
/**
 * @package      Projectfork
 * @subpackage   Repository
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


// Include dependancies
jimport('joomla.application.component.controller');
jimport('joomla.application.component.helper');
jimport('projectfork.framework');

// Register classes to autoload
JHtml::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_pfrepo/helpers/html');

$controller = JControllerLegacy::getInstance('PFrepo');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();