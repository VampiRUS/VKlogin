<?php
/**
 * JComments - Joomla Comment System
 *
 * Enable avatar support for JComments
 *
 * This plugin support loading user avatars from:
 *
 * - Agora Forum
 * - CommunityBuilder
 * - Contacts
 * - FireBoard Forum
 * - Gravatar
 * - IDoBlog
 * - K2
 * - Kunena Forum
 * - JooBB Forum
 * - JomSocial
 * - iJoomla Magazine
 * - phpBB3 - Blogomunity p8pbb bridge
 * - phpBB3 - JFusion bridge
 * - phpBB3 - RokBridge
 * - vBulletin
 *
 * @version 1.0
 * @package JComments
 * @author Sergey M. Litvinov (smart@joomlatune.ru)
 * @copyright (C) 2006-2009 by Sergey M. Litvinov (http://www.joomlatune.ru)
 * @license GNU/GPL: http://www.gnu.org/copyleft/gpl.html
 *
 * If you fork this to create your own project, 
 * please make a reference to JComments someplace in your code 
 * and provide a link to http://www.joomlatune.ru
 **/

// ensure this file is being included by a parent file
defined('_JEXEC') or die('Direct Access to this location is not allowed.');

class plgJCommentsVkAvatar extends JPlugin{
	
	function plgJCommentsVkAvatar(& $subject, $config) {
		parent::__construct($subject, $config);
	}
	
	function onPrepareAvatar(&$comments){
		$comments = array();
		$comments[0] =& $comment;
		$this->onPrepareAvatars($comments);
	}
	
	function onPrepareAvatars(&$comments){
		$users = array();
		for ($i=0,$n=count($comments); $i < $n; $i++) {
			if ($comments[$i]->userid != 0) {
				$users[] = $comments[$i]->userid;
			}
		}
		$users = array_unique($users);
		if (count($users)) {
			$query = 'SELECT userid, photo as avatar'
				. ' FROM #__vklogin_users'
				. ' WHERE userid in (' . implode(',', $users)  . ')'
				;
			$db = JFactory::getDBO();
			$db->setQuery($query);
			$avatars = $db->loadObjectList('userid');
			unset($users);
		} else {
			$avatars = array();
		}
		for ($i=0,$n=count($comments); $i < $n; $i++) {
			$userid = (int) $comments[$i]->userid;
			if (isset($avatars[$userid]) && $avatars[$userid]->avatar != '') {
				$comments[$i]->avatar =  '<img src="'.$avatars[$userid]->avatar.'" border="0" alt=""/><img align="bottom" style="width:16px;height:15px;margin-left:-16px;" src="'.JURI::root().'administrator/components/com_vklogin/img/vk.png" />';
			} else {
				$comments[$i]->avatar = '';
			}
		}
		unset($avatars);
	}
}
?>
