<?php


/**
 * 支付返回
 * @param $orderSn
 * @param $data
 */
function addonsPayNotify( $orderSn , $data )
{
    $orderInfo = findDataWithCondition("addons_fightgroups_order", array("order_sn" => $orderSn));
    if (!empty($orderInfo)) {
        if ($orderInfo["status"] != 0) {
            return;
        }
        saveData("addons_fightgroups_order", array("order_sn" => $orderSn), array('status' => 1, "pay_time" => time(), "pay_tag" => serialize($data)));
    }
}

/**
 * 获取支付数据
 * @param $orderId
 * @return array
 */
function addonsPayData( $orderId )
{
    $id = $orderId;
    $payData = array(
        "order"     => "",
        "goUrl"     => "",
        "backUrl"   => "",
        "notifyUrl" => "",
    );
    if ($_SESSION['openid'] && strstr($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger')) {
        $order = findDataWithCondition("addons_fightgroups_order", array("id" => $id));
        if (!empty($order)) {
            if ($order['status'] != 0) {
                header('Location: ' . U('Mobile/Addons/christmas', array('pluginName' => 'orderDetail')));
                exit;
            }
            $activityInfo = findDataWithCondition("addons_christmas_activity");
            $start_time = $activityInfo["start_time"];
            $end_time = $activityInfo["end_time"];
            if (time() < $start_time) {
                mobileJumpToast( U('Mobile/Addons/christmas', array('pluginName' => 'rule')) , null , "活动还未开始" );
                exit;
            }
            if ($end_time < time()) {
                mobileJumpToast( U('Mobile/Addons/christmas', array('pluginName' => 'rule')) , null , "活动已经结束" );
                exit;
            }
            $number = getCountWithCondition("addons_christmas_order", array("activity_id" => $activityInfo['id'],"status"=>array("neq","0")));
            if ($number >= $activityInfo['number']) {
                mobileJumpToast( U('Mobile/Addons/christmas', array('pluginName' => 'rule')) , null , "礼包名额已满" );
                exit;
            }
            $order["order_amount"] = $order["money"];
            $payData['order'] = $order;
            $payData['goUrl'] = U('Mobile/Addons/christmas', array("pluginName" => "results", "order_id" => $id));
            $payData['backUrl'] = U('Mobile/Addons/christmas', array("pluginName" => "payBack", "order_id" => $id));
            $payData['notifyUrl'] = SITE_URL . '/index.php/Api/Addons/christmas/pluginName/notifyUrl';
            return $payData;
        }
    }
    die("<script>history.go(-1);</script>");
}
