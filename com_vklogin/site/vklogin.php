<?php
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.controller');

// Create the controller
$version = new JVersion;
$joomla = $version->getShortVersion();
if(substr($joomla,0,3) == '1.6'){
	$controller = JController::getInstance('VKlogin');
} else {
	require_once(JPATH_COMPONENT.DS.'controller.php');
	$controller = new VkloginController();
}

// Perform the Request task
$task = JRequest::getVar('task', null, 'default', 'cmd');

switch ($task) {
	case 'connect': $task = 'connect';break;
	default: $task = 'default';
}
$controller->execute($task);

// Redirect if set by the controller
$controller->redirect();

?>
