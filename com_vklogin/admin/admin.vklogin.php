<?php
defined( '_JEXEC' ) or die( 'Restricted access' );
jimport('joomla.application.helper');
jimport('joomla.filesystem.file');
require_once( JApplicationHelper::getPath( 'admin_html' ) );
JToolBarHelper::preferences('com_vklogin', '250');
$jspath = JPATH_ROOT.DS.'components'.DS.'com_community'.DS.'libraries'.DS.'core.php';
$cbpath = JPATH_ADMINISTRATOR.DS.'components/com_comprofiler/admin.comprofiler.php';
$task = JRequest::getCmd('task');
if ($task == 'postinstall'){
	include_once(dirname(__FILE__).'/install/helper.php');
	$errors = array();
	if (!VkloginInstallHelper::installPlugin('Authentication - Vkontakte', 'vkontakte', 'authentication')){
		$errors[] = JText::_('COM_VKLOGIN_AUTH_PLUGIN_INSTALLATION_ERROR');
	}
	if (!VkloginInstallHelper::installPlugin('User - Vkontakte', 'vkuser', 'user')){
		$errors[] = JText::_('COM_VKLOGIN_USER_PLUGIN_INSTALLATION_ERROR');
	}
	if (!VkloginInstallHelper::installModule('Войти ВКонтакте','mod_vklogin')){
		$errors[] = JText::_('COM_VKLOGIN_MOD_VKLOGIN_MODULE_INSTALLATION_ERROR');
	}
	VkloginInstallHelper::updateTable();
	foreach ($errors as $error){
		JError::raiseError( 500, $error);
	}
	$version = new JVersion;
	$joomla = $version->getShortVersion();
	if (empty($errors)){
		$mainframe = & JFactory::getApplication();
		$mainframe->redirect('index.php?option=com_vklogin',(substr($joomla,0,3) == '1.5')?JText::_('COM_VKLOGIN_COMPONENT_WAS_SUCCESSFULLY_INSTALLED'):'');
	}
	
}
if (JFile::exists($jspath)||JFile::exists($cbpath)){
	if ($task == 'save'){
		updateData();
	}
	JToolBarHelper::save();
	HTML_vklogin::showFields(getFields(JFile::exists($cbpath)));
}

function getFields($cb){
	if ($cb) {
		return getFieldsCB();
	} else {
		return getFieldsJS();
	}
}

function getFieldsJS() {
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

function getFieldsCB(){
	$db = JFactory::getDBO();
	$query = "SELECT f.fieldid as id,f.title as name,f.type,v.value FROM #__comprofiler_fields AS f "
	. 'LEFT JOIN '. $db->nameQuote( '#__vklogin' ) . ' AS v '
	. 'ON f.fieldid=v.id '
	. 'WHERE f.published=1 AND f.registration=1 '
	. "AND `table`='#__comprofiler' "
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
