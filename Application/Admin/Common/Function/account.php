<?php



/**
 * 是否为供应商
 * @return bool
 */
function is_supplier(){
    if(session('admin_role_id') == 3){
        return true;
    }
    return false;
}



function getAccountInfo(){
    $condition = array(
        "admin_id" => session("admin_id"),
    );
    return findDataWithCondition("admin",$condition);
function refreshAccountMoney( $adminId ){

    $cumulativeTransactionAmount = 0;
    $condition = array(
        "admin_id" => $adminId,
        "is_send" => array( "in" , "0,1" )
    );
//    $sql = "SELECT og.*,g.original_img FROM __PREFIX__order_goods og LEFT JOIN __PREFIX__order o ON g.goods_id = og.goods_id WHERE order_id = ".$order_id;
//
//    M( ) ->join( array("__PREFIX__order_goods ") ) -> where($condition)
//    M("order_goods") ->join() -> where($condition)


}
}