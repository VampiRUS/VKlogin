<?php

if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }
$_PLUGINS->registerFunction( 'onAfterFieldsFetch', 'updateFields','getvkloginregisterTab' );
include_once(JPATH_ROOT.DS.'components'.DS.'com_vklogin'.DS.'core.php');
jimport( 'joomla.user.helper' );
$_PLUGINS->registerFunction( 'onInputFieldHtmlRender', 'editForm','getvkloginregisterTab' );
$_PLUGINS->registerFunction( 'onBeforeUserRegistration', 'updateUser','getvkloginregisterTab' );
$_PLUGINS->registerFunction( 'onAfterUserRegistrationMailsSent', 'autoLogin','getvkloginregisterTab' );
class getvkloginregisterTab extends cbPluginHandler {
	/**
	 * Construnctor
	 */
	function getvkloginregisterTab() {
		$this->cbPluginHandler();
	}

	function updateFields(&$fields, &$user, $reason, $tabid, $fieldIdOrName){
		if ($reason == 'register'){
			$session =& JFactory::getSession();
			if (!VKlogin::check_cookie($vk_cookie))
			return;
			$data = $session->get('vkdata',array());
			$socialdata = $session->get('jsdata',array());
			$step = $session->get('regstep',-1);
			unset($data['activation']);
			if ($step == 0 && empty($_POST) && !empty($data)){
				$user->set('name',$data['name']);
				$user->set('username',$data['username']);
				$user->set('firstname',$socialdata['first_name']);
				$user->set('lastname',$socialdata['last_name']);
			}
		}
	}

	function editForm($htmlInput, $htmlIcons, $pointer, $field, $user, $reason, $tag, $type, $inputName, $value, $additional, $htmlDescription, $allValues, $displayFieldIcons, $oReq){
		$session =& JFactory::getSession();
		$step = $session->get('regstep',-1);
		if ($reason == 'register' && VKlogin::check_cookie($vk_cookie) && ($step == 0 || $step == 1 )){
			$session->set('regstep',1);
			$pass = JUserHelper::genRandomPassword();
			$append = 	'<script>'.
			'jQuery(document).ready(function($){
					$(".cbft_password").hide();
					$("#password__verify").val("'.$pass.'");
					$("#password").val("'.$pass.'");
				})'
			.'</script>';
		} else {
			$append = '';
		}
		return $htmlInput.$htmlIcons.$append;
	}
	
	function updateUser(&$user1, &$user2){
		$session =& JFactory::getSession();
		if (!VKlogin::check_cookie($vk_cookie) || $session->get('regstep',-1) != 1)
			return;
		$user1->set('confirmed',1);
		$user1->set('approved',1);
		$user1->set('block',0);
		$session->set('regstep',2);
	}
	

	function autoLogin(&$userComplete, &$userComplete, &$messagesToUser, $reg_confirmation, $reg_admin_approval, $bool){
		$session =& JFactory::getSession();
		if (!VKlogin::check_cookie($vk_cookie) || $session->get('regstep',-1) != 2)
			return;
		$db = &JFactory::getDBO();
		$db->setQuery('UPDATE #__users SET activation='.$db->Quote($vk_cookie['mid']).' WHERE id='.(int)$userComplete->id);
		$db->query();
		$session->set('regstep',3);
		$mainframe	=& JFactory::getApplication();
		$mainframe->redirect(JRoute::_('index.php?option=com_vklogin', false));
	}
}
?>