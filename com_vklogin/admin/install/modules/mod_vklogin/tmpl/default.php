<?php // no direct access
defined('_JEXEC') or die('Restricted access'); ?>
<?php if($type == 'login') : ?>
<script type="text/javascript">
//<![CDATA[
<?php if(!defined('VKINIT')):
	define('VKINIT',1);
?>
VK.init({
	apiId: <?php echo $appid?>
});
<?php endif; ?>
function getUsername(data){
	return <?php $login = $params->get('login_format');
	switch($login){
		case 1: echo 'data.last_name;';break;
		case 2: echo 'data.first_name+" "+data.last_name;';break;
		case 3: echo 'data.id+"_"+data.last_name;';break;
		case 4: echo 'data.id+"_"+data.first_name;';break;
		case 5: echo 'data.screen_name;';break;
		case 0:
		default: echo '"";';
	}
	?>
}
//]]>
</script>
<style type="text/css">
#vk_auth iframe{
	width:<?php echo $params->get('width','200');?>px!important;
}
</style>
<?php echo $params->get('pretext'); ?>
<?php if ($params->get('standart_login')) : ?>
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
	<fieldset class="input">
	<p id="form-login-username">
		<label for="modlgn_username"><?php echo JText::_('MOD_VKLOGIN_USERNAME') ?></label><br />
		<input id="modlgn_username" type="text" name="username" class="inputbox" alt="username" size="18" />
	</p>
	<p id="form-login-password">
		<label for="modlgn_passwd"><?php echo JText::_('MOD_VKLOGIN_PASSWORD') ?></label><br />
		<input id="modlgn_passwd" type="password" name="<?php echo (($jVersion!='1.5')?'password':'passwd');?>" class="inputbox" size="18" alt="password" />
	</p>
	<?php if(JPluginHelper::isEnabled('system', 'remember')) : ?>
	<p id="form-login-remember">
		<label for="modlgn_remember"><?php echo JText::_('MOD_VKLOGIN_REMEMBER_ME') ?></label>
		<input id="modlgn_remember" type="checkbox" name="remember" class="inputbox"
		 value="yes" alt="<?php echo JText::_('MOD_VKLOGIN_REMEMBER_ME') ?>" onclick="toggleRemember(this)"/>
	</p>
	<?php endif; ?>
	<input type="submit" name="Submit" class="button" value="<?php echo JText::_('MOD_VKLOGIN_BUTTON_LOGIN') ?>" />
	</fieldset>
	<input type="hidden" name="option" value="com_user<?php echo (($jVersion!='1.5')?'s':'');?>" />
	<input type="hidden" name="task" value="<?php echo (($jVersion!='1.5')?'user.':'');?>login" />
	<input type="hidden" name="return" value="<?php echo $return; ?>" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
<?php endif; ?>
<?php if ($params->get('widget')) : ?>
<div id="vk_auth"></div>
<script type="text/javascript">
 VK.Widgets.Auth('vk_auth', {onAuth:vk_handler,width:'<?php echo $params->get('width','200');?>px'});
</script>
<?php else: ?>
<a href="javascript:void(0);" onclick="vk_login();return false;"><img src="<?php echo JUri::base()?>/modules/mod_vklogin/tmpl/vk.jpg"/></a>
<?php endif;?>
<form name="vklogin" method="post" action="<?php echo JRoute::_( 'index.php?option=com_vklogin'); ?>">
	<input type="hidden" name="name" value=""/>
	<input type="hidden" name="username" value=""/>
	<input type="hidden" name="option" value="com_vklogin"/>
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
	<input type="hidden" name="jomsocial[photo_rec]" value=""/>
	<input type="hidden" name="jomsocial[photo_medium_rec]" value=""/>
	<input type="hidden" name="domain" value=""/>
	<input type="hidden" name="vkremember" value="" id="vkremember"/>
	<input type="hidden" name="photo_rec" value=""/>
	<input type="hidden" name="return" value="<?php echo $return; ?>" />
</form>
<?php if ($params->get('standart_login')) : ?>
	<ul>
		<li>
			<a href="<?php echo $resetlink ?>">
			<?php echo JText::_('MOD_VKLOGIN_FORGOT_YOUR_PASSWORD'); ?></a>
		</li>
		<li>
			<a href="<?php echo $remindlink ?>">
			<?php echo JText::_('MOD_VKLOGIN_FORGOT_YOUR_USERNAME'); ?></a>
		</li>
		<?php
		$usersConfig = &JComponentHelper::getParams( 'com_users' );
		if ($usersConfig->get('allowUserRegistration')) : ?>
		<li>
			<a href="<?php echo $registerlink ?>">
				<?php echo JText::_('MOD_VKLOGIN_REGISTER'); ?></a>
		</li>
		<?php endif; ?>
	</ul>
<?php endif;?>
	<?php echo $params->get('posttext'); ?>
<?php else:
 if ($params->get('greeting')) : 
	if ($user_photo){
		echo '<img src="'.$user_photo.'" style="float:left;margin-right:5px;"/>';
	}
	echo JText::sprintf( 'MOD_VKLOGIN_HINAME', $user->name );
endif; ?>
<form method="post" action="<?php echo JRoute::_('index.php')?>" name="vklogin">
	<input type="submit" name="Submit" class="button" value="<?php echo JText::_( 'MOD_VKLOGIN_BUTTON_LOGOUT'); ?>" />
	<input type="hidden" value="com_user<?php echo (($jVersion!='1.5')?'s':'');?>" name="option">
	<input type="hidden" value="<?php echo (($jVersion!='1.5')?'user.':'');?>logout" name="task">
	<input type="hidden" name="return" value="<?php echo $return; ?>" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
<?php endif; ?>
