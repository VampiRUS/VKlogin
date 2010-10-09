<?php
defined( '_JEXEC' ) or die( 'Restricted access' );
jimport('joomla.application.helper');

require_once( JApplicationHelper::getPath( 'admin_html' ) );
JToolBarHelper::preferences('com_vklogin', '200');
$jspath = JPATH_ROOT.DS.'components'.DS.'com_community';
if ($task == 'postinstall'){
	include_once(dirname(__FILE__).'/install/helper.php');
	$errors = array();
	if (!VkloginInstallHelper::installPlugin('Authentication - Vkontakte', 'vkontakte', 'authentication')){
		$errors[] = JText::_('Authentication - Vkontakte plugin installation error');
	}
	if (!VkloginInstallHelper::installPlugin('User - Vkontakte', 'vkuser', 'user')){
		$errors[] = JText::_('User - Vkontakte plugin installation error');
	}
	if (!VkloginInstallHelper::installModule('Войти ВКонтакте','mod_vklogin')){
		$errors[] = JText::_('mod_vklogin module installation error');
	}
	foreach ($errors as $error){
		JError::raiseError( 500, $error);
	}
	if (empty($errors)){
		$mainframe = & JFactory::getApplication();
		$mainframe->enqueueMessage(JText::_('The component was successfully installed'));
	}
}
if (file_exists($jspath.DS.'libraries'.DS.'core.php')){
	if ($task == 'save'){
		updateData();
	}
	JToolBarHelper::save();
	HTML_vklogin::showFields(getFields());
}

function getFields(){
	$db	= & JFactory::getDBO();
	$query	= 'SELECT f.id,f.name,f.type,v.value FROM ' . $db->nameQuote( '#__community_fields' ) . ' AS f '
				. 'LEFT JOIN '. $db->nameQuote( '#__vklogin' ) . ' AS v '
				. 'ON f.id=v.id '
				. 'WHERE f.published=1 '
				. 'ORDER BY ' . $db->nameQuote( 'ordering' );
	$db->setQuery( $query);
	$fields	= $db->loadObjectList();
	return $fields;
}

function updateData(){
	$db	=& JFactory::getDBO();
	$query = "INSERT INTO #__vklogin (`id`,`value`) VALUES ";
	$fields = JRequest::getVar('fields',array(),'post');
	$field = '';
	foreach ( $fields as $id=>$value){
		$field .= "( $id, ".$db->Quote($value)."),";
	}
	$query .= substr($field,0,-1)."  ON DUPLICATE KEY UPDATE `value`=VALUES(`value`)";
	if (!empty($fields)){
		$db->setQuery($query);
		$db->query();
		if($db->getErrorNum()) {
			JError::raiseError( 500, $db->stderr());
		}
	}
}

?>
