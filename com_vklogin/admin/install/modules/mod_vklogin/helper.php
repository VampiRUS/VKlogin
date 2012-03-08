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

class modVKLoginHelper
{
	public function getReturnURL($params, $type)
	{
		if($itemid =  $params->get($type))
		{
			$menu =& JSite::getMenu();
			$item = $menu->getItem($itemid);
			$url = JRoute::_($item->link.'&Itemid='.$itemid, false);
		}
		else
		{
			$uri = JFactory::getURI();
			$url = $uri->toString(array('path', 'query', 'fragment'));
		}
		return base64_encode($url);
	}

	public function getType()
	{
		$user = & JFactory::getUser();
		return (!$user->get('guest')) ? 'logout' : 'login';
	}
}
