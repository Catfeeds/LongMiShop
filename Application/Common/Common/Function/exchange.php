<?php

/**
 * 查看 兑换码 是否存在 是否可用
 * @param $code
 * @return array
 */
function checkCode( $code  )
{
    if (!empty($code)) {
        $condition = array(
            "gift_coupon_id" => array('neq', 0),
            "coupon_id"      => array('eq', 0),
//            "user_id"        => array('eq', 0),
//            "state"          => array('eq', 0),
            "code"           => $code,
        );
        $codeInfo = findDataWithCondition( "coupon_code" , $condition);

        /**
         * 特殊情况
         */
        if($codeInfo["gift_coupon_id"] == 3){
            $condition = array(
                "gift_coupon_id" => 3,
                "user_id"        => session(__UserID__),
                "state"          => array('neq', 0),
            );
            if( getCountWithCondition("coupon_code" ,$condition) > 0 ){
                return callback(false, "每人只有一次领取种子的机会!");
            }
        }



        if( !empty($codeInfo)){
            if( $codeInfo["user_id"] == 0 && $codeInfo["state"] == 0){
                return callback(true, "可用兑换码");
            }else{
                return callback(false, "兑换码已被领取");
            }
        }
//        if ( isExistenceDataWithCondition( "coupon_code" , $condition ) ) {
//            return callback(true, "可用兑换码");
//        }
        $condition = array(
            "uid"      => array('eq', 0),
            "order_id" => array('eq', 0),
            "code"     => $code,
        );
        if ( isExistenceDataWithCondition( "coupon_list" , $condition ) ) {
            return callback(true, "可用兑换码");
        }

    }
    return callback(false, "未找到兑换码");
}

/**
 * 修改礼品券兑换码使用状态
 * @param $code
 * @param $userId
 * @param null $orderId
 * @return array
 */
function changeCodeState( $code , $userId , $orderId = null )
{
    $where = array();
    $couponCodeData = array();
    $where['code'] = $code;
    $couponCodeData['state'] = 2;
    $couponCodeData['use_time'] = time();
    $couponCodeData['user_id'] = $userId;
    $couponCodeData['receive_time'] = time();
    if( !is_null($orderId) ){
        $couponCodeData['g_code_order_id'] = $orderId;
    }
    $result = M('coupon_code') -> where($where)->save($couponCodeData);
    if ($result > 0 || $result === 0) {
       return callback( true );
    }
    return callback( false , "修改券状态失败" );
}

/**
 * 获取兑换商品信息
 * @param $code
 * @return array
 */
function getExchangeGoodsList( $code ){
    $GiftCouponId = getGiftCouponId( $code );
    $condition = array(
        "gift_coupon_id"    =>  $GiftCouponId,
    );
    $goodsList = selectDataWithCondition( 'gift_coupon_goods_list' , $condition );
    if( !empty($goodsList) ){
        foreach ( $goodsList as $key => $goodsItem ){
            $goodsInfo = findDataWithCondition( "goods" , array( 'goods_id' => $goodsItem['goods_id'] ) ) ;
            if( empty($goodsInfo) ){
                unset( $goodsList[$key] );
            }
            $goodsList[$key]['market_price'] = $goodsInfo['market_price'];
            $goodsList[$key]['goods_price'] = $goodsInfo['shop_price'];
            $goodsList[$key]['member_goods_price'] = $goodsInfo['shop_price'];
        }
    }
    return $goodsList;
}
