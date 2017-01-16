<?php

/**
 * 支付返回
 * @param $orderSn
 * @param $data
 */
function addonsPayNotify( $orderSn , $data ){
    $orderInfo = findDataWithCondition( "addons_lunchfeast_order" , array( "order_sn" => $orderSn ) );
    if( !empty( $orderInfo ) ){
        if( $orderInfo["status"] != 0 ){
            return;
        }
        $add = array(
            "order_id" => $orderInfo["id"],
            "user_id" => $orderInfo["user_id"],
            "openid" => $data["openid"],
            "create_time" => time(),
            "pay_time" => time(),
            "money" => $data["total_fee"]/100,
            "tag" => serialize( $data ),
            "status" => 1,
        );
        addData( "addons_lunchfeast_order_pay_log" , $add );
        $payLogList =selectDataWithCondition( "addons_lunchfeast_order_pay_log" , array('order_id' =>$orderInfo["id"] , "status" => 1 )  , "money");
        $money = 0;
        foreach ($payLogList as $payLogItem){
            $money += $payLogItem["money"];
        }
        if( $orderInfo["pay_amount"] <= $money ){
            saveData( "addons_lunchfeast_order" ,  array( "order_sn" => $orderSn ) , array( 'status' => 1 ,"pay_time" => time()));
            //邀请人奖励
            lunchFeastGiveInviteGift( $orderInfo['user_id'] );
            //宴午推送
            lunchFeastWeChatSend($orderInfo);
        }
    }
}

/**
 * 获取支付数据
 * @param $orderId
 * @return array
 */
function addonsPayData( $orderId ){
    $id = $orderId;
    $payData = array(
        "order" => "",
        "goUrl" => "",
        "backUrl" => "",
        "notifyUrl" => "",
    );
    if( $_SESSION['openid'] && strstr($_SERVER['HTTP_USER_AGENT'],'MicroMessenger')) {
        $order = findDataWithCondition( "addons_lunchfeast_order" , array("id" => $id));
        if (!empty($order)) {
            if($order['status'] != 0){
                header('Location: '.U('Mobile/Addons/lunchFeast',array('pluginName'=>'orderList')));
                exit;
            }
            $order["order_amount"] = $order["pay_amount"];
            $payData['order'] = $order;
            $payData['goUrl'] = U('Mobile/Addons/lunchFeast', array("pluginName" => "results" , "id" => $id ) );
            $payData['backUrl'] = U('Mobile/Addons/lunchFeast', array("pluginName" => "payBack" , "id" => $id ));
            $payData['notifyUrl'] =  SITE_URL.'/index.php/Api/Addons/lunchFeast/pluginName/notifyUrl';
            return $payData;
        }
    }
    die("<script>history.go(-1);</script>");
}
