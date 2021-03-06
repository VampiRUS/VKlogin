<?php
/**
* @version $Id$
* VKlogin Component
* @package VKlogin
*
* @Copyright (C) 2012 vampirus.ru. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
**/

defined('_JEXEC') or die( 'Restricted access' );
jimport( 'joomla.filesystem.folder' );
jimport( 'joomla.filesystem.file' );

class VkloginInstallHelper{
	
	static function installPlugin($name, $element, $folder){
		$success = true;
		$version = new JVersion;
		$joomla = $version->getShortVersion();
		$db = & JFactory::getDBO();
		$pluginsDstPath = JPATH_ROOT.DS.'plugins'.DS.$folder;
		if(substr($joomla,0,3) != '1.5'){
			$pluginsDstPath .= DS.$element;
		}
		if (JFile::exists($pluginsDstPath.DS.$element.'.xml')){
			self::uninstallPlugin($element, $folder);
		}
		if(substr($joomla,0,3) != '1.5'){
			$pluginsQuery = "INSERT INTO `#__extensions` (`type`,`name`, `element`, `folder`, `enabled`,`protected` ) VALUES ('plugin','%s', '%s', '%s', 1, 1 );";
		} else {
			$pluginsQuery = "INSERT INTO `#__plugins` (`name`, `element`, `folder`, `published` ) VALUES ('%s', '%s', '%s', 1 );"; 
		}
		$pluginsSrcPath = JPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_vklogin'.DS.'install'.DS.'plugins'.DS.$folder.DS.$element;
		$db->setQuery(sprintf($pluginsQuery, $name, $element, $folder));
		$db->query();
		if($db->getErrorNum()) {
			JError::raiseError( 500, $db->stderr());
			$success = false;
		}
		if(substr($joomla,0,3) == '1.5'){
			$files = JFolder::files($pluginsSrcPath);
			foreach( $files as $file){
				if (!JFile::move($pluginsSrcPath.DS.$file, $pluginsDstPath.DS.$file)){
					$success = false;
				}
			}
		} else {
			$success = JFolder::move($pluginsSrcPath, $pluginsDstPath);
		}
		return $success;
	}
	
	
	static function uninstallPlugin($element, $folder){
		$db = & JFactory::getDBO();
		$version = new JVersion;
		$joomla = $version->getShortVersion();
		if(substr($joomla,0,3) != '1.5'){
			$pluginsQuery = 'DELETE FROM `#__extensions` WHERE `folder`='.$db->Quote($folder).' AND `element`='.$db->Quote($element);
		} else {
			$pluginsQuery = 'DELETE FROM `#__plugins` WHERE `folder`='.$db->Quote($folder).' AND `element`='.$db->Quote($element); 
		}
		$db->setQuery($pluginsQuery);
		$db->query();
		if(substr($joomla,0,3) == '1.5'){
			$files[] = JPATH_ROOT.DS.'plugins'.DS.$folder.DS.$element.'.php';
			$files[] = JPATH_ROOT.DS.'plugins'.DS.$folder.DS.$element.'.xml';
			//TODO to get files from xml
			JFile::delete($files);
		} else {
			JFolder::delete(JPATH_ROOT.DS.'plugins'.DS.$folder.DS.$element);
		}
	}

	static function installModule( $title, $module ){
		$success = true;
		$db = & JFactory::getDBO();
		$modulesDstPath = JPATH_ROOT.DS.'modules';
		$modulesSrcPath = JPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_vklogin'.DS.'install'.DS.'modules';
		$version = new JVersion;
		$joomla = $version->getShortVersion();
		if(substr($joomla,0,3) != '1.5'){
			$modulesQuery = 'SELECT * FROM `#__extensions` WHERE `name`="%2$s"';
		} else {
			$modulesQuery = "SELECT * FROM `#__modules` WHERE `title`='%s' AND `module`='%s'";
		}
		$db->setQuery(sprintf($modulesQuery, $title, $module));
		$db->query();
		if (!$db->getNumRows()){
			if(substr($joomla,0,3) != '1.5'){
				$modulesQuery = "INSERT INTO `#__extensions` (`type`,`name`, `element`, `folder`, `enabled`,`protected` ) VALUES ('module','mod_vklogin', 'mod_vklogin', '', 1, 1 );";
				$db->setQuery($modulesQuery);
				$db->query();
			}
			$modulesQuery =  "INSERT INTO `#__modules` (`title`, `module`, `position`";
			if(substr($joomla,0,3) != '1.5'){
				$modulesQuery .= ", access, language";
			}
			$modulesQuery .= " ) VALUES ('%s', '%s', 'left'";
			if(substr($joomla,0,3) != '1.5'){
				$modulesQuery .= ", 1, '*'";
			}
			$modulesQuery .= " );";
			$db->setQuery(sprintf($modulesQuery, $title, $module));
			$db->query();
			if($db->getErrorNum()) {
				JError::raiseError( 500, $db->stderr());
				$success = false;
			}
		}
		
		if (JFolder::exists($modulesDstPath.DS.$module)){
			JFolder::delete($modulesDstPath.DS.$module);
		}
		if (!JFolder::move($modulesSrcPath.DS.$module, $modulesDstPath.DS.$module)){
			$success = false;
		}
		return $success;
	}
	
