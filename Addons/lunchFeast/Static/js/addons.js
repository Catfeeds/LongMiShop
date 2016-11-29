
/**
 * 重写alert函数
 * @param msg
 */
function alert( msg ){
    mui.toast( msg );
}

//    setInterval("getMsg()", 1000);
$(function(){
    getMsg();
});
function getMsg(){
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
}