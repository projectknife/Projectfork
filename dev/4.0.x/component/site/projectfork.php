<?php
// no direct access
defined('_JEXEC') or die;

// Include dependancies
jimport('joomla.application.component.controller');

$controller = JController::getInstance('Projectfork');
$controller->execute(JRequest::getCmd('task'));
$controller->redirect();
?>