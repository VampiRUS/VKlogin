<?php // no direct access
defined('_JEXEC') or die('Restricted access'); ?>
<?php if($type == 'login') : ?>
<div id="vk_api_transport"></div>
<script type="text/javascript">
//<![CDATA[
VK.init({
	apiId: <?php echo $appid?>,
	nameTransportPath: "<?php echo $reciver?>"
});
function vk_login() {
    VK.Auth.login(vk_handler);
    return false;
}
function vk_handler(response) {
	if (response.session) {
		var start = document.cookie.indexOf('vk_app_'+VK._apiId);
		var end = document.cookie.indexOf(';', start);
		var vk_cookie = (end == -1)?document.cookie.substring(start):document.cookie.substring(start, end);
		start = vk_cookie.indexOf('mid') + 4;
		end = vk_cookie.indexOf('&', start);
		var mid = vk_cookie.substring(start, end);
		<?if ($social){
			echo "var code = 'var me =  API.getProfiles({uids:'+mid+',fields:\"nickname,sex,bdate,city,country,timezone,photo,photo_medium,photo_big,contacts,education\"})[0];'
		+'var country = API.getCountries({cids:me.country})[0].name;'
		+'var city = API.getCities({cids:me.city})[0].name;'
		+'return [me,country,city];';";
		} else {
			echo "var code = 'var me =  API.getProfiles({uids:'+mid+',fields:\"nickname,photo\"})[0];'
		+'return [me,false,false];';";
			
		}?>
		VK.Api.call('execute', {'code': code}, function(r){
			if(r.response) {
				document.forms.vklogin.name.value = r.response[0].last_name + ' ' + r.response[0].first_name;
				document.forms.vklogin.photo.value = r.response[0].photo;
				if (r.response[0].nickname == ''){
					document.forms.vklogin.username.value = <?php $login = $params->get('login');
					switch($login){
						case 1: echo 'r.response[0].last_name;';break;
						case 2: echo 'r.response[0].first_name+" "+r.response[0].last_name;';break;
						case 3: echo 'r.response[0].last_name+mid;';break;
						case 4: echo 'r.response[0].first_name+mid;';break;
						case 0:
						default: echo '"";';
					}
					?>
				} else {
					document.forms.vklogin.username.value = r.response[0].nickname;
				} 
				<?php if ($social) {?>
					document.forms.vklogin.elements[3].value = r.response[0].uid;
					document.forms.vklogin.elements[4].value = r.response[0].first_name;
					document.forms.vklogin.elements[5].value = r.response[0].last_name;
					document.forms.vklogin.elements[6].value = r.response[0].nickname;
					if (r.response[0].sex != 'undefined'){ 
						document.forms.vklogin.elements[7].value = r.response[0].sex;
					}
					if (r.response[0].bdate != 'undefined'){ 
						document.forms.vklogin.elements[8].value = r.response[0].bdate;
					}
					if (r.response[2] !== false){ 
						document.forms.vklogin.elements[9].value = r.response[2];
					}
					if (r.response[1] !== false){ 
						document.forms.vklogin.elements[10].value = r.response[1];
					}
					if (r.response[0].timezone != 'undefined'){ 
						document.forms.vklogin.elements[11].value = r.response[0].timezone;
					}
					if (r.response[0].photo != 'undefined'){ 
						document.forms.vklogin.elements[12].value = r.response[0].photo;
					}
					if (r.response[0].photo_medium != 'undefined'){ 
						document.forms.vklogin.elements[13].value = r.response[0].photo_medium;
					}
					if (r.response[0].photo_big != 'undefined'){ 
						document.forms.vklogin.elements[14].value = r.response[0].photo_big;
					}
					if (r.response[0].home_phone != 'undefined'){ 
						document.forms.vklogin.elements[15].value = r.response[0].home_phone;
					}
					if (r.response[0].mobile_phone != 'undefined'){ 
						document.forms.vklogin.elements[16].value = r.response[0].mobile_phone;
					}
					if (r.response[0].university_name != 'undefined'){ 
						document.forms.vklogin.elements[17].value = r.response[0].university_name;
					}
					if (r.response[0].faculty_name != 'undefined'){ 
						document.forms.vklogin.elements[18].value = r.response[0].faculty_name;
					}
					if (r.response[0].graduation != 'undefined'){ 
						document.forms.vklogin.elements[19].value = r.response[0].graduation;
					}
				<?php }?>
			}
		document.forms.vklogin.submit();
		}); 
    }
}
//]]>
</script>
<?php if(JPluginHelper::isEnabled('authentication', 'openid')) :
		$lang->load( 'plg_authentication_openid', JPATH_ADMINISTRATOR );
		$langScript = 	'var JLanguage = {};'.
						' JLanguage.WHAT_IS_OPENID = \''.JText::_( 'WHAT_IS_OPENID' ).'\';'.
						' JLanguage.LOGIN_WITH_OPENID = \''.JText::_( 'LOGIN_WITH_OPENID' ).'\';'.
						' JLanguage.NORMAL_LOGIN = \''.JText::_( 'NORMAL_LOGIN' ).'\';'.
						' var modlogin = 1;';
		$document = &JFactory::getDocument();
		$document->addScriptDeclaration( $langScript );
		JHTML::_('script', 'openid.js');
