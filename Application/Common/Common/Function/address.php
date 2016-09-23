<?php


/**
 * 获取用户地址（单条）
 * @param $userId
 * @param null $addressId
 * @return mixed
 */
function getCurrentAddress( $userId , $addressId = null ){

    $condition = array(
        "user_id" => $userId,
    );

    if( !is_null($addressId) ){
        $condition['address_id'] = $addressId;
    }else{
        $condition['is_default'] = 1;
    }

    return M('user_address')->where($condition)->find();
}