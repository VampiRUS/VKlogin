<?php
defined('_JEXEC') or die('Restricted access');
$url = JURI::root() . 'administrator/index.php?option=com_vklogin&task=postinstall';
header('Location: ' . $url);
?>