endif; ?>
<form action="<?php echo JRoute::_( 'index.php', true, $params->get('usesecure')); ?>" method="post" name="login" id="form-login" >
	<?php echo $params->get('pretext'); ?>
	<fieldset class="input">
	<p id="form-login-username">
		<label for="modlgn_username"><?php echo JText::_('Username') ?></label><br />
		<input id="modlgn_username" type="text" name="username" class="inputbox" alt="username" size="18" />
	</p>
	<p id="form-login-password">
		<label for="modlgn_passwd"><?php echo JText::_('Password') ?></label><br />
		<input id="modlgn_passwd" type="password" name="passwd" class="inputbox" size="18" alt="password" />
	</p>
	<?php if(JPluginHelper::isEnabled('system', 'remember')) : ?>
	<p id="form-login-remember">
		<label for="modlgn_remember"><?php echo JText::_('Remember me') ?></label>
		<input id="modlgn_remember" type="checkbox" name="remember" class="inputbox" value="yes" alt="Remember Me" />
	</p>
	<?php endif; ?>
	<input type="submit" name="Submit" class="button" value="<?php echo JText::_('LOGIN') ?>" />
	</fieldset>
	<input type="hidden" name="option" value="com_user" />
	<input type="hidden" name="task" value="login" />
	<input type="hidden" name="return" value="<?php echo $return; ?>" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
<a href="javascript:void(0);" onclick="vk_login();return false;"><img src="<?php echo JUri::base()?>/modules/mod_vklogin/tmpl/vk.jpg"/></a>
<form name="vklogin" method="post" action="<?php echo JRoute::_( 'index.php?option=com_vklogin'); ?>">
	<input type="hidden" name="name" value=""/>
	<input type="hidden" name="username" value=""/>
	<input type="hidden" name="option" value="com_vklogin"/>
	<?php if ($social){?>
		<input type="hidden" name="jomsocial[uid]" value=""/>
		<input type="hidden" name="jomsocial[first_name]" value=""/>
		<input type="hidden" name="jomsocial[last_name]" value=""/>
		<input type="hidden" name="jomsocial[nickname]" value=""/>
		<input type="hidden" name="jomsocial[sex]" value=""/>
		<input type="hidden" name="jomsocial[bdate]" value=""/>
		<input type="hidden" name="jomsocial[city]" value=""/>
		<input type="hidden" name="jomsocial[country]" value=""/>
		<input type="hidden" name="jomsocial[timezone]" value=""/>
		<input type="hidden" name="jomsocial[photo]" value=""/>
		<input type="hidden" name="jomsocial[photo_medium]" value=""/>
		<input type="hidden" name="jomsocial[photo_big]" value=""/>
		<input type="hidden" name="jomsocial[home_phone]" value=""/>
		<input type="hidden" name="jomsocial[mobile_phone]" value=""/>
		<input type="hidden" name="jomsocial[university_name]" value=""/>
		<input type="hidden" name="jomsocial[faculty_name]" value=""/>
		<input type="hidden" name="jomsocial[graduation]" value=""/>
	<?php }	?>
	<input type="hidden" name="photo" value=""/>
	<input type="hidden" name="return" value="<?php echo $return; ?>" />
</form>
	<ul>
		<li>
			<a href="<?php echo $resetlink ?>">
			<?php echo JText::_('FORGOT_YOUR_PASSWORD'); ?></a>
		</li>
		<li>
			<a href="<?php echo $remindlink ?>">
			<?php echo JText::_('FORGOT_YOUR_USERNAME'); ?></a>
		</li>
		<?php
		$usersConfig = &JComponentHelper::getParams( 'com_users' );
		if ($usersConfig->get('allowUserRegistration')) : ?>
		<li>
			<a href="<?php echo $registerlink ?>">
				<?php echo JText::_('REGISTER'); ?></a>
		</li>
		<?php endif; ?>
	</ul>
	<?php echo $params->get('posttext'); ?>
<?php else:
$name = explode(' ',$user->name);
if (count($name)>1){
	$name = $name[1];
} else {
	$name = $name[0];
}
?>
<?php if ($params->get('greeting')) : 
	echo JText::sprintf( 'HINAME', $name );
endif; ?>
<form method="post" action="<?php echo JRoute::_('index.php')?>" name="vklogin">
	<input type="submit" name="Submit" class="button" value="<?php echo JText::_( 'BUTTON_LOGOUT'); ?>" />
	<input type="hidden" value="com_user" name="option">
	<input type="hidden" value="logout" name="task">
	<input type="hidden" name="return" value="<?php echo $return; ?>" />
</form>
<?php endif; ?>
