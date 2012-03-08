<?php 
/**
* @version $Id$
* VKlogin Component
* @package VKlogin
*
* @Copyright (C) 2012 vampirus.ru. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
**/

defined('_JEXEC') or die( 'Restricted access' );
class VKlogin{
	static function check_cookie(&$vk_cookie){
		$mainframe	=& JFactory::getApplication();
		$vkConfig = &JComponentHelper::getParams( 'com_vklogin' );
		$appid = trim($vkConfig->get( 'appid' ));
		$user = &JFactory::getUser();
		$vk_cookie = array();
		if (!$user->guest || !isset($_COOKIE['vk_app_'.$appid]))
		{
			return false;
		}
		$vars = explode('&',urldecode($_COOKIE['vk_app_'.$appid]));
		$vk_cookie = array();
		foreach ($vars as $var)
		{
			list($param,$value) = explode('=',$var);
			$vk_cookie[$param] = $value;
		}
		$secret = $vkConfig->get( 'secret' );
		$hash = JRequest::getVar('hash');
		if ($hash == md5($appid.$vk_cookie['mid'].$secret)){
			$vk_cookie['sig'] = md5('expire='
				.$vk_cookie['expire']
				.'mid='.$vk_cookie['mid']
				.'secret='.$vk_cookie['secret']
				.'sid='.$vk_cookie['sid']
				.trim($secret));
			$path = JURI::getInstance()->getPath();
			//firefox
			setcookie('vk_app_'.$appid,'expire='
				.$vk_cookie['expire']
				.'&mid='.$vk_cookie['mid']
				.'&secret='.$vk_cookie['secret']
				.'&sid='.$vk_cookie['sid']
				.'&sig='.$vk_cookie['sig'],
				time()+86400,
				$path
			);
			//opera
			setcookie('vk_app_'.$appid,'expire='
				.$vk_cookie['expire']
				.'&mid='.$vk_cookie['mid']
				.'&secret='.$vk_cookie['secret']
				.'&sid='.$vk_cookie['sid']
				.'&sig='.$vk_cookie['sig'],
				time()+86400,
				'/'
			);
		}
		if (!isset($vk_cookie['expire']) 
			|| !isset($vk_cookie['mid']) 
			|| !isset($vk_cookie['secret']) 
			|| !isset($vk_cookie['sid']) 
			|| !isset($vk_cookie['sig'])
			|| md5('expire='
				.$vk_cookie['expire']
				.'mid='.$vk_cookie['mid']
				.'secret='.$vk_cookie['secret']
				.'sid='.$vk_cookie['sid']
				.trim($secret)) != $vk_cookie['sig'])
		{
			return false;
		}
		return true;
	}
}
?>
