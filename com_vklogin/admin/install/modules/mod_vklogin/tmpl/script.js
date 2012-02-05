function vk_login() {
	VK.Auth.login(vk_handler,1);
	return false;
}
function toggleRemember(box){
	document.getElementById('vkremember').value = box.checked;
}
function vk_handler(response) {
	if (response.session) {
		var d = new Date();
		var now = d.toGMTString();
		if (Date.hasOwnProperty('format')) {
			//mootools
			newDate = new Date(Date.parse(now).format('%s')*1000+24*60*60*1000).toGMTString();
		} else {
			newDate = new Date(Date.parse(now)+24*60*60*1000).toGMTString();
		}
		window.Date.prototype.toGMTString = function(){
			return newDate;
		};
		VK.Cookie.set(response.session);
		var start = document.cookie.indexOf('vk_app_'+VK._apiId);
		var end = document.cookie.indexOf(';', start);
		var vk_cookie = (end == -1)?document.cookie.substring(start):document.cookie.substring(start, end);
		start = vk_cookie.indexOf('mid') + 4;
		end = vk_cookie.indexOf('&', start);
		var mid = vk_cookie.substring(start, end);
		var code = 'var me =  API.getProfiles({uids:'+mid+',fields:\"nickname,sex,bdate,city,country,timezone,photo,photo_medium,photo_medium_rec,photo_big,photo_rec,contacts,education,domain\"})[0];'
		+'var country = API.places.getCountryById({cids:me.country})[0].name;'
		+'var city = API.places.getCityById({cids:me.city})[0].name;'
		+'return [me,country,city];';
		VK.Api.call('execute', {'code': code}, function(r){
			if(r.response) {
				document.forms.vklogin.name.value = r.response[0].first_name + ' ' + r.response[0].last_name;
				document.forms.vklogin.photo_rec.value = r.response[0].photo_rec;
				if (r.response[0].nickname == ''){
					document.forms.vklogin.username.value = getUsername(r.response[0]);
				} else {
					document.forms.vklogin.username.value = r.response[0].nickname;
				} 
				document.forms.vklogin.domain.value = r.response[0].domain;
					document.forms.vklogin.elements[3].value = r.response[0].uid;
					document.forms.vklogin.elements[4].value = r.response[0].first_name;
					document.forms.vklogin.elements[5].value = r.response[0].last_name;
					document.forms.vklogin.elements[6].value = r.response[0].nickname;
					if (typeof r.response[0].sex != 'undefined'){ 
						document.forms.vklogin.elements[7].value = r.response[0].sex;
					}
					if (typeof r.response[0].bdate != 'undefined'){ 
						document.forms.vklogin.elements[8].value = r.response[0].bdate;
					}
					if (r.response[2] !== false){ 
						document.forms.vklogin.elements[9].value = r.response[2];
					}
					if (r.response[1] !== false){ 
						document.forms.vklogin.elements[10].value = r.response[1];
					}
					if (typeof r.response[0].timezone != 'undefined'){ 
						document.forms.vklogin.elements[11].value = r.response[0].timezone;
					}
					if (typeof r.response[0].photo != 'undefined'){ 
						document.forms.vklogin.elements[12].value = r.response[0].photo;
					}
					if (typeof r.response[0].photo_medium != 'undefined'){ 
						document.forms.vklogin.elements[13].value = r.response[0].photo_medium;
					}
					if (typeof r.response[0].photo_big != 'undefined'){ 
						document.forms.vklogin.elements[14].value = r.response[0].photo_big;
					}
					if (typeof r.response[0].home_phone != 'undefined'){ 
						document.forms.vklogin.elements[15].value = r.response[0].home_phone;
					}
					if (typeof r.response[0].mobile_phone != 'undefined'){ 
						document.forms.vklogin.elements[16].value = r.response[0].mobile_phone;
					}
					if (typeof r.response[0].university_name != 'undefined'){ 
						document.forms.vklogin.elements[17].value = r.response[0].university_name;
					}
					if (typeof r.response[0].faculty_name != 'undefined'){ 
						document.forms.vklogin.elements[18].value = r.response[0].faculty_name;
					}
					if (typeof r.response[0].graduation != 'undefined'){ 
						document.forms.vklogin.elements[19].value = r.response[0].graduation;
					}
					if (typeof r.response[0].photo_rec != 'undefined'){ 
						document.forms.vklogin.elements[20].value = r.response[0].photo_rec;
					}
					if (typeof r.response[0].photo_medium_rec != 'undefined'){ 
						document.forms.vklogin.elements[21].value = r.response[0].photo_medium_rec;
					}
			}
		document.forms.vklogin.submit();
		}); 
	}
}