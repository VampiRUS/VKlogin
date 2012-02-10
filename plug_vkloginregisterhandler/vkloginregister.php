<?php

if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }
$_PLUGINS->registerFunction( 'onAfterFieldsFetch', 'updateFields','getvkloginregisterTab' );
include_once(JPATH_ROOT.DS.'components'.DS.'com_vklogin'.DS.'core.php');
jimport( 'joomla.user.helper' );
$_PLUGINS->registerFunction( 'onInputFieldHtmlRender', 'editForm','getvkloginregisterTab' );
$_PLUGINS->registerFunction( 'onBeforeUserRegistration', 'updateUser','getvkloginregisterTab' );
$_PLUGINS->registerFunction( 'onAfterUserRegistrationMailsSent', 'autoLogin','getvkloginregisterTab' );
class getvkloginregisterTab extends cbPluginHandler {

	private $vkfields = null;
	
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
			unset($data['vkid']);
			if ($step == 0 && empty($_POST) && !empty($data)){
				$user->set('name',$data['name']);
				$user->set('username',$data['username']);
				$user->set('firstname',$socialdata['first_name']);
				$user->set('lastname',$socialdata['last_name']);
				if (!$this->vkfields){
					$db = JFactory::getDBO();
					$db->setQuery("SELECT f.fieldid,f.name,f.type,v.value FROM #__comprofiler_fields  AS f "
					. 'LEFT JOIN '. $db->nameQuote( '#__vklogin' ) . ' AS v '
					. 'ON f.fieldid=v.id '
					. "WHERE f.published=1 AND f.registration=1 AND `table`='#__comprofiler' " 
					. 'ORDER BY ' . $db->nameQuote( 'ordering' ));
					$this->vkfields = $db->loadObjectList('fieldid');
				}
				foreach ($this->vkfields as $field) {
					$vkvalue = $socialdata[$field->value];
					if (isset($fields[$field->fieldid])&&($fields[$field->fieldid]->type=='date'||$fields[$field->fieldid]->type=='datetime')&&$field->value=='bdate'){
						$date = explode('.',$socialdata[$field->value]);
						$vkvalue = (!empty($date[2])?$date[2]:'').'-'.(!empty($date[1])?$date[1]:'').'-'.(!empty($date[0])?$date[0]:''); 
					}
					if (isset($fields[$field->fieldid])&&($fields[$field->fieldid]->type=='select'||$fields[$field->fieldid]->type=='radio')&&$field->value=='sex'){
						$db = JFactory::getDBO();
						$db->setQuery( "SELECT fieldtitle AS `value`, fieldtitle AS `text`, concat('cbf',fieldvalueid) AS id FROM #__comprofiler_field_values"		// id needed for the labels
										. "\n WHERE fieldid = " . (int) $field->fieldid
										. "\n ORDER BY ordering" );
						$allValues		=	$db->loadObjectList();
					$vkvalue = $allValues[($socialdata[$field->value]-1)]->value;
					}
					$user->set($field->name,$vkvalue);
				}
			}
		}
	}

	function editForm($htmlInput, $htmlIcons, $pointer, $field, $user, $reason, $tag, $type, $inputName, $value, $additional, $htmlDescription, $allValues, $displayFieldIcons, $oReq){
		$session =& JFactory::getSession();
		$step = $session->get('regstep',-1);
		if ($reason == 'register' && VKlogin::check_cookie($vk_cookie) && ($step == 0 || $step == 1 )){
			if($field->name == 'password__verify'){
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
		global $ueConfig;
		$session =& JFactory::getSession();
		if (!VKlogin::check_cookie($vk_cookie) || $session->get('regstep',-1) != 2)
			return;
		$db = &JFactory::getDBO();
		$db->setQuery('INSERT INTO #__vklogin_users (userid, vkid) VALUES ('
		.(int)$userComplete->id.','.$db->Quote($vk_cookie['mid']).')');
		$db->query();
		ob_start();
		var_dump($db);
		$session->set('regstep',3);
		$jsdata = $session->get('jsdata',array());
		$config	=& JFactory::getConfig();
		$mainframe	=& JFactory::getApplication();
		$tmp_path = $mainframe->getCfg( 'tmp_path' );
		if (!empty($jsdata['photo_big'])&&($data = file_get_contents($jsdata['photo_big']))) {
			$tmp_name = $tmp_path.'/'.md5($jsdata['photo_big']);
			if (file_put_contents($tmp_name, $data)){
				cbimport( 'cb.imgtoolbox' );
				$imgToolBox						=	new imgToolBox();
				$imgToolBox->_conversiontype	=	$ueConfig['conversiontype'];
				$imgToolBox->_IM_path			=	$ueConfig['im_path'];
				$imgToolBox->_NETPBM_path		=	$ueConfig['netpbm_path'];
				$imgToolBox->_maxsize			=	$ueConfig['avatarSize'];
				$imgToolBox->_maxwidth			=	$ueConfig['avatarWidth'];
				$imgToolBox->_maxheight			=	$ueConfig['avatarHeight'];
				$imgToolBox->_thumbwidth		=	$ueConfig['thumbWidth'];
				$imgToolBox->_thumbheight		=	$ueConfig['thumbHeight'];
				$imgToolBox->_debug				=	0;
				$allwaysResize					=	( isset( $ueConfig['avatarResizeAlways'] ) ? $ueConfig['avatarResizeAlways'] : 1 );

				$newFileName		=	$imgToolBox->processImage( array('name'=>'img.jpg','tmp_name'=>$tmp_name), uniqid($userComplete->id."_"),JPATH_ROOT. '/images/comprofiler/', 0, 0, 2, $allwaysResize );
				if ($newFileName) {
					$db = JFactory::getDBO();
					$db->setQuery("UPDATE #__comprofiler SET avatar=" . $db->Quote($newFileName) . ", avatarapproved=1 WHERE id=" . (int) $userComplete->id);
					$db->query();
				}
				@unlink($tmp_path);
			}
		}
		$mainframe->redirect(JRoute::_('index.php?option=com_vklogin', false));
	}
}
?>
