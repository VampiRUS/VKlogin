<?php
// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.plugin.plugin');
/*
*
* @package 		Joomla
* @subpackage	System
*/
class plgSystemJsVk extends JPlugin
{
	/**
	 * Constructor
	 *
	 * For php4 compatability we must not use the __constructor as a constructor for plugins
	 * because func_get_args ( void ) returns a copy of all passed arguments NOT references.
	 * This causes problems with cross-referencing necessary for the observer design pattern.
	 *
	 * @param	object		$subject The object to observe
	  * @param 	array  		$config  An array that holds the plugin configuration
	 * @since	1.0
	 */
	function plgSystemJsVk(&$subject, $config)  {
		parent::__construct($subject, $config);
	}

	function onAfterRoute()
	{
		$mainframe	=& JFactory::getApplication();
		if ($mainframe->isAdmin()) {
			return;
		}
		if (file_exists(JPATH_ROOT.DS.'components'.DS.'com_vklogin'.DS.'core.php')){
			include_once(JPATH_ROOT.DS.'components'.DS.'com_vklogin'.DS.'core.php');
		} else {
			return;
		}
		$session =& JFactory::getSession();
		$vkConfig 	= &JComponentHelper::getParams( 'com_vklogin' );
		if ($vkConfig ->get('useractivation',0)){
			$usersConfig 	= &JComponentHelper::getParams( 'com_users' );
			$usersConfig->set( 'useractivation', 1 );
			$vkConfig ->get('useractivation',0);
		}
		$step = $session->get('regstep',0);
		
		if(JRequest::getCmd('option') == 'com_community' 
			&& JRequest::getCmd('view') == 'register'
			)
		{
			if (!VKlogin::check_cookie($vk_cookie))
			return 0;
			if (JRequest::getCmd('task') == 'registerProfile')
			{
			if ($step == 1 && empty($_POST)){
				$session->set('regstep',2);
				$jsdata = $session->get('jsdata',array());
				$data = array();
				$db =& JFactory::getDBO();
				$query = "SELECT v.id as `key`,v.value as value,f.type as type,f.options as options FROM #__vklogin as v JOIN #__community_fields as f ON v.id=f.id";
				$db->setQuery($query);
				$result = $db->loadObjectList();
				if($db->getErrorNum()) {
				JError::raiseError( 500, $db->stderr());
				}
				foreach ($result as $field){
					if(isset($jsdata[$field->value])){
						if ($field->value == "sex" && ($field->type=="select" || $field->type == "radio" || $field->type == "singleselect")){
							//he,she,it
							$options	= explode("\n", $field->options);
							array_walk($options, array('JString' , 'trim') );
							$jsdata[$field->value] =  ($jsdata[$field->value]==2)?$options[0]:
								(($jsdata[$field->value]==1)?$options[1]:
								(!empty($options[2])?$options[2]:''));
						}
						switch ($field->type){
							case 'date':
								$date = explode('.',$jsdata[$field->value]);
								$data['field'.$field->key] = (!empty($date[2])?$date[2]:'').'-'.(!empty($date[1])?$date[1]:'').'-'.(!empty($date[0])?$date[0]:'');
								break;
							case 'country':
								$data['field'.$field->key] = $this->getCountry($jsdata[$field->value]);
								break;
							default:
								$data['field'.$field->key] = $jsdata[$field->value];
						}
					}
				}
				JRequest::set($data,'post');
			}
			} else if (JRequest::getCmd('task') == 'registerAvatar' && $step == 4){
				$session->set('regstep',5);
				$usr = $session->get('tmpUser');
				$data = $session->get('vkdata',array());
				$db = &JFactory::getDBO();
				$db->setQuery('UPDATE #__users SET `block`=0  WHERE id='.$db->Quote($usr->id));
				$db->query();
				$db->setQuery('INSERT INTO #__vklogin_users (userid,vkid,email_hash) VALUES ('.$usr->id.','
					.$db->Quote($data['vkid']).','.$db->Quote(md5($usr->email)).')');
				$db->query();
				$session->clear('tmpUser');
				$session->clear('JS_REG_TOKEN');
				$session->clear('regstep');
				$mainframe->redirect(JRoute::_('index.php?option=com_vklogin'));
			} else if (JRequest::getCmd('task') == 'registerUpdateProfile' && $step == 2){
					$session->set('regstep',3);
					$usersConfig 	= &JComponentHelper::getParams( 'com_users' );
					$useractivation = $usersConfig->get( 'useractivation' );
					if ($useractivation){
						$usersConfig->set( 'useractivation',0 );
						$vkConfig->set( 'useractivation',1 );
					}
				}
		}
	}

