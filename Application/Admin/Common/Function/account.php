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



function getSupplierAccountMoney(){
    $money = 0;
    $condition = array(
        "admin_id" => session("admin_id"),
    );
//    $goods->field('th_goods.*,th__user.provinceId')->join('left join th_user on th_goods.userId = th_user.userId')->where($whereCondition)->select();

    $orderList = M("order") -> where( $condition ) -> select();
    $orderGoodsList = M('order_goods') -> where($condition) -> select();
//    dd($orderGoodsList);
    return $money;
}

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