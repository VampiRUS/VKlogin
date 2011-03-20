<?php
// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.plugin.plugin');
/*
*
* @package 		Joomla
* @subpackage	System
*/
class plgSystemVkGravatar extends JPlugin
{
	function plgSystemVkGravatar(&$subject, $config)  {
		parent::__construct($subject, $config);
	}

	function onAfterRender()
	{
		$app =& JFactory::getApplication();

		if($app->getName() != 'site') {
			return true;
		}
		
		$buffer = JResponse::getBody();
		
		// pull out contents of editor to prevent URL changes inside edit area
		$editor =& JFactory::getEditor();
		$regex = '#'.$editor->_tagForSEF['start'].'(.*)'.$editor->_tagForSEF['end'].'#Us';
		preg_match_all($regex, $buffer, $editContents, PREG_PATTERN_ORDER);

		// create an array to hold the placeholder text (in case there are more than one editor areas)
		$placeholders = array();
		for ($i = 0; $i < count($editContents[0]); $i++) {
			$placeholders[] = $editor->_tagForSEF['start'].$i.$editor->_tagForSEF['end'];
		}
		
		// replace editor contents with placeholder text
		$buffer 	= str_replace($editContents[0], $placeholders, $buffer);
		
	$preg = '#src="(http://www.gravatar.com/avatar/(.*?)\\?.*?)"|src="(http://www.gravatar.com/avatar.php\\?gravatar_id=(.*?)&.*?)"#';
		if (preg_match_all($preg, $buffer, $result)){
			$result[2] = array_merge($result[2],$result[4]);
			$result[1] = array_merge($result[1],$result[3]);
			$db = JFactory::getDBO();
			$emails = array_map(array($db,"Quote"),$result[2]);
			$db->setQuery('SELECT photo,email_hash FROM #__vklogin_users WHERE email_hash in ('.implode(',', $emails).')');
			$photo = $db->loadObjectList('email_hash');
			$keys =array_intersect($result[2], array_keys($photo));
			$search = array_intersect_key($result[1], $keys);
			$replace = array();

			foreach ($result[2] as $key){
				if (isset($photo[$key])) {
					$replace[] = $photo[$key]->photo;
				}
			}
			$buffer = str_replace($search, $replace, $buffer);
		}
		$buffer 	= str_replace($placeholders, $editContents[0], $buffer);
		JResponse::setBody($buffer);
		return true;
	}
	
}