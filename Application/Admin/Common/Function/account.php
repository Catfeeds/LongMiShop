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
    dd($orderGoodsList);
    return $money;
}