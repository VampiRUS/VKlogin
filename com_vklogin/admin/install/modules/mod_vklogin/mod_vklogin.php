<?php
/**
* @version $Id$
* VKlogin Module
* @package VKlogin
*
* @Copyright (C) 2012 vampirus.ru. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
**/


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
$version = new JVersion;
$joomla = $version->getShortVersion();
if(substr($joomla,0,3) == '1.5'){
	$jVersion = '1.5';
} else {
	$jVersion = '1.7';
}
switch ($social) {
	case 2:
		$remindlink = JRoute::_( 'index.php?option=com_comprofiler&task=lostpassword' );
		$resetlink = JRoute::_( 'index.php?option=com_comprofiler&task=lostpassword' );
		$registerlink = JRoute::_( 'index.php?option=com_comprofiler&task=registers' );
		break;
	case 1:
		$remindlink = JRoute::_( 'index.php?option=com_user'.(($jVersion!='1.5')?'s':'').'&view=remind' );
		$resetlink = JRoute::_( 'index.php?option=com_user'.(($jVersion!='1.5')?'s':'').'&view=reset' );
		$registerlink = JRoute::_( 'index.php?option=com_community&view=register' );
		break;
	default:
		$remindlink = JRoute::_( 'index.php?option=com_user'.(($jVersion!='1.5')?'s':'').'&view=remind' );
		$resetlink = JRoute::_( 'index.php?option=com_user'.(($jVersion!='1.5')?'s':'').'&view=reset' );
		$registerlink = JRoute::_( 'index.php?option=com_user'.(($jVersion!='1.5')?'s&view=registration':'&view=register'));
}
if (!defined('VKAPI') && $type != 'logout'){
	define('VKAPI',1);
	$doc =& JFactory::getDocument();
	$doc->addCustomTag("<script src='http://userapi.com/js/api/openapi.js?49' type='text/javascript' charset='windows-1251'></script>");
}

$mod_id = $params->get('mod_id');
if ($type == 'logout' && $mod_id != ''){
	$document	= &JFactory::getDocument();
	$renderer	= $document->loadRenderer('module');
	$db		=& JFactory::getDBO();
	
	if ($jVersion=='1.5') {
	$query = 'SELECT id, title, module, position, params'
		. ' FROM #__modules AS m'
		. ' WHERE id='.intval($mod_id);
	} else {
	$query = 'SELECT id, title, module, position, content, showtitle, params'
	. ' FROM #__modules AS m'
	//. ' LEFT JOIN #__modules_menu AS mm ON mm.moduleid = m.id'
	. ' WHERE m.id = '.intval($mod_id);
	}
	$db->setQuery( $query );
	if ($mod = $db->loadObject()){
		$file					= $mod->module;
		$custom 				= substr( $file, 0, 4 ) == 'mod_' ?  0 : 1;
		$modu->user  	= $custom;
		// CHECK: custom module name is given by the title field, otherwise it's just 'om' ??
		$mod->name		= $custom ? $mod->title : substr( $file, 4 );
		$mod->style		= null;
		$mod->position	= strtolower($mod->position);
		echo $renderer->render($mod, array());
	}
} else {
	if ($type == 'login') {
		JHTML::_('script', 'script.js','modules/mod_vklogin/tmpl/');
		JHTML::_('script', 'punycode.js','modules/mod_vklogin/tmpl/');
	}
	require(JModuleHelper::getLayoutPath('mod_vklogin'));
}