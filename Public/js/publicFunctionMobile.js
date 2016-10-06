


/**
 * 重写alert函数
 * @param msg
 */
function alert( msg ){
     mui.toast( msg );
}

$(function(){
    var time = Date.parse(new Date());
    time = time * 123465;
    $.ajax({
        type : "get",
        url:"/index.php/Mobile/User/returnSession/time/"+time+".html",
        success: function(data)
        {
            if( data != undefined && data != "" && data != null ){
                alert(data);
            }
        }
    });
});


/**
 * 取消订单
 * @param id
 * @returns {boolean}
 */
function cancel_order( id ){
    if(!confirm("确定取消订单?")){
        return false;
    }
    location.href = "/index.php?m=Mobile&c=Order&a=cancelOrder&id="+id;
}

/**
 * 设置 cookie
 * @param objName
 * @param objValue
 * @param objHours
 */
function setMobileCookie(objName, objValue, objHours)
{
    var str = objName + "=" + escape(objValue);
    if (objHours > 0) {//为0时不设定过期时间，浏览器关闭时cookie自动消失
        var date = new Date();
        var ms = objHours * 3600 * 1000;
        date.setTime(date.getTime() + ms);
        str += "; expires=" + date.toGMTString();
    }
    document.cookie = str;
}
/**
 * 获取cookie
 * @param objName
 * @returns {null}
 */
function getMobileCookie(objName)
{
    // console.log( document.cookie );
    var arrStr = document.cookie.split("; ");
    for (var i = 0; i < arrStr.length; i++) {
        var temp = arrStr[i].split("=");
        if (temp[0] == objName)
            return unescape(temp[1]);
    }
}



/**
 * 删除cookie
 * @param name
 */
function delMobileCookie(name)
{
    var exp = new Date();
    exp.setTime(exp.getTime() - 10000);
    document.cookie = name + "=123;expires="+exp.toGMTString();
}