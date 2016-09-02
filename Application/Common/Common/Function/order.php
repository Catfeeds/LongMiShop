<?php


/**
 * 订单进度条
 * @param $orderInfo
 * @return array
 */
function getOderProgressBar($orderInfo){
    $orderStatus    = $orderInfo[''];
    $shippingStatus = $orderInfo[''];
    $payStatus      = $orderInfo[''];

    $parameter = array(
        "speed" => 0 ,
        "first" => array(
            "show" => 1,
            "on"   => 0,
            "done" => 0,
            "date" => "",
            "time" => "",
            "name" => "下单",
        ),
        "second" => array(
            "show" => 1,
            "on"   => 0,
            "done" => 0,
            "date" => "",
            "time" => "",
            "name" => "付款",
        ),
        "third" => array(
            "show" => 1,
            "on"   => 0,
            "done" => 0,
            "date" => "",
            "time" => "",
            "name" => "已出库",
        ),
        "fourth" => array(
            "show" => 1,
            "on"   => 0,
            "done" => 0,
            "date" => "",
            "time" => "",
            "name" => "交易完成",
        ),
    );

    if( $orderStatus == 0 && $payStatus == 0){  //订单查询状态 待支付
        $parameter['speed'] = 0;
        $parameter['first']['on'] = 1;
        $parameter['first']['done'] = 1;
        $parameter['first']['date'] = date('Y-m-d' , $orderInfo['add_time']);
        $parameter['first']['time'] = date('H:i:s' , $orderInfo['add_time']);
    }
    if( ($orderStatus == 0 ||$orderStatus == 1) && $payStatus == 1 && $shippingStatus != 1){ //订单查询状态 待发货
        $parameter['speed'] = 33;
        $parameter['first']['done'] = 1;
        $parameter['first']['date'] = date('Y-m-d' , $orderInfo['add_time']);
        $parameter['first']['time'] = date('H:i:s' , $orderInfo['add_time']);
        $parameter['second']['on'] = 1;
        $parameter['second']['done'] = 1;
        $parameter['second']['date'] = date('Y-m-d' , $orderInfo['pay_time']);
        $parameter['second']['time'] = date('H:i:s' , $orderInfo['pay_time']);
    }
    if( $orderStatus == 1 && $shippingStatus == 1){  //订单查询状态 待收货
        $parameter['speed'] = 67;
        $parameter['first']['done'] = 1;
        $parameter['first']['date'] = date('Y-m-d' , $orderInfo['add_time']);
        $parameter['first']['time'] = date('H:i:s' , $orderInfo['add_time']);
        $parameter['second']['done'] = 1;
        $parameter['second']['date'] = date('Y-m-d' , $orderInfo['pay_time']);
        $parameter['second']['time'] = date('H:i:s' , $orderInfo['pay_time']);
        $parameter['third']['on'] = 1;
        $parameter['third']['done'] = 1;
        $parameter['third']['date'] = date('Y-m-d' , $orderInfo['shipping_time']);
        $parameter['third']['time'] = date('H:i:s' , $orderInfo['shipping_time']);
    }
    if( $orderStatus == 2 || $orderStatus == 4 ){  // 待评价 确认收货
        $parameter['speed'] = 100;
        $parameter['first']['done'] = 1;
        $parameter['first']['date'] = date('Y-m-d' , $orderInfo['add_time']);
        $parameter['first']['time'] = date('H:i:s' , $orderInfo['add_time']);
        $parameter['second']['done'] = 1;
        $parameter['second']['date'] = date('Y-m-d' , $orderInfo['pay_time']);
        $parameter['second']['time'] = date('H:i:s' , $orderInfo['pay_time']);
        $parameter['third']['done'] = 1;
        $parameter['third']['date'] = date('Y-m-d' , $orderInfo['shipping_time']);
        $parameter['third']['time'] = date('H:i:s' , $orderInfo['shipping_time']);
        $parameter['third']['done'] = 1;
        $parameter['third']['date'] = date('Y-m-d' , $orderInfo['shipping_time']);
        $parameter['third']['time'] = date('H:i:s' , $orderInfo['shipping_time']);
        $parameter['fourth']['on'] = 1;
        $parameter['fourth']['done'] = 1;
        $parameter['fourth']['date'] = date('Y-m-d' , $orderInfo['confirm_time']);
        $parameter['fourth']['time'] = date('H:i:s' , $orderInfo['confirm_time']);
    }
    if( $orderStatus == 3 ){  // 已取消
        $parameter['speed'] = 100;
        $parameter['first']['done'] = 1;
        $parameter['first']['date'] = date('Y-m-d' , $orderInfo['add_time']);
        $parameter['first']['time'] = date('H:i:s' , $orderInfo['add_time']);
        $parameter['second']['show'] = 0;
        $parameter['third']['show'] = 0;
        $parameter['fourth']['on'] = 1;
        $parameter['fourth']['done'] = 1;
        $parameter['fourth']['name'] = "订单关闭";
        $parameter['fourth']['date'] = date('Y-m-d' , $orderInfo['confirm_time']);
        $parameter['fourth']['time'] = date('H:i:s' , $orderInfo['confirm_time']);
    }
    return $parameter;

}