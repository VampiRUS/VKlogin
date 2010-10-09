<?php
jimport( 'joomla.filesystem.folder' );
jimport( 'joomla.filesystem.file' );

class VkloginInstallHelper{
	
	function installPlugin($name, $element, $folder){
		$success = true;
		$db = & JFactory::getDBO();
		$pluginsDstPath = JPATH_ROOT.DS.'plugins'.DS.$folder;
		if (JFile::exists($pluginsDstPath.DS.$element.DS.'.xml')){
			self::uninstallPlugin($element, $folder);
		}
		$pluginsQuery = "INSERT INTO `#__plugins` (`name`, `element`, `folder`, `published` ) VALUES ('%s', '%s', '%s', 1 );";
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
	
	
	function uninstallPlugin($element, $folder){
		$db = & JFactory::getDBO();
		$db->setQuery('DELETE FROM `#__plugins` WHERE `folder`='.$db->Quote($folder).' AND `element`='.$db->Quote($element));
		$db->query();
		$files[] = JPATH_ROOT.DS.'plugins'.DS.$folder.DS.$element.'.php';
		$files[] = JPATH_ROOT.DS.'plugins'.DS.$folder.DS.$element.'.xml';
		//TODO to get files from xml
		JFile::delete($files);
	}

	function installModule( $title, $module ){
		$success = true;
		$db = & JFactory::getDBO();
		$modulesDstPath = JPATH_ROOT.DS.'modules';
		$modulesSrcPath = JPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_vklogin'.DS.'install'.DS.'modules';
		$modulesQuery = "SELECT * FROM `#__modules` WHERE `title`='%s' AND `module`='%s'";
		$db->setQuery(sprintf($modulesQuery, $title, $module));
		$db->query();
		if (!$db->getNumRows()){
			$modulesQuery = "INSERT INTO `#__modules` (`title`, `module`, `position` ) VALUES ('%s', '%s', 'left' );";
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
	
	function uninstallModule( $module ){
		$db = & JFactory::getDBO();
		$db->setQuery('DELETE FROM `#__modules` WHERE `module`='.$db->Quote($module));
		$db->query();
		JFolder::delete(JPATH_ROOT.DS.'modules'.DS.$module);
	}
	
}


?>
