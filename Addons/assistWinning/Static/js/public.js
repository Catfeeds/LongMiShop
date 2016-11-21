/* puclic js -- xiaoxu increase 2015-06-23 */


function isNum(str){
	var reg = /^[0-9_-]+$/;
	if(!reg.test(str)){
		return false;
	}
	return true;
}


function isTel(str){
	var reg = /^1/;
	if(!reg.test(str) || str.length!=11){
		return false;
	}
	return true;
}

function isNull(data){ 
	return (data == "" || data == undefined || data == null) ? "yes" : "no"; 
}