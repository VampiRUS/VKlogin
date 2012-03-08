<?php
/**
* @version $Id$
* vkuser plugin
* @package VKlogin
*
* @Copyright (C) 2012 vampirus.ru. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
**/

defined('_JEXEC') or die( 'Restricted access' );

jimport('joomla.plugin.plugin');

class plgUserVKuser extends JPlugin {

	public function onUserAfterDelete($user, $succes, $msg)
	{
		return $this->onAfterDeleteUser($user, $succes, $msg);
	}
	
	public function onAfterDeleteUser($user, $succes, $msg)
	{
		$db = JFactory::getDBO();
		$db->setQuery('DELETE FROM #__vklogin_users where userid='.$user['id']);
		$db->query();
	}


	public function onUserLogout($user, $options = array())
	{
		$this->onLogoutUser($user);
	}
	
	public function onLogoutUser($user)
	{
		$vkConfig = &JComponentHelper::getParams( 'com_vklogin' );
		$appid = trim($vkConfig->get( 'appid' ));
		setcookie ('vk_app_'.$appid, "", time() - 3600);
		unset($_COOKIE['vk_app_'.$appid]); 
		return true;
	}
	
}
