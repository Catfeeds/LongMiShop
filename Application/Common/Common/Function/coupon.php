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
 * 获取优惠券id
 * @param $code
 * @return int
 */
function getCouponId( $code ){
    $condition = array(
        "code"     => $code,
    );
    $data = findDataWithCondition( "coupon_list" , $condition , "cid");
    return intval($data['cid']);
}


/**
 * 生成随机数
 * @param $number
 * @return array
 */
function getCouponCode( $number ){
    $codeArray = array();
//    $GiftCouponCount = M('gift_coupon')->count();
    $key = 0;
    for( $i = 1 ; $i <= $number; $i++){

//        $codeNumber = $GiftCouponCount + $i;
//        $tempString = md5( $codeNumber . "LONGMI");
        do{
            $code = get_rand_str(8,0,1);//获取随机8位字符串
            $check_exist = findDataWithCondition('coupon_list',array('code'=>$code),"code");
            if( empty( $check_exist ) ){
                $check_exist = findDataWithCondition('coupon_code',array('code'=>$code),"code");
            }
        }while($check_exist);

        $codeArray[$key] = generateCode(18);
        $key ++;
    }
    return $codeArray;
}

/**
 * 获取优惠券详情
 * @param $couponId
 * @return mixed
 */
function getCouponInfo( $couponId ){
    $couponId = intval( $couponId );
    $condition  = array(
        "id" => $couponId,
    );
    return findDataWithCondition( 'coupon' , $condition , "name" );
}

/**
 * 通过兑换码获取优惠券详情
 * @param $code
 * @return mixed
 */
function getCouponInfoWithCode( $code ){
    $couponId = getCouponId( $code );
    return getCouponInfo( $couponId );
}


/**
 * 领取卡券
 * @param $code
 * @param $userId
 * @return bool
 */
function receiveCouponCode( $code , $userId  )
{
    $condition = array(
        "code"     => $code,
        "uid"      => array("eq", "0"),
        "order_id" => array("eq", "0"),
    );
    $data = array(
        "uid"       => $userId,
    );
    $result = saveData("coupon_list", $condition, $data);
    if ($result > 0 || $result === 0) {
        return true;
    }
    return false;
}


/**
 * 获取优惠券通过兑换码
 * @param $code
 * @param $userId
 * @return bool
 */
function gainCouponWithCode( $code , $userId ){

    $couponInfo = getCouponInfoWithCode( $code );
    if( empty( $couponInfo ) ){
        return false;
    }
    if( ! receiveCouponCode( $code , $userId ) ){
        return false;
    }
//    if( ! giveGift( $userId , $couponId , 1 ) ){
//        return false;
//    }
    return true;
}