	function getCountry($country){
		switch ($country){
			case 'Австралия':$result = "Australia";
				break;
			case 'Австрия':$result = "Austria";
				break;
			case 'Азербайджан':$result = "Azerbaijan";
				break;
			case 'Албания':$result = "Albania";
				break;
			case 'Алжир':$result = "Algeria";
				break;
			case 'Американское Самоа':$result = "American Samoa";
				break;
			case 'Ангилья':$result = "Anguilla";
				break;
			case 'Ангола':$result = "Angola";
				break;
			case 'Андорра':$result = "Andorra";
				break;
			case 'Антигуа и Барбуда':$result = "Antigua and Barbuda";
				break;
			case 'Аргентина':$result = "Argentina";
				break;
			case 'Армения':$result = "Armenia";
				break;
			case 'Аруба':$result = "Aruba";
				break;
			case 'Афганистан':$result = "Afghanistan";
				break;
			case 'Багамы':$result = "Bahamas";
				break;
			case 'Бангладеш':$result = "Bangladesh";
				break;
			case 'Барбадос':$result = "Barbados";
				break;
			case 'Бахрейн':$result = "Bahrain";
				break;
			case 'Беларусь':$result = "Belarus";
				break;
			case 'Белиз':$result = "Belize";
				break;
			case 'Бельгия':$result = "Belgium";
				break;
			case 'Бенин':$result = "Benin";
				break;
			case 'Бермуды':$result = "Bermuda";
				break;
			case 'Болгария':$result =  "Bulgaria";
				break;
			case 'Боливия':$result = "Bolivia";
				break;
			case 'Босния и Герцеговина':$result = "Bosnia and Herzegovina";
				break;
			case 'Ботсвана':$result = "Botswana";
				break;
			case 'Бразилия':$result = "Brazil";
				break;
			case 'Бруней-Даруссалам':$result = "Brunei Darussalam";
				break;
			case 'Буркина-Фасо':$result = "Burkina Faso";
				break;
			case 'Бурунди':$result = "Burundi";
				break;
			case 'Бутан':$result = "Bhutan";
				break;
			case 'Вануату':$result = "Vanuatu";
				break;
			case 'Великобритания':$result = "Great Britain (UK)";
				break;
			case 'Венгрия':$result = "Hungary";
				break;
			case 'Венесуэла':$result = "Venezuela";
				break;
			case 'Виргинские острова, Британские':$result = "Virgin Islands (British)";
				break;
			case 'Виргинские острова, США':$result = "Virgin Islands (U.S.)";
				break;
			case 'Восточный Тимор':$result = "East Timor";
				break;
			case 'Вьетнам':$result = "Viet Nam";
				break;
			case 'Габон':$result = "Gabon";
				break;
			case 'Гаити':$result = "Haiti";
				break;
			case 'Гайана':$result = "Guyana";
				break;
			case 'Гамбия':$result = "Gambia";
				break;
			case 'Гана':$result =  "Ghana";
				break;
			case 'Гваделупа':$result = "Guadeloupe";
				break;
			case 'Гватемала':$result = "Guatemala";
				break;
			case 'Гвинея':$result = "Guinea";
				break;
			case 'Гвинея-Бисау':$result = "Guinea-Bissau";
				break;
			case 'Германия':$result = "Germany";
				break;
			case 'Гибралтар':$result = "Gibraltar";
				break;
			case 'Гондурас':$result = "Honduras";
				break;
			case 'Гонконг':$result = "Hong Kong";
				break;
			case 'Гренада':$result = "Grenada";
				break;
			case 'Гренландия':$result = "Greenland";
				break;
			case 'Греция':$result = "Greece";
				break;
			case 'Грузия':$result = "Georgia";
				break;
			case 'Гуам':$result = "Guam";
				break;
			case 'Дания':$result = "Denmark";
				break;
			case 'Джибути':$result = "Djibouti";
				break;
			case 'Доминика':$result = "Dominica";
				break;
			case 'Доминиканская Республика':$result = "Dominican Republic";
				break;
			case 'Египет':$result = "Egypt";
				break;
			case 'Замбия':$result = "Zambia";
				break;
			case 'Западная Сахара':$result = "Western Sahara";
				break;
			case 'Зимбабве':$result = "Zimbabwe";
				break;
			case 'Израиль':$result = "Israel";
				break;
			case 'Индия':$result = "India";
				break;
			case 'Индонезия':$result = "Indonesia";
				break;
			case 'Иордания':$result = "Jordan";
				break;
			case 'Ирак':$result = "Iraq";
				break;
			case 'Иран':$result = "Iran";
				break;
			case 'Ирландия':$result = "Ireland";
				break;
			case 'Исландия':$result = "Iceland";
				break;
			case 'Испания':$result = "Spain";
				break;
			case 'Италия':$result = "Italy";
				break;
			case 'Йемен':$result = "Yemen";
				break;
			case 'Кабо-Верде':$result = "Cape Verde";
				break;
			case 'Казахстан':$result = "Kazakhstan";
				break;
			case 'Камбоджа':$result = "Cambodia";
				break;
			case 'Камерун':$result = "Cameroon";
				break;
			case 'Канада':$result = "Canada";
				break;
			case 'Катар':$result =  "Qatar";
				break;
			case 'Кения':$result = "Kenya";
				break;
			case 'Кипр':$result = "Cyprus";
				break;
			case 'Кирибати':$result = "Kiribati";
				break;
			case 'Китай':$result = "China";
				break;
			case 'Колумбия':$result = "Colombia";
				break;
			case 'Коморы':$result = "Comoros";
				break;
			case 'Конго':$result = "Congo";
				break;
			case 'Конго, демократическая республика':$result = "Congo";
				break;
			case 'Коста-Рика':$result = "Costa Rica";
				break;
			case 'Кот д`Ивуар':$result = "Cote D'Ivoire (Ivory Coast)";
				break;
			case 'Куба':$result = "Cuba";
				break;
			case 'Кувейт':$result = "Kuwait";
				break;
			case 'Кыргызстан':$result = "Kyrgyzstan";
				break;
			case 'Лаос':$result = "Laos";
				break;
			case 'Латвия':$result = "Latvia";
				break;
			case 'Лесото':$result = "Lesotho";
				break;
			case 'Либерия':$result = "Liberia";
				break;
			case 'Ливан':$result = "Lebanon";
				break;
			case 'Ливийская Арабская Джамахирия':$result = "Libya";
				break;
			case 'Литва':$result = "Lithuania";
				break;
			case 'Лихтенштейн':$result = "Liechtenstein";
				break;
			case 'Люксембург':$result = "Luxembourg";
				break;
			case 'Маврикий':$result = "Mauritius";
				break;
			case 'Мавритания':$result = "Mauritania";
				break;
			case 'Мадагаскар':$result = "Madagascar";
				break;
			case 'Макао':$result = "Macau";
				break;
			case 'Македония':$result = "Macedonia";
				break;
			case 'Малави':$result = "Malawi";
				break;
			case 'Малайзия':$result = "Malaysia";
				break;
			case 'Мали':$result = "Mali";
				break;
			case 'Мальдивы':$result = "Maldives";
				break;
			case 'Мальта':$result = "Malta";
				break;
			case 'Марокко':$result = "Morocco";
				break;
			case 'Мартиника':$result = "Martinique";
				break;
			case 'Маршалловы Острова':$result = "Marshall Islands";
				break;
			case 'Мексика':$result = "Mexico";
				break;
			case 'Микронезия, федеративные штаты':$result = "Micronesia";
				break;
			case 'Мозамбик':$result = "Mozambique";
				break;
			case 'Молдова':$result = "Moldova";
				break;
			case 'Монако':$result = "Monaco";
				break;
			case 'Монголия':$result = "Mongolia";
				break;
			case 'Монтсеррат':$result = "Montserrat";
				break;
			case 'Мьянма':$result = "Myanmar";
				break;
			case 'Намибия':$result = "Namibia";
				break;
			case 'Науру':$result = "Nauru";
				break;
			case 'Непал':$result = "Nepal";
				break;
			case 'Нигер':$result = "Niger";
				break;
			case 'Нигерия':$result = "Nigeria";
				break;
			case 'Нидерландские Антилы':$result = "Netherlands Antilles";
				break;
			case 'Нидерланды':$result = "Netherlands";
				break;
			case 'Никарагуа':$result = "Nicaragua";
				break;
			case 'Ниуэ':$result = "Niue";
				break;
			case 'Новая Зеландия':$result = "New Zealand";
				break;
			case 'Новая Каледония':$result = "New Caledonia";
				break;
			case 'Норвегия':$result = "Norway";
				break;
			case 'Объединенные Арабские Эмираты':$result = "United Arab Emirates";
				break;
			case 'Оман':$result = "Oman";
				break;
			case 'Остров Мэн':$result = "";
				break;
			case 'Остров Норфолк':$result = "Norfolk Island";
				break;
			case 'Острова Кайман':$result = "Cayman Islands";
				break;
			case 'Острова Кука':$result = "Cocos (Keeling) Islands";
				break;
			case 'Острова Теркс и Кайкос':$result = "Turks and Caicos Islands";
				break;
			case 'Пакистан':$result = "Pakistan";
				break;
			case 'Палау':$result = "Palau";
				break;
			case 'Палестинская автономия':$result = "";
				break;
			case 'Панама':$result = "Panama";
				break;
			case 'Папуа - Новая Гвинея':$result = "Papua New Guinea";
				break;
			case 'Парагвай':$result =  "Paraguay";
				break;
			case 'Перу':$result = "Peru";
				break;
			case 'Питкерн':$result = "Pitcairn";
				break;
			case 'Польша':$result = "Poland";
				break;
			case 'Португалия':$result = "Portugal";
				break;
			case 'Пуэрто-Рико':$result = "Puerto Rico";
				break;
			case 'Реюньон':$result = "Reunion";
				break;
			case 'Россия':$result = "Russian Federation";
				break;
			case 'Руанда':$result = "Rwanda";
				break;
			case 'Румыния':$result = "Romania";
				break;
			case 'США':$result = "United States";
				break;
			case 'Сальвадор':$result = "El Salvador";
				break;
			case 'Самоа':$result = "Samoa";
				break;
			case 'Сан-Марино':$result = "San Marino";
				break;
			case 'Сан-Томе и Принсипи':$result = "Sao Tome and Principe";
				break;
			case 'Саудовская Аравия':$result = "Saudi Arabia";
				break;
			case 'Свазиленд':$result = "Swaziland";
				break;
			case 'Святая Елена':$result = "St. Helena";
				break;
			case 'Северная Корея':$result = "Korea, North";
				break;
			case 'Северные Марианские острова':$result = "Northern Mariana Islands";
				break;
			case 'Сейшелы':$result = "Seychelles";
				break;
			case 'Сенегал':$result = "Senegal";
				break;
			case 'Сент-Винсент':$result = "Saint Vincent and the Grenadines";
				break;
			case 'Сент-Китс и Невис':$result = "Saint Kitts and Nevis";
				break;
			case 'Сент-Люсия':$result = "Saint Lucia";
				break;
			case 'Сент-Пьер и Микелон':$result = "St. Pierre and Miquelon";
				break;
			case 'Сербия':$result = "";
				break;
			case 'Сингапур':$result = "Singapore";
				break;
			case 'Сирийская Арабская Республика':$result = "Syria";
				break;
			case 'Словакия':$result = "Slovak Republic";
				break;
			case 'Словения':$result = "Slovenia";
				break;
			case 'Соломоновы Острова':$result = "Solomon Islands";
				break;
			case 'Сомали':$result = "Somalia";
				break;
			case 'Судан':$result = "Sudan";
				break;
			case 'Суринам':$result = "Suriname";
				break;
			case 'Сьерра-Леоне':$result = "Sierra Leone";
				break;
			case 'Таджикистан':$result = "Tajikistan";
				break;
			case 'Таиланд':$result = "Thailand";
				break;
			case 'Тайвань':$result = "Taiwan";
				break;
			case 'Танзания':$result = "Tanzania";
				break;
			case 'Того':$result = "Togo";
				break;
			case 'Токелау':$result = "Tokelau";
				break;
			case 'Тонга':$result = "Tonga";
				break;
			case 'Тринидад и Тобаго':$result = "Trinidad and Tobago";
				break;
			case 'Тувалу':$result = "Tuvalu";
				break;
			case 'Тунис':$result = "Tunisia";
				break;
			case 'Туркмения':$result = "Turkmenistan";
				break;
			case 'Турция':$result = "Turkey";
				break;
			case 'Уганда':$result = "Uganda";
				break;
			case 'Узбекистан':$result = "Uzbekistan";
				break;
			case 'Украина':$result = "Ukraine";
				break;
			case 'Уоллис и Футуна':$result = "Wallis and Futuna Islands";
				break;
			case 'Уругвай':$result = "Uruguay";
				break;
			case 'Фарерские острова':$result = "Faroe Islands";
				break;
			case 'Фиджи':$result = "Fiji";
				break;
			case 'Филиппины':$result = "Philippines";
				break;
			case 'Финляндия':$result = "Finland";
				break;
			case 'Фолклендские острова':$result = "Falkland Islands (Malvinas)";
				break;
			case 'Франция':$result = "France";
				break;
			case 'Французская Гвиана':$result = "French Guiana";
				break;
			case 'Французская Полинезия':$result = "French Polynesia";
				break;
			case 'Хорватия':$result = "";
				break;
			case 'Центрально-Африканская Республика':$result = "Central African Republic";
				break;
			case 'Чад':$result = "Chad";
				break;
			case 'Черногория':$result = '';
				break;
			case 'Чехия':$result = "Czech Republic";
				break;
			case 'Чили':$result = "China";
				break;
			case 'Швейцария':$result = "Switzerland";
				break;
			case 'Швеция':$result = "Sweden";
				break;
			case 'Шпицберген и Ян Майен':$result = "";
				break;
			case 'Шри-Ланка':$result = "Sri Lanka";
				break;
			case 'Эквадор':$result = "Ecuador";
				break;
			case 'Экваториальная Гвинея':$result = "Equatorial Guinea";
				break;
			case 'Эритрея':$result = "Eritrea";
				break;
			case 'Эстония':$result = "Estonia";
				break;
			case 'Эфиопия':$result = "Ethiopia";
				break;
			case 'Южная Корея':$result = "South Korea";
				break;
			case 'Южно-Африканская Республика':$result = "South Africa";
				break;
			case 'Ямайка':$result = "Jamaica";
				break;
			case 'Япония':$result = "Japan";
				break;
			default: $result = '';
		}
		return $result;
	}
}
