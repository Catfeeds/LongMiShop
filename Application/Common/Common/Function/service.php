<?php


/**
 * 售后单进度条
 * @param $orderInfo
 * @return array
 */
function getServiceOrderProgressBar($serviceOrderInfo){
    $serviceOrderStatus    = $serviceOrderInfo['status'];

    $parameter = array(
        "speed" => 0 ,
        "first" => array(
            "show" => 1,
            "on"   => 0,
            "done" => 0,
            "date" => "",
            "time" => "",
            "name" => "申请中",
        ),
        "second" => array(
            "show" => 1,
            "on"   => 0,
            "done" => 0,
            "date" => "",
            "time" => "",
            "name" => "客服理中",
        ),
        "third" => array(
            "show" => 1,
            "on"   => 0,
            "done" => 0,
            "date" => "",
            "time" => "",
            "name" => "已完成",
        )
    );

    if( $serviceOrderStatus == 0){  //订单查询状态 待支付
        $parameter['speed'] = 0;
        $parameter['first']['on'] = 1;
        $parameter['first']['done'] = 1;
    }elseif( $serviceOrderStatus == 1 ){ //订单查询状态 待发货
        $parameter['speed'] = 33;
        $parameter['first']['on'] = 0;
        $parameter['first']['done'] = 1;
        $parameter['second']['on'] = 1;
        $parameter['second']['done'] = 1;
    }elseif( $serviceOrderStatus == 2){ //订单查询状态 待发货
        $parameter['speed'] = 33;
        $parameter['first']['on'] = 0;
        $parameter['first']['done'] = 1;
        $parameter['second']['on'] = 1;
        $parameter['second']['done'] = 1;
    }
    return $parameter;

}


/**
 * 获取退货单详情
 * @param $id
 * @param $userId
 * @return mixed
 */
function getServiceOrderInfo( $id , $userId = null){
    $condition = array(
        "id" => $id,
    );
    if( !is_null($userId) ){
        $condition["user_id"] = $userId;
    }
    return M('return_goods') -> where($condition) -> find();
}