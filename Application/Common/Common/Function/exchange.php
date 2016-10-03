<?php

/**
 * 查看 兑换码 是否存在 是否可用
 * @param $code
 * @return array
 */
function checkCode( $code  ){
    if( !empty($code) ){
        $condition = array(
            "gift_coupon_id"    =>  array('neq',0),
            "coupon_id"         =>  array('eq',0),
            "user_id"           =>  array('eq',0),
            "state"             =>  array('eq',0),
            "code"              =>  $code,
        );
        if( isExistenceDataWithCondition( "coupon_code" , $condition ) ){
            return callback( true , "可用兑换码" );
        }
    }
    return callback( false , "未找到兑换码" );
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
    $goodsList = M('gift_coupon_goods_list') -> where( $condition ) -> select();
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
