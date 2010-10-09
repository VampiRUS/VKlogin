<?php
defined('_JEXEC') or die( 'Restricted access' );

jimport( 'joomla.application.component.controller' );
jimport( 'joomla.application.application' );
jimport( 'joomla.user.helper' );
include_once(dirname(__FILE__).DS.'core.php');


class VkloginController extends JController
{
	function display()
	{
		$mainframe	=& JFactory::getApplication();
		if (!VKlogin::check_cookie($vk_cookie)){
			JError::raiseError( 403, JText::_( 'Cookie error' ));
		}
		$user = &JFactory::getUser();
		$session =& JFactory::getSession();
		$session->set('mid',$vk_cookie['mid']);
		$session->set('expire',$vk_cookie['expire']);
		$session->set('secret',$vk_cookie['secret']);
		$session->set('sid',$vk_cookie['sid']);
		$session->set('sig',$vk_cookie['sig']);
		$session->set('vk_photo',JRequest::getString('photo', '', 'post'));
		$db = &JFactory::getDBO();
		$db->setQuery('SELECT id FROM #__users WHERE activation='.$db->Quote($vk_cookie['mid']));
		$db->query();
		if ($db->getNumRows())
		{
			$this->login();
		} else 
		{
			// Check if registration is allowed
			$usersConfig = &JComponentHelper::getParams( 'com_users' );
			if (!$usersConfig->get( 'allowUserRegistration' )) {
				JError::raiseError( 403, JText::_( 'Registration not allowed' ));
				return;
			}
			$username = JRequest::getString('username', '', 'post');
			$name = JRequest::getString('name', '', 'post');
			$email = JRequest::getString('email', '', 'post');
			$user 		= clone(JFactory::getUser());
			$authorize	=& JFactory::getACL();
			$newUsertype = $usersConfig->get( 'new_usertype' );
			if (!$newUsertype) {
				$newUsertype = 'Registered';
			}
			$data = array(
				'username'	=> $username,
				'name'		=> $name,
				'email'		=> $email,
				'activation'=> $vk_cookie['mid']
			);
			$vkConfig = &JComponentHelper::getParams( 'com_vklogin' );
			if ($vkConfig->get( 'jomsocial' )){
				//если jomsocial
				$session->set('vkdata',$data);
				$session->set('jsdata',JRequest::getVar('jomsocial', array(), 'post'));
				$session->set('regstep',0);
				if ($vkConfig->get( 'jomsocial' ) == 1)
					$mainframe->redirect(JRoute::_('index.php?option=com_community&view=register', false));
				else
					$mainframe->redirect(JRoute::_('index.php?option=com_comprofiler&task=registers', false));
				return;
			}
			if (!$user->bind( $data, 'usertype' )) {
				JError::raiseError( 500, $user->getError());
			}
			$user->set('usertype', $newUsertype);
			$user->set('gid', $authorize->get_group_id( '', $newUsertype, 'ARO' ));
					// If there was an error with registration, set the message and display form
			if ( !$user->save() )
			{
				JError::raiseWarning('', JText::_( $user->getError()));
				$this->register();
				return false;
			} else {
				$this->login();
			}
			$this->setRedirect('index.php');
		}
	}	
	
	function connect()
	{
		global $mainframe;
		$db =& JFactory::getDBO();
		if(!VKlogin::check_cookie($vk_cookie)){
			JError::raiseError( 403, JText::_( 'Cookie error' ));
		}
		$username =  JRequest::getString('username', '', 'post');
		$password = JRequest::getString('password', '', 'post');
		$query = 'SELECT `id`, `password`, `block`,`usertype`'
			. ' FROM `#__users`'
			. ' WHERE username=' . $db->Quote($username)
			;
		$db->setQuery( $query );
		$result = $db->loadObject();
		if($result && $result->usertype != 'Super Administrator' && !$result->block)
		{
			$parts	= explode( ':', $result->password );
			$crypt	= $parts[0];
			$salt	= @$parts[1];
			$testcrypt = JUserHelper::getCryptedPassword($password, $salt);
			if ($crypt == $testcrypt) {
				$db->setQuery( 'UPDATE #__users SET activation='.$db->Quote($vk_cookie['mid']).' WHERE id='.$result->id );
				$db->query();
				$error = $mainframe->login(array('username'=>$username, 'password'=>$password), array());
				if(!JError::isError($error))
				{
					$mainframe->redirect( JRoute::_('index.php?option=com_user') );
				} else {
					$mainframe->redirect( JRoute::_('index.php?option=com_user&view=login', false ));
				}
			} else {
				JError::raiseWarning('', JText::_('Invalid password'));
				$this->register();
			}
		} else {
			JError::raiseWarning('', JText::_('User does not exist'));
			$this->register();
		}
	}
	
	function reciver()
	{
		JRequest::setVar('view', 'reciver');
		parent::display();
	}
	
	/**
	 * Prepares the registration form
	 * @return void
	 */
	function register()
	{
		global $mainframe;
		$user 	=& JFactory::getUser();

		if ( $user->get('guest')) {
			JRequest::setVar('view', 'register');
		} else {
			$mainframe->redirect(JRoute::_('index.php?option=com_user&task=edit',false),JText::_('You are already registered.'));
		}

		parent::display();
	}
	
	function login()
	{
		global $mainframe;

		if ($return = JRequest::getVar('return', '', 'method', 'base64')) {
			$return = base64_decode($return);
			if (!JURI::isInternal($return)) {
				$return = '';
			}
		}

		$options = array();
		$options['remember'] = JRequest::getBool('remember', false);
		$options['return'] = $return;

		$credentials = array();
		$credentials['username'] = 'VK_LOGIN';
		$credentials['password'] = 'VK_PASSWORD';

		//preform the login action
		$error = $mainframe->login($credentials, $options);
		if(!JError::isError($error))
		{
			// Redirect if the return url is not registration or login
			if ( ! $return ) {
				$return	= JRoute::_('index.php?option=com_user');
			}

			$mainframe->redirect( $return );
		}
		else
		{
			// Facilitate third party login forms
			if ( ! $return ) {
				$return	= JRoute::_('index.php?option=com_user&view=login', false);
			}

			// Redirect to a login form
			$mainframe->redirect( $return );
		}
	}
}

?>
