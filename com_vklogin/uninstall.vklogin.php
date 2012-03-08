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
include_once(dirname(__FILE__).'/install/helper.php');
VkloginInstallHelper::uninstallPlugin('vkontakte', 'authentication');
VkloginInstallHelper::uninstallPlugin('vkuser', 'user');
VkloginInstallHelper::uninstallModule('mod_vklogin');
?>
