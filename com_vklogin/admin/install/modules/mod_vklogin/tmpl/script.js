function vk_login() {
	VK.Auth.login(vk_handler,1);
	return false;
}
function toggleRemember(box){
	document.getElementById('vkremember').value = box.checked;
}
function vk_handler(response) {
	if (typeof response.hash != 'undefined') {
			document.forms.vklogin.hash.value = response.hash;
	}
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
		var result = punycode.ToASCII(window.location.hostname);
		if (window.location.hostname != result) {
			VK.Cookie.setRaw = function(val, ts, domain) {
				var
				rawCookie;
				domain = punycode.ToASCII(domain);
				rawCookie = 'vk_app_' + VK._apiId + '=' + val + '';
				rawCookie += (val && ts == 0 ? '' : '; expires=' + new Date(ts * 1000).toGMTString());
				rawCookie += '; path=/';
				rawCookie += (domain ? '; domain=.' + domain : '');
				document.cookie = rawCookie;
				this._domain = domain;
			}
			VK.Cookie.set(response.session);
			VK.Api.attachScript('http://api.vkontakte.ru/method/getProfiles?fields=nickname,sex,bdate,timezone,photo,photo_medium,photo_medium_rec,photo_big,photo_rec,contacts,education,screen_name&callback=getResp&uids='+response.uid);
		} else {
			VK.Cookie.set(response.session);
			var code = 'var me =  API.getProfiles({uids:'+response.uid+',fields:\"nickname,sex,bdate,city,country,timezone,photo,photo_medium,photo_medium_rec,photo_big,photo_rec,contacts,education,screen_name\"})[0];'
			+'var country = API.places.getCountryById({cids:me.country})[0].name;'
			+'var city = API.places.getCityById({cids:me.city})[0].name;'
			+'return [me,country,city];';
			VK.Api.call('execute', {'code': code}, getResp); 
		}
	}
}
function getResp(r){
	if(r.response) {
		document.forms.vklogin.name.value = r.response[0].first_name + ' ' + r.response[0].last_name;
		document.forms.vklogin.photo_rec.value = r.response[0].photo_rec;
		if (r.response[0].nickname == ''){
			document.forms.vklogin.username.value = getUsername(r.response[0]);
		} else {
			document.forms.vklogin.username.value = r.response[0].nickname;
		} 
		document.forms.vklogin.domain.value = r.response[0].screen_name;
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
			if (r.response[2] !== false && typeof r.response[2] != 'undefined'){ 
				document.forms.vklogin.elements[9].value = r.response[2];
			}
			if (r.response[1] !== false && typeof r.response[1] != 'undefined'){ 
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
}
