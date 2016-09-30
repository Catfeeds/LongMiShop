<?php



/**
 * 获取礼品券id
 * @param $code
 * @return int
 */
function getGiftCouponId( $code ){
    $condition = array(
        "gift_coupon_id"    =>  array('neq',0),
        "coupon_id"         =>  array('eq',0),
        "code"              =>  $code,
    );
    $data = findDataWithCondition( "coupon_code" , $condition , "gift_coupon_id");
    return intval($data['gift_coupon_id']);
}
