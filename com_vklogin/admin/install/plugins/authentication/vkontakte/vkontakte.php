<?php
// Check to ensure this file is included in Joomla!
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.plugin.plugin' );

class plgAuthenticationVkontakte extends JPlugin
{
	function plgAuthenticationVkontakte(& $subject, $config)
	{
		parent::__construct($subject, $config);
	}
	
	function onAuthenticate( $credentials, $options, &$response )
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
				$db->setQuery('SELECT * FROM #__users WHERE activation='.$db->Quote($session->get('mid', 'error')));
				if ($row = $db->loadObject())
				{
					$photo = $session->get('vk_photo','');
					if (preg_match('#http://cs\d+\.vkontakte\.ru/u\d+/e_[a-z0-9]+\.jpg|http://vkontakte\.ru/images/question_e\.gif#',$photo)){
						$db->setQuery('INSERT INTO #__vklogin_users (userid, photo) VALUES ('.$db->Quote($row->id).', '.$db->Quote($photo).')'.
							' ON DUPLICATE KEY UPDATE `photo`= VALUES(`photo`)');
						$db->query();
					}
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
