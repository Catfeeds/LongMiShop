


/**
 * 重写alert函数
 * @param msg
 */
function alert(msg){
     mui.toast(msg);
}


/**
 * 取消订单
 * @param id
 * @returns {boolean}
 */
function cancel_order(id){
    if(!confirm("确定取消订单?")){
        return false;
    }
    location.href = "/index.php?m=Mobile&c=User&a=cancel_order&id="+id;
}