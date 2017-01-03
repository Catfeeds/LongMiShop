/**
 * 获取url
 * @param e
 * @returns {*}
 */
function getQueryString(e) {
	var t = new RegExp("(^|&)" + e + "=([^&]*)(&|$)");
	var a = window.location.search.substr(1).match(t);
	if (a != null) return a[2];
	return ""
}


/**
 * 是否处于微信浏览器
 * @returns {boolean}
 */
function isWeiXin(){
	var ua = window.navigator.userAgent.toLowerCase();
	if(ua.match(/MicroMessenger/i) == 'micromessenger'){
		return true;
	}else{
		return false;
	}
}

