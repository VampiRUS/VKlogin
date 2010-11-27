<?php
/**
* @version		$Id: mod_login.php 10381 2008-06-01 03:35:53Z pasamio $
* @package		Joomla
* @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

// Include the syndicate functions only once
require_once (dirname(__FILE__).DS.'helper.php');

$params->def('greeting', 1);
$type 	= modVKLoginHelper::getType();
$return	= modVKLoginHelper::getReturnURL($params, $type);
$vkConfig = &JComponentHelper::getParams( 'com_vklogin' );
$appid = trim($vkConfig->get( 'appid' ));
$social = $vkConfig->get( 'jomsocial' );

$lang->load('mod_login');

$user =& JFactory::getUser();
if ($user->id){
	$db = JFactory::getDBO();
	$db->setQuery('SELECT photo FROM #__vklogin_users WHERE userid='.$user->id);
	$user_photo = $db->loadResult();
} else {
	$user_photo = '';
}
switch ($social) {
	case 2:
		$remindlink = JRoute::_( 'index.php?option=com_comprofiler&task=lostpassword' );
		$resetlink = JRoute::_( 'index.php?option=com_comprofiler&task=lostpassword' );
		$registerlink = JRoute::_( 'index.php?option=com_comprofiler&task=registers' );
		break;
	case 1:
		$remindlink = JRoute::_( 'index.php?option=com_user&view=remind' );
		$resetlink = JRoute::_( 'index.php?option=com_user&view=reset' );
		$registerlink = JRoute::_( 'index.php?option=com_community&view=register' );
		break;
	default:
		$remindlink = JRoute::_( 'index.php?option=com_user&view=remind' );
		$resetlink = JRoute::_( 'index.php?option=com_user&view=reset' );
		$registerlink = JRoute::_( 'index.php?option=com_user&view=register' );
}
if (!defined('VKAPI')){
	define('VKAPI',1);
	$doc =& JFactory::getDocument();
	$doc->addCustomTag("<script src='http://userapi.com/js/api/openapi.js?18' type='text/javascript' charset='windows-1251'></script>");
}

require(JModuleHelper::getLayoutPath('mod_vklogin'));
