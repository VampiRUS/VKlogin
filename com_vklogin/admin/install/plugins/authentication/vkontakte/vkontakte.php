<?php
// Check to ensure this file is included in Joomla!
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.plugin.plugin' );

class plgAuthenticationVkontakte extends JPlugin
{
	
	public function onUserAuthenticate($credentials, $options, &$response)
	{
		return $this->onAuthenticate( $credentials, $options, $response );
	}
	
	public function onAuthenticate( $credentials, $options, &$response )
	{
		/*
		 * Here you would do whatever you need for an authentication routine with the credentials
		 *
		 * In this example the mixed variable $return would be set to false
		 * if the authentication routine fails or an integer userid of the authenticated
		 * user if the routine passes
		 */
		$success = 0;
		if ($credentials['username'] == 'VK_LOGIN' && $credentials['password'] == 'VK_PASSWORD')
		{
			$vkConfig = &JComponentHelper::getParams( 'com_vklogin' );
			$session =& JFactory::getSession();
			if (md5('expire='.$session->get('expire', 'error')
					.'mid='.$session->get('mid', 'error')
					.'secret='.$session->get('secret', 'error')
					.'sid='.$session->get('sid', 'error')
					.trim($vkConfig->get('secret'))) == $session->get('sig', 'error'))
			{
				$db = &JFactory::getDBO();
				$db->setQuery('SELECT u.* FROM #__users as u,#__vklogin_users as v WHERE v.`vkid`='
					.$db->Quote($session->get('mid', 'error')).' AND v.userid=u.id');
				if ($row = $db->loadObject())
				{
					$photo = $session->get('vk_photo','');
					if (preg_match('#http://cs\d+\.vkontakte\.ru/u\d+/e_[a-z0-9]+\.jpg|http://vkontakte\.ru/images/question_e\.gif#',$photo)){
						$db->setQuery('INSERT INTO #__vklogin_users (userid, photo,email_hash) VALUES ('
							.$db->Quote($row->id).', '.$db->Quote($photo).','.$db->Quote(md5($row->email)).')'.
							' ON DUPLICATE KEY UPDATE `photo`= VALUES(`photo`),`email_hash`=VALUES(`email_hash`)');
						$db->query();
					}
					$success = 1;
					if (JRequest::getBool('vkremember', false)){
						jimport('joomla.utilities.simplecrypt');
						jimport('joomla.utilities.utility');
			
						//Create the encryption key, apply extra hardening using the user agent string
						$key = JUtility::getHash(@$_SERVER['HTTP_USER_AGENT']);
						$credentials['vk_username'] = $row->username;
						$credentials['vk_password'] = $row->password;
						$crypt = new JSimpleCrypt($key);
						$rcookie = $crypt->encrypt(serialize($credentials));
						$lifetime = time() + 365*24*60*60;
						setcookie( JUtility::getHash('JLOGIN_REMEMBER'), $rcookie, $lifetime, '/' );
					}
					$response->email 	= $row->email;
					$response->fullname = $row->name;
					$response->password = $row->password;
					$response->username= $row->username;
				}
				
			} elseif (isset($credentials['vk_username']) && isset($credentials['vk_password'])){
				$db = &JFactory::getDBO();
				$db->setQuery('SELECT * FROM #__users WHERE username='.
					$db->Quote($credentials['vk_username'])." AND password=".
					$db->Quote($credentials['vk_password']));
				if ($row = $db->loadObject())
				{
					$success = 1;
					$response->email 	= $row->email;
					$response->fullname = $row->name;
					$response->password = $row->password;
					$response->username= $row->username;
				}
			}
		}
		if ($success)
		{
			$response->status			= JAUTHENTICATE_STATUS_SUCCESS;
			$response->error_message	= '';
			return true;
		}
		else
		{
			$response->status			= JAUTHENTICATE_STATUS_FAILURE;
			$response->error_message	= 'Could not authenticate';
			return false;
		}
	}
}

?>
