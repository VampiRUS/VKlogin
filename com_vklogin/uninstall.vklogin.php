<?php
include_once(dirname(__FILE__).'/install/helper.php');
VkloginInstallHelper::uninstallPlugin('vkontakte', 'authentication');
VkloginInstallHelper::uninstallPlugin('vkuser', 'user');
VkloginInstallHelper::uninstallModule('mod_vklogin');
?>