	static function uninstallModule( $module ){
		$db = & JFactory::getDBO();
		$db->setQuery('DELETE FROM `#__modules` WHERE `module`='.$db->Quote($module));
		$db->query();
		$version = new JVersion;
		$joomla = $version->getShortVersion();
		if(substr($joomla,0,3) != '1.5'){
			$db->setQuery('DELETE FROM `#__extensions` WHERE `name`='.$db->Quote($module));
			$db->query();
		}
		JFolder::delete(JPATH_ROOT.DS.'modules'.DS.$module);
	}
	
	static function updateTable(){
		$db = JFactory::getDBO();
		$db->setQuery("SHOW COLUMNS FROM #__vklogin_users LIKE 'vkid'");
		if (!$db->loadObject()){
			$db->setQuery('ALTER TABLE #__vklogin_users ADD COLUMN `vkid` INT(11) UNSIGNED NOT NULL, ADD INDEX (`vkid`)');
			$db->query();
			$db->setQuery('SELECT activation,id FROM #__users WHERE id IN (SELECT userid FROM #__vklogin_users)');
			$data = $db->loadObjectList();
			$insertData = array();
			foreach ($data as $user) {
				$insertData[] = "({$user->id}, {$user->activation})";
			}
			if (!empty($insertData)){
				$sql = "INSERT INTO #__vklogin_users (`userid`, `vkid`) VALUES ";
				$sql .= implode(',', $insertData);
				$sql .= " ON DUPLICATE KEY UPDATE `vkid`=VALUES(`vkid`)";
				$db->setQuery($sql);
				$db->query();
			}
		}
		$db->setQuery("SHOW COLUMNS FROM #__vklogin_users LIKE 'email_hash'");
		if (!$db->loadObject()){
			$db->setQuery('ALTER TABLE #__vklogin_users ADD COLUMN `email_hash` VARCHAR(32) NOT NULL, ADD INDEX (`email_hash`)');
			$db->query();
			$db->setQuery('SELECT MD5(email) as email,id FROM #__users WHERE id IN (SELECT userid FROM #__vklogin_users)');
			$data = $db->loadObjectList();
			$insertData = array();
			foreach ($data as $user) {
				$insertData[] = "({$user->id}, ".$db->Quote($user->email).")";
			}
			if (!empty($insertData)){
				$sql = "INSERT INTO #__vklogin_users (`userid`, `email_hash`) VALUES ";
				$sql .= implode(',', $insertData);
				$sql .= " ON DUPLICATE KEY UPDATE `email_hash`=VALUES(`email_hash`)";
				$db->setQuery($sql);
				$db->query();
			}
		}
		$newFileds = array(
			'first_name', 'last_name', 'nickname', 'sex', 'bdate', 'city', 'country',
			'timezone','photo_medium','photo_medium_rec', 'photo_big', 'photo_rec', 'home_phone',
			'mobile_phone', 'university_name', 'faculty_name', 'graduation', 'domain'
		);
		$fieldsToInsert = array();
		foreach ($newFileds as $field) {
			$db->setQuery("SHOW COLUMNS FROM #__vklogin_users LIKE '$field'");
			if (!$db->loadObject()){
				$fieldsToInsert[] = $field;
			}
		}
		if (!empty($fieldsToInsert)) {
			$sql = 'ALTER TABLE #__vklogin_users ';
			foreach ($fieldsToInsert as $field) {
				$sql .= "ADD COLUMN `$field` VARCHAR(255) NULL,";
			}
			$sql = substr($sql, 0, -1);
			$db->setQuery($sql);
			$db->query();
		}
	}
}
?>
