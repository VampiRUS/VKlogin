<?php
/**
 * @version $Id$
 * Kunena Component
 * @package Kunena
 *
 * @Copyright (C) 2008 - 2011 Kunena Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.kunena.org
 *
 **/
//
// Dont allow direct linking
defined( '_JEXEC' ) or die('');

class KunenaAvatarVklogin extends KunenaAvatar
{

	public function __construct() {
		$this->priority = 50;
		$lang = JFactory::getLanguage();
		$lang->load('com_vklogin');
	}
	
	public function getEditURL() {}
	
	protected function _getURL($user, $sizex, $sizey)
	{
		$u = KunenaFactory::getUser($user);
		if ($sizex<100){ $field = 'photo_rec';}
		else if ($sizex<200){ $field = 'photo_medium_rec';}
		else $field = 'photo_big';
		$db = JFactory::getDBO();
		$db->setQuery('select '.$field.' from #__vklogin_users where userid='.$u->userid);
		$avatar = $db->loadResult();
		if (!$avatar){
			$kunena = KunenaIntegration::initialize ( 'avatar', 'kunena' );
			return $kunena->_getURL($user, $sizex, $sizey);
		}
		return $avatar;
	}
}
