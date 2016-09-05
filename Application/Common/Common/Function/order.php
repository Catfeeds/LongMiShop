<?php


/**
 * 订单进度条
 * @param $orderInfo
 * @return array
 */
function getOderProgressBar($orderInfo){
    $orderStatus    = $orderInfo['order_status'];
    $shippingStatus = $orderInfo['shipping_status'];
    $payStatus      = $orderInfo['pay_status'];
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
    }elseif( $orderInfo['pay_code'] == 'cod' && ($orderStatus == 1 || $orderStatus ==0) && $shippingStatus == 0 ){ //订单查询状态 待发货
        $parameter['speed'] = 33;
        $parameter['first']['on'] = 0;
        $parameter['first']['done'] = 1;
        $parameter['first']['date'] = date('Y-m-d' , $orderInfo['add_time']);
        $parameter['first']['time'] = date('H:i:s' , $orderInfo['add_time']);
        $parameter['second']['on'] = 1;
        $parameter['second']['done'] = 1;
        $parameter['second']['date'] = date('Y-m-d' , $orderInfo['pay_time']);
        $parameter['second']['time'] = date('H:i:s' , $orderInfo['pay_time']);
    }elseif(($orderStatus == 0 ||$orderStatus == 1) && $payStatus == 1 && $shippingStatus != 1){ //订单查询状态 待发货
        $parameter['speed'] = 33;
        $parameter['first']['on'] = 0;
        $parameter['first']['done'] = 1;
        $parameter['first']['date'] = date('Y-m-d' , $orderInfo['add_time']);
        $parameter['first']['time'] = date('H:i:s' , $orderInfo['add_time']);
        $parameter['second']['on'] = 1;
        $parameter['second']['done'] = 1;
        $parameter['second']['date'] = date('Y-m-d' , $orderInfo['pay_time']);
        $parameter['second']['time'] = date('H:i:s' , $orderInfo['pay_time']);
    }elseif( $orderStatus == 1 && $shippingStatus == 1){  //订单查询状态 待收货
        $parameter['speed'] = 67;
        $parameter['first']['on'] = 0;
        $parameter['first']['done'] = 1;
        $parameter['first']['date'] = date('Y-m-d' , $orderInfo['add_time']);
        $parameter['first']['time'] = date('H:i:s' , $orderInfo['add_time']);
        $parameter['second']['on'] = 0;
        $parameter['second']['done'] = 1;
        $parameter['second']['date'] = date('Y-m-d' , $orderInfo['pay_time']);
        $parameter['second']['time'] = date('H:i:s' , $orderInfo['pay_time']);
        $parameter['third']['on'] = 1;
        $parameter['third']['done'] = 1;
        $parameter['third']['date'] = date('Y-m-d' , $orderInfo['shipping_time']);
        $parameter['third']['time'] = date('H:i:s' , $orderInfo['shipping_time']);
    }elseif( $orderStatus == 2 || $orderStatus == 4 ){  // 待评价 确认收货
        $parameter['speed'] = 100;
        $parameter['first']['on'] = 0;
        $parameter['first']['done'] = 1;
        $parameter['first']['date'] = date('Y-m-d' , $orderInfo['add_time']);
        $parameter['first']['time'] = date('H:i:s' , $orderInfo['add_time']);
        $parameter['second']['on'] = 0;
        $parameter['second']['done'] = 1;
        $parameter['second']['date'] = date('Y-m-d' , $orderInfo['pay_time']);
        $parameter['second']['time'] = date('H:i:s' , $orderInfo['pay_time']);
        $parameter['third']['on'] = 0;
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
    }elseif( $orderStatus == 3 ){  // 已取消
        $parameter['speed'] = 100;
        $parameter['first']['on'] = 0;
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




/**
 * 给订单数组添加属性  包括按钮显示属性 和 订单状态显示属性
 * @param $order
 * @return array
 */
function set_btn_order_status($order)
{
    $order_status_arr = C('ORDER_STATUS_DESC');
    $order['order_status_code'] = $order_status_code = orderStatusDesc(0, $order); // 订单状态显示给用户看的
    $order['order_status_desc'] = $order_status_arr[$order_status_code];
    $orderBtnArr = orderBtn(0, $order);
    return array_merge($order,$orderBtnArr); // 订单该显示的按钮
}


/**
 * 获取订单状态的 中文描述名称
 * @param int $order_id
 * @param array $order 订单数组
 * @return string
 */
function orderStatusDesc($order_id = 0, $order = array())
{
    if(empty($order)){
        $order = M('Order')->where("order_id = $order_id")->find();
    }
    // 货到付款
    if($order['pay_code'] == 'cod')
    {
        if(in_array($order['order_status'],array(0,1)) && $order['shipping_status'] == 0)
            return 'WAITSEND'; //'待发货',
    }
    else // 非货到付款
    {
        if($order['pay_status'] == 0 && $order['order_status'] == 0)
            return 'WAITPAY'; //'待支付',
        if($order['pay_status'] == 1 &&  in_array($order['order_status'],array(0,1)) && $order['shipping_status'] != 1)
            return 'WAITSEND'; //'待发货',
    }
    if(($order['shipping_status'] == 1) && ($order['order_status'] == 1))
        return 'WAITRECEIVE'; //'待收货',
    if($order['order_status'] == 2)
        return 'WAITCCOMMENT'; //'待评价',
    if($order['order_status'] == 3)
        return 'CANCEL'; //'已取消',
    if($order['order_status'] == 4)
        return 'FINISH'; //'已完成',
    return 'OTHER';
}



/**
 * 获取订单状态的 显示按钮
 * @param int $order_id
 * @param array $order
 * @return array
 */
function orderBtn($order_id = 0, $order = array())
{
    if(empty($order))
        $order = M('Order')->where("order_id = $order_id")->find();
    /**
     *  订单用户端显示按钮
    去支付     AND pay_status=0 AND order_status=0 AND pay_code ! ="cod"
    取消按钮  AND pay_status=0 AND shipping_status=0 AND order_status=0
    确认收货  AND shipping_status=1 AND order_status=0
    评价      AND order_status=1
    查看物流  if(!empty(物流单号))
     */
    $btn_arr = array(
        'pay_btn' => 0, // 去支付按钮
        'cancel_btn' => 0, // 取消按钮
        'receive_btn' => 0, // 确认收货
        'comment_btn' => 0, // 评价按钮
        'shipping_btn' => 0, // 查看物流
        'return_btn' => 0, // 退货按钮 (联系客服)
    );


    // 货到付款
    if($order['pay_code'] == 'cod')
    {
        if(($order['order_status']==0 || $order['order_status']==1) && $order['shipping_status'] == 0) // 待发货
        {
            $btn_arr['cancel_btn'] = 1; // 取消按钮 (联系客服)
        }
        if($order['shipping_status'] == 1 && $order['order_status'] == 1) //待收货
        {
            $btn_arr['receive_btn'] = 1;  // 确认收货
            $btn_arr['return_btn'] = 1; // 退货按钮 (联系客服)
        }
    }
    // 非货到付款
    else
    {
        if($order['pay_status'] == 0 && $order['order_status'] == 0) // 待支付
        {
            $btn_arr['pay_btn'] = 1; // 去支付按钮
            $btn_arr['cancel_btn'] = 1; // 取消按钮
        }
        if($order['pay_status'] == 1 && in_array($order['order_status'],array(0,1)) && $order['shipping_status'] == 0) // 待发货
        {
            $btn_arr['return_btn'] = 1; // 退货按钮 (联系客服)
        }
        if($order['pay_status'] == 1 && $order['order_status'] == 1  && $order['shipping_status'] == 1) //待收货
        {
            $btn_arr['receive_btn'] = 1;  // 确认收货
            $btn_arr['return_btn'] = 1; // 退货按钮 (联系客服)
        }
    }
    if($order['order_status'] == 2)
    {
        $btn_arr['comment_btn'] = 1;  // 评价按钮
        $btn_arr['return_btn'] = 1; // 退货按钮 (联系客服)
    }
    if($order['shipping_status'] != 0)
    {
        $btn_arr['shipping_btn'] = 1; // 查看物流
    }
    if($order['shipping_status'] == 2 && $order['order_status'] == 1) // 部分发货
    {
        $btn_arr['return_btn'] = 1; // 退货按钮 (联系客服)
    }

    return $btn_arr;
}

