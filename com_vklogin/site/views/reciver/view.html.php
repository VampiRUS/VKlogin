<?php
// Check to ensure this file is included in Joomla!
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.view');

class VkloginViewReciver extends JView
{

	function display($tpl = null)
	{
		ob_clean();
		parent::display($tpl);
		exit();
	}	
	
}

?>
