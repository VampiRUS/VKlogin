<?php
/**
* @version $Id$
* VKlogin Component
* @package VKlogin
*
* @Copyright (C) 2012 vampirus.ru. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
**/
defined('_JEXEC') or die('Restricted access');
$url = JURI::root() . 'administrator/index.php?option=com_vklogin&task=postinstall';
header('Location: ' . $url);
?>
