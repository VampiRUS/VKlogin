<?php
/**
 *
 * Author : Team Joomlaxi
 * Email  : shyam@joomlaxi.com
 * (C) www.joomlaxi.com
 *
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

// include joomla plugin framework
jimport( 'joomla.plugin.plugin' );
$jspath = JPATH_ROOT.DS.'components'.DS.'com_community';
include_once($jspath.DS.'libraries'.DS.'core.php');
include_once(JPATH_ROOT.DS.'components'.DS.'com_vklogin'.DS.'core.php');
jimport( 'joomla.user.helper' );

class plgCommunityVkontakte extends CApplications
{
	private $_pluginHandler;
	
	function plgCommunityVkontakte( &$subject, $params )
	{
		parent::__construct( $subject, $params );
	}
	
	
	function onUserRegisterFormDisplay(&$form) 
	{
		$session =& JFactory::getSession();
		if (!VKlogin::check_cookie($vk_cookie))
			return;
		$data = $session->get('vkdata',array());
		$step = $session->get('regstep',0);
		if (empty($_POST) && !empty($data)){
			$session->set('regstep',1);
			$pass = JUserHelper::genRandomPassword();
			$data['password'] = $pass;
			$data['password2'] = $pass;
			unset($data['vkid']);
		}
		if ($step == 1){
			$pass = JUserHelper::genRandomPassword();
			$data = array();
			$data['password'] = $pass;
			$data['password2'] = $pass;
		}
		$form = JString::str_ireplace('</form>','
		</form><a href="#" onclick="return showbox()">'.JText::_('I\'m already registred').'</a>
<div id="addition_form" style="display:none;">
<form action="'.JRoute::_( 'index.php?option=com_vklogin' ).'" method="post" class="form-validate">
<table cellpadding="0" cellspacing="0" border="0" width="100%" class="contentpane">
<tr>
	<td width="30%" height="40">
		<label id="usermsg" for="user">
			'.JText::_( 'Site User Name' ).':
		</label>
	</td>
  	<td>
  		<input type="text" name="username" id="user" size="40" value="" class="inputbox required" maxlength="50" /> *
  	</td>
</tr>
<tr>
	<td width="30%" height="40">
		<label id="passwordmsg" for="password">
			'.JText::_( 'Site Password' ).':
		</label>
	</td>
  	<td>
  		<input type="password" id="password" name="password" size="40" value="" class="inputbox required" maxlength="50" /> *
  	</td>
</tr>
</table>
<button class="button validate" type="submit">'.JText::_('Merge').'</button>
<input type="hidden" name="task" value="connect"/>
</form></div>
		',$form);
		$this->addJS(&$form, $data);
	}
	
	function addJS(&$form, $data){
		$params = json_encode($data);
		$form = JString::str_ireplace('<script type="text/javascript">', '<script type="text/javascript">
			vk_params='.
			$params.';
			function showbox(){
				document.getElementById("addition_form").style.display="block";
				return false;
			}
			jQuery(document).ready(function(){
				for (var key in vk_params){
					jQuery("#js"+key).val(vk_params[key]);
				}
				jQuery("#jspassword").parent().parent().hide();
				jQuery("#jspassword2").parent().parent().hide();
				jQuery("#jsusername").blur();
				/*var but = jQuery("<input class=\"button\" type=\"button\" id=\"addButton\" value=\"'.JText::_('CC NEXT').'\"/>");
				but.insertAfter("#btnSubmit");
				but.click(function(){
					jQuery(this).hide();
					jQuery("#btnSubmit").show();
					 window.setTimeout("jQuery(\"#btnSubmit\").click()",1000);
					});
				jQuery("#btnSubmit").hide();*/
				jQuery("#jsemail").keyup(function(){
					if((jQuery.trim(jQuery(this).val()) != ""))
					{
						if(cvalidate.validateElement(this))
							cvalidate.markValid(this);
						else
							cvalidate.markInvalid(this);
					}
				} );
			});', $form);
	}

	function onProfileCreate($cuser)
	{
		jimport('joomla.filesystem.file');
		jimport('joomla.utilities.utility');
		$session =& JFactory::getSession();
		if (!VKlogin::check_cookie($vk_cookie))
			return 0;
		$step = $session->get('regstep',0);
		if ($step == 3){
			$session->set('regstep',4);
			CFactory::load( 'helpers' , 'image' );
			$jsdata = $session->get('jsdata',array());
			if (empty($jsdata['photo_big']))
				return 0;
			$uid = $cuser->_userid;
			$config	=& JFactory::getConfig();
			$mainframe	=& JFactory::getApplication();
			$tmp_path = $mainframe->getCfg( 'tmp_path' );
			$data = file_get_contents($jsdata['photo_big']);
			if ($data){
				$tmp_name = $tmp_path.'/'.md5($jsdata['photo_big']);
				if (file_put_contents($tmp_name, $data) && cValidImage( $tmp_name )){
					$imageSize		= cImageGetSize( $tmp_name );
					//not configurable in jomsocial
					$imageMaxWidth	= 160;
					$file_type = 'image/jpg';
					$fileName		= JUtility::getHash( $tmp_name . time() );
					$hashFileName	= JString::substr( $fileName , 0 , 24 );
					$storage			= JPATH_ROOT . DS . 'images' . DS . 'avatar';
					$storageImage		= $storage . DS . $hashFileName . cImageTypeToExt( $file_type );
					$storageThumbnail	= $storage . DS . 'thumb_' . $hashFileName . cImageTypeToExt( $file_type );
					$image				= 'images/avatar/' . $hashFileName . cImageTypeToExt( $file_type );
					$thumbnail			= 'images/avatar/' . 'thumb_' . $hashFileName . cImageTypeToExt( $file_type );
					if (cImageResizePropotional( $tmp_name , $storageImage , $file_type , $imageMaxWidth )
					&& cImageCreateThumb( $tmp_name , $storageThumbnail , $file_type )
					 ){
						$u = &CFactory::getUser($uid);
						// a little bit hack
						$u->_avatar = $image;
						$u->_thumb = $thumbnail;
					}
				}
			}
		}
	}
}
