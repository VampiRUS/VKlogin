<?php 
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
		$vars = explode('&',$_COOKIE['vk_app_'.$appid]);
		$vk_cookie = array();
		foreach ($vars as $var)
		{
			list($param,$value) = explode('=',$var);
			$vk_cookie[$param] = $value;
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
				.trim($vkConfig->get( 'secret' ))) != $vk_cookie['sig'])
		{
			return false;
		}
		return true;
	}
}
?>
