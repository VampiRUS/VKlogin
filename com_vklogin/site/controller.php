<?php
defined('_JEXEC') or die( 'Restricted access' );

jimport( 'joomla.application.component.controller' );
jimport( 'joomla.application.application' );
jimport( 'joomla.user.helper' );
include_once(dirname(__FILE__).DS.'core.php');


class VkloginController extends JController
{
	private $jVersion = '1.5';
	
	public function __construct($config = array())
	{
		$version = new JVersion;
		$joomla = $version->getShortVersion();
		$this->jVersion =substr($joomla,0,3);
		parent::__construct($config);
	}
	
	private function isJ15()
	{
		return $this->jVersion=='1.5';
	}
	
	public function display()
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
		if (!$session->get('jsdata')){
			$session->set('jsdata',array_merge(array('domain'=>JRequest::getString('domain', '', 'post')), 
			JRequest::getVar('jomsocial', array(), 'post')));
		}
		$db = &JFactory::getDBO();
		$db->setQuery('SELECT userid FROM #__vklogin_users WHERE vkid='.$db->Quote($vk_cookie['mid']));
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
			$vkConfig = &JComponentHelper::getParams( 'com_vklogin' );
			//joomla 1.6. form
			$requestData = JRequest::getVar('jform', array(), 'post', 'array');
			$username = JRequest::getString('username', @$requestData['username'], 'post');
			$name = JRequest::getString('name', @$requestData['name'], 'post');
			$email = JRequest::getString('email', @$requestData['email'], 'post');
			if ($vkConfig->get('silentreg') && !$email && !$vkConfig->get( 'jomsocial' )){
				$email = JRequest::getString('domain', '', 'post').'@vk.com';
			} 
			if(!$this->isJ15()){
				JUser::getTable('User', 'JTable');
				$user = new JUser();
			} else {
				$user 	= clone(JFactory::getUser());
			}
			$authorize	=& JFactory::getACL();
			$data = array(
				'username'	=> $username,
				'name'		=> $name,
				'email'		=> $email,
				'vkid'=> $vk_cookie['mid']
			);
			if($this->isJ15()){
				$newUsertype = $usersConfig->get( 'new_usertype', 'Registered');
			} else {
				$newUsertype = $usersConfig->get( 'new_usertype', 2);
				$data['groups'][] = $newUsertype;
				$mainframe->setUserState('com_vklogin.registration.data', $data);
			}
			if ($vkConfig->get( 'jomsocial' )){
				$session->set('vkdata',$data);
				$session->set('regstep',0);
				if ($vkConfig->get( 'jomsocial' ) == 1)
					$mainframe->redirect(JRoute::_('index.php?option=com_community&view=register', false));
				else
					$mainframe->redirect(JRoute::_('index.php?option=com_comprofiler&task=registers', false));
				return;
			}
			if($this->isJ15()){
				if (!$user->bind( $data, 'usertype' )) {
					JError::raiseError( 500, $user->getError());
				}
			} else {
				if (!$user->bind( $data)) {
					JError::raiseError( 500, $user->getError());
				}
			}
			if($this->isJ15()){
				$user->set('usertype', $newUsertype);
				$user->set('gid', $authorize->get_group_id( '', $newUsertype, 'ARO' ));
			}
			// If there was an error with registration, set the message and display form
			if ( !$user->save() )
			{
				JError::raiseWarning('', JText::_( $user->getError()));
				$this->register();
				return false;
			} else {
				$db->setQuery('INSERT INTO #__vklogin_users (userid,vkid,email_hash) VALUES ('.$user->id.','
					.$db->Quote($vk_cookie['mid']).','.$db->Quote(md5($user->email)).')');
				$db->query();
				$this->login();
			}
			$this->setRedirect('index.php');
		}
	}	
	
	public function connect()
	{
		$mainframe	=& JFactory::getApplication();
		$db =& JFactory::getDBO();
		if(!VKlogin::check_cookie($vk_cookie)){
			JError::raiseError( 403, JText::_( 'Cookie error' ));
		}
		$username =  JRequest::getString('username', '', 'post');
		$password = JRequest::getString('password', '', 'post');
		$query = 'SELECT u.`id`,u.`email`, u.`password`, u.`block`,u.`usertype`'
			. ' FROM `#__users` as u'
			. ' WHERE username=' . $db->Quote($username)
			;
		$db->setQuery( $query );
		$result = $db->loadObject();
		if ( !$this->isJ15()){
			$checkType = !JAccess::check($result->id, 'core.admin');
		} else {
			$checkType = true;
		}
		if($result && $result->usertype != 'Super Administrator' && !$result->block && $checkType)
		{
			$parts	= explode( ':', $result->password );
			$crypt	= $parts[0];
			$salt	= @$parts[1];
			$testcrypt = JUserHelper::getCryptedPassword($password, $salt);
			if ($crypt == $testcrypt) {
				$db->setQuery( 'INSERT INTO #__vklogin_users (vkid,userid,email_hash) VALUES('
					.$db->Quote($vk_cookie['mid']).','.$result->id.','.$db->Quote(md5($result->email)).')' );
				$db->query();
				$error = $mainframe->login(array('username'=>$username, 'password'=>$password), array());
				if(!JError::isError($error))
				{	
					$mainframe->redirect( JRoute::_('index.php?option=com_user'.(($this->jVersion == '1.6')?'s':'')) );
				} else {
					$mainframe->redirect( JRoute::_('index.php?option=com_user'.(($this->jVersion == '1.6')?'s':'').'&view=login', false ));
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
	
	
	/**
	 * Prepares the registration form
	 * @return void
	 */
	protected function register()
	{
		$mainframe	=& JFactory::getApplication();
		$user 	=& JFactory::getUser();

		if ( $user->get('guest')) {
			if($this->isJ15()){
				JRequest::setVar('view', 'register');
				parent::display();
			} else {
				$document	= JFactory::getDocument();
				// Set the default view name and format from the Request.
				$vFormat = $document->getType();
				$view = $this->getView('register', $vFormat);
				$model = $this->getModel('Registration');
				$view->setModel($model, true);
				$view->setLayout('register16');
				// Push document object into the view.
				$view->assignRef('document', $document);
				$view->display();
			}
		} else {
				$mainframe->redirect(JRoute::_('index.php?option=com_user'.((!$this->isJ15())?'s':'').'&task=edit',false),JText::_('You are already registered.'));
		}
	}
	
	protected function login()
	{
		$mainframe	=& JFactory::getApplication();

		if ($return = JRequest::getVar('return', '', 'method', 'base64')) {
			$return = base64_decode($return);
			if (!JURI::isInternal($return)) {
				$return = '';
			}
		}

		$options = array();
		$options['remember'] = false;
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
					$return	= JRoute::_('index.php?option=com_user'.((!$this->isJ15())?'s':''));
			}

			$mainframe->redirect( $return );
		}
		else
		{
			// Facilitate third party login forms
			if ( ! $return ) {
				$return	= JRoute::_('index.php?option=com_user'.((!$this->isJ15())?'s':'').'&view=login', false);
			}

			// Redirect to a login form
			$mainframe->redirect( $return );
		}
	}
}

?>
