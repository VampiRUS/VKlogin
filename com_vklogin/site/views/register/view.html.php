<?php
/**
* @version $Id$
* VKlogin Component
* @package VKlogin
*
* @Copyright (C) 2012 vampirus.ru. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
**/

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.view');

class VkloginViewRegister extends JView
{
	protected $form;
	
	
	public function display($tpl = null)
	{
		$mainframe	=& JFactory::getApplication();
		$this->form		= $this->get('Form');

		// Check if registration is allowed
		$usersConfig = &JComponentHelper::getParams( 'com_users' );
		if (!$usersConfig->get( 'allowUserRegistration' )) {
			JError::raiseError( 403, JText::_( 'Access Forbidden' ));
			return;
		}

		$pathway  =& $mainframe->getPathway();
		$document =& JFactory::getDocument();
		$params	= &$mainframe->getParams();

		$params->set('page_title',	JText::_( 'COM_VKLOGIN_REGISTRATION' ));
		$document->setTitle( $params->get( 'page_title' ) );

		$pathway->addItem( JText::_( 'COM_VKLOGIN_NEW' ));

		// Load the form validation behavior

		$user =& JFactory::getUser();
		$this->assignRef('user', $user);
		$this->assignRef('params',		$params);
		parent::display($tpl);
	}	
	
}

?>
