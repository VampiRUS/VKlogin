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
					$toUpdate = $session->get('jsdata',array());
					$photo_rec = (preg_match('#http://cs\d+\.vk\.com/u\d+/e_[a-z0-9]+\.jpg|http://vk\.com/images/question_e\.gif#',
						$toUpdate['photo_rec']))?$toUpdate['photo_rec']:'';
					$photo_big = (preg_match('#http://cs\d+\.vk\.com/u\d+/a_[a-z0-9]+\.jpg|http://vk\.com/images/question_a\.gif#',
						$toUpdate['photo_big']))?$toUpdate['photo_big']:'';
					$photo_medium = (preg_match('#http://cs\d+\.vk\.com/u\d+/b_[a-z0-9]+\.jpg|http://vk\.com/images/question_b\.gif#',
						$toUpdate['photo_medium']))?$toUpdate['photo_medium']:'';
					$photo_medium_rec = (preg_match('#http://cs\d+\.vk\.com/u\d+/d_[a-z0-9]+\.jpg|http://vk\.com/images/question_d\.gif#',
						$toUpdate['photo_medium_rec']))?$toUpdate['photo_medium_rec']:'';
					$photo = (preg_match('#http://cs\d+\.vk\.com/u\d+/e_[a-z0-9]+\.jpg|http://vk\.com/images/question_e\.gif#',
						$toUpdate['photo']))?$toUpdate['photo']:'';
					$first_name = isset($toUpdate['first_name'])?$toUpdate['first_name']:"";
					$last_name = isset($toUpdate['last_name'])?$toUpdate['last_name']:"";
					$bdate = isset($toUpdate['bdate'])?$toUpdate['bdate']:"";
					$city = isset($toUpdate['city'])?$toUpdate['city']:"";
					$country = isset($toUpdate['country'])?$toUpdate['country']:"";
					$sex = isset($toUpdate['sex'])?$toUpdate['sex']:"";
					$nickname = isset($toUpdate['nickname'])?$toUpdate['nickname']:"";
					$timezone = isset($toUpdate['timezone'])?$toUpdate['timezone']:"";
					$home_phone = isset($toUpdate['home_phone'])?$toUpdate['home_phone']:"";
					$mobile_phone = isset($toUpdate['mobile_phone'])?$toUpdate['mobile_phone']:"";
					$university_name = isset($toUpdate['university_name'])?$toUpdate['university_name']:"";
					$faculty_name = isset($toUpdate['faculty_name'])?$toUpdate['faculty_name']:"";
					$graduation = isset($toUpdate['graduation'])?$toUpdate['graduation']:"";
					$domain = isset($toUpdate['domain'])?$toUpdate['domain']:"";

					$db->setQuery('INSERT INTO #__vklogin_users (userid, photo,
						email_hash,first_name, last_name, nickname, sex, bdate, city, country,
						timezone,photo_medium, photo_big, photo_rec, photo_medium_rec, home_phone,
						mobile_phone, university_name, faculty_name, graduation, domain ) VALUES ('
					.$db->Quote($row->id).', '.$db->Quote($photo).','.$db->Quote(md5($row->email)).','.
					$db->Quote($first_name).','.$db->Quote($last_name).','.$db->Quote($nikname).','.$db->Quote($sex).','.$db->Quote($bdate).','.
					$db->Quote($city).','.$db->Quote($country).','.
					$db->Quote($timezone).', '.$db->Quote($photo_medium).', '.$db->Quote($photo_big).', '.
					$db->Quote($photo_rec).','.$db->Quote($photo_medium_rec).','.$db->Quote($home_phone).','.
					$db->Quote($mobile_phone).','.$db->Quote($university_name).','.$db->Quote($faculty_name).','.
					$db->Quote($graduation).','.$db->Quote($domain).')'.
					' ON DUPLICATE KEY UPDATE `photo`= VALUES(`photo`),`email_hash`=VALUES(`email_hash`)
					,`first_name`=VALUES(`first_name`),`last_name`=VALUES(`last_name`),`bdate`=VALUES(`bdate`)
					,`city`=VALUES(`city`),`country`=VALUES(`country`),`sex`=VALUES(`sex`),`photo_big`=VALUES(`photo_big`)
					,`photo_medium`=VALUES(`photo_medium`),`photo_rec`=VALUES(`photo_medium_rec`),`photo_rec`=VALUES(`photo_medium_rec`)
					,`nickname`=VALUES(`nickname`),`timezone`=VALUES(`timezone`),`home_phone`=VALUES(`home_phone`)
					,`mobile_phone`=VALUES(`mobile_phone`),`university_name`=VALUES(`university_name`),`faculty_name`=VALUES(`faculty_name`)
					,`graduation`=VALUES(`graduation`),`domain`=VALUES(`domain`)');
					$db->query();
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
