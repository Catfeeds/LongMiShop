<?php

//改变优惠券状态
if( $this -> _post_data['useCoupon'] == true && !empty($this -> _post_data['couponInfo'])){
    $condition = array(
        "id" => $this -> _post_data["userCouponId"],
        "uid" => $this -> user['user_id'],
        "order_id" => 0,
    );
    $save = array(
        "order_id" => $order["order_id"],
        "use_time" => $this -> nowTime,
    );

    $result = M('coupon_list') -> where($condition) -> save($save);
    if(empty($result)){
        throw new \Exception('优惠券使用失败！');
    }
}