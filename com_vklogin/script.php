<?php
defined('_JEXEC') or die('Restricted access');
 
class com_vkloginInstallerScript{
	
	function install($parent) 
	{
		//$parent is the class calling this method
		$parent->getParent()->setRedirectURL('index.php?option=com_vklogin&task=postinstall');
	}
 
	/**
	 * method to uninstall the component
	 *
	 * @return void
	*/
	function uninstall($parent) 
	{
		include_once(dirname(__FILE__).'/install/helper.php');
		VkloginInstallHelper::uninstallPlugin('vkontakte', 'authentication');
		VkloginInstallHelper::uninstallPlugin('vkuser', 'user');
		VkloginInstallHelper::uninstallModule('mod_vklogin');
	}
}
?>