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


/**
 * @return mixed
 */
function getAccountInfo(){
//    refreshAccountMoney( session("admin_id") );
    $condition = array(
        "admin_id" => session("admin_id"),
    );
    return findDataWithCondition("admin",$condition);
}

/**
 * @param null $adminId
 */
function refreshAccountMoney( $adminId = null ){
    if( is_null($adminId) ){
        return;
    }
    $cumulativeTransactionAmount = 0;
    $where = array();
    $where["_string"] = " ( order_status = 2 or  order_status = 4 ) ";
    $where["confirm_time"] = array( "lt" , time() - ( 60 * 60 * 24 * 7 ) );
    $where["pay_status"] = 1;
    $where["admin_list"] = array("like","%[".$adminId."]%");
    $orderList = selectDataWithCondition( "order" , $where , "order_id" );
    if( !empty( $orderList ) ){
        foreach ( $orderList as $orderItem ){
            $condition = array(
                "order_id" =>  $orderItem['order_id'],
                "is_send" => 1,
            );
            $orderGoodsInfo = selectDataWithCondition( "order_goods" , $condition , " member_goods_price , goods_num , goods_postage");
            if( !empty( $orderGoodsInfo ) ){
                foreach ( $orderGoodsInfo as $orderGoodsItem){
                    $cumulativeTransactionAmount += $orderGoodsItem["goods_num"] * $orderGoodsItem["member_goods_price"] ;
                    $cumulativeTransactionAmount += $orderGoodsItem["goods_postage"] ;
                }
            }
        }
    }

    $adminInfo = findDataWithCondition( "admin" , array( "admin_id" => $adminId ) , "withdrawals_amount" );
    $amount = $cumulativeTransactionAmount - $adminInfo['withdrawals_amount'];
    $save = array(
        "amount_refresh_time"   =>  time(),
        "amount"                =>  $amount,
        "transaction_amount"    =>  $cumulativeTransactionAmount
    );

    M("admin") -> where( array( "admin_id" => $adminId ) ) -> save($save);


}