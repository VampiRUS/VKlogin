<?php
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.helper');

require_once(JPATH_COMPONENT.DS.'controller.php');

// Create the controller
$controller = new VkloginController();

// Perform the Request task
$task = JRequest::getVar('task', null, 'default', 'cmd');

switch ($task) {
	case 'connect': $task = 'connect';break;
	case 'reciver': $task = 'reciver';break;
	default: $task = 'default';
}
$controller->execute($task);

// Redirect if set by the controller
$controller->redirect();

?>
