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
    $giftCouponId = getGiftCouponId( $code );
    $condition = array(
        "gift_coupon_id"    =>  $giftCouponId,
    );
    $goodsList = M('gift_coupon_goods_list') -> where( $condition ) -> select();
    if( !empty($goodsList) ){
        foreach ( $goodsList as $key => $goodsItem ){
            if( !isExistenceDataWithCondition( "goods" , array( 'goods_id' => $goodsItem ) ) ){
                unset( $goodsList[$key] );
            }
        }
    }
//    return $goodsList;
//    return M('gift_coupon_goods_list') -> where( $condition ) -> select();
    return array(
        array(
            'goods_id'        => 66,   // 商品id
            'admin_id'        => 1,
            'goods_sn'        => "",   // 商品货号
            'goods_name'      => "迎馨家纺全棉斜纹印花双人四件套邂逅 AB版纯棉，亲肤透气",   // 商品名称
            'market_price'    => 100,   // 市场价
            'goods_price'     => 100,  // 购买价
            'member_goods_price' => 100,  // 会员折扣价 默认为 购买价
            'goods_num'       => 6, // 购买数量
            'spec_key'        => "", // 规格key
            'spec_key_name'   => "", // 规格 key_name
            'sku'             => "", // 商品条形码
            'add_time'        => time(), // 加入购物车时间
            'prom_type'       => 0,   // 0 普通订单,1 限时抢购, 2 团购 , 3 促销优惠
            'prom_id'         => 0,   // 活动id
        ),
    );
}
