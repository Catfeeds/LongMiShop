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


/**
 * 生成随机数
 * @param $number
 * @return array
 */
function getCouponCode( $number ){
    $codeArray = array();
//    $giftCouponCount = M('gift_coupon')->count();
    $key = 0;
    for( $i = 1 ; $i <= $number; $i++){

//        $codeNumber = $giftCouponCount + $i;
//        $tempString = md5( $codeNumber . "LONGMI");
        $codeArray[$key] = generateCode(18);
        $key ++;
    }
    return $codeArray;
}


function getCouponInfo( $couponId ){
    $condition  = array(
        "coupon_id" => $couponId,
    );
    return M('coupon') -> where( $condition )->find();
}