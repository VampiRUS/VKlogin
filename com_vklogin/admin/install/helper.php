<?php
jimport( 'joomla.filesystem.folder' );
jimport( 'joomla.filesystem.file' );

class VkloginInstallHelper{
	
	static function installPlugin($name, $element, $folder){
		$success = true;
		$db = & JFactory::getDBO();
		$pluginsDstPath = JPATH_ROOT.DS.'plugins'.DS.$folder;
		if (JFile::exists($pluginsDstPath.DS.$element.DS.'.xml')){
			self::uninstallPlugin($element, $folder);
		}
		$version = new JVersion;
		$joomla = $version->getShortVersion();
		if(substr($joomla,0,3) == '1.6'){
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
		$files = JFolder::files($pluginsSrcPath);
		foreach( $files as $file){
			if (!JFile::move($pluginsSrcPath.DS.$file, $pluginsDstPath.DS.$file)){
				$success = false;
			}
		}
		return $success;
	}
	
	
	static function uninstallPlugin($element, $folder){
		$db = & JFactory::getDBO();
		$version = new JVersion;
		$joomla = $version->getShortVersion();
		if(substr($joomla,0,3) == '1.6'){
			$pluginsQuery = 'DELETE FROM `#__extensions` WHERE `folder`='.$db->Quote($folder).' AND `element`='.$db->Quote($element);
		} else {
			$pluginsQuery = 'DELETE FROM `#__plugins` WHERE `folder`='.$db->Quote($folder).' AND `element`='.$db->Quote($element); 
		}
		$db->setQuery($pluginsQuery);
		$db->query();
		$files[] = JPATH_ROOT.DS.'plugins'.DS.$folder.DS.$element.'.php';
		$files[] = JPATH_ROOT.DS.'plugins'.DS.$folder.DS.$element.'.xml';
		//TODO to get files from xml
		JFile::delete($files);
	}

	static function installModule( $title, $module ){
		$success = true;
		$db = & JFactory::getDBO();
		$modulesDstPath = JPATH_ROOT.DS.'modules';
		$modulesSrcPath = JPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_vklogin'.DS.'install'.DS.'modules';
		$version = new JVersion;
		$joomla = $version->getShortVersion();
		if(substr($joomla,0,3) == '1.6'){
			$modulesQuery = 'SELECT * FROM `#__extensions` WHERE `name`="%2$s"';
		} else {
			$modulesQuery = "SELECT * FROM `#__modules` WHERE `title`='%s' AND `module`='%s'";
		}
		$db->setQuery(sprintf($modulesQuery, $title, $module));
		$db->query();
		if (!$db->getNumRows()){
			if(substr($joomla,0,3) == '1.6'){
				$modulesQuery = "INSERT INTO `#__extensions` (`type`,`name`, `element`, `folder`, `enabled`,`protected` ) VALUES ('module','mod_vklogin', 'mod_vklogin', '', 1, 1 );";
				$db->setQuery($modulesQuery);
				$db->query();
			}
			$modulesQuery =  "INSERT INTO `#__modules` (`title`, `module`, `position`";
			if(substr($joomla,0,3) == '1.6'){
				$modulesQuery .= ", access, language";
			}
			$modulesQuery .= " ) VALUES ('%s', '%s', 'left'";
			if(substr($joomla,0,3) == '1.6'){
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
		if(substr($joomla,0,3) == '1.6'){
			$db->setQuery('DELETE FROM `#__extensions` WHERE `name`='.$db->Quote($module));
			$db->query();
		}
		JFolder::delete(JPATH_ROOT.DS.'modules'.DS.$module);
	}
	
}
?>
