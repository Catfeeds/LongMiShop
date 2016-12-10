<?php



/**
 * 支付返回
 * @param $orderSn
 * @param $data
 */
function addonsPayNotify( $orderSn , $data )
{
    $orderInfo = findDataWithCondition("addons_christmas_order", array("order_sn" => $orderSn));
    if (!empty($orderInfo)) {
        if ($orderInfo["status"] != 0) {
            return;
        }
        saveData("addons_lunchfeast_order", array("order_sn" => $orderSn), array('status' => 1, "pay_time" => time(), "pay_tag" => serialize($data)));
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
        $order = findDataWithCondition("addons_christmas_order", array("id" => $id));
        if (!empty($order)) {
            if ($order['status'] != 0) {
                header('Location: ' . U('Mobile/Addons/christmas', array('pluginName' => 'orderDetail')));
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

/**
 * 获取活动详情
 * * @param $id
 * @return mixed
 */
function addonsGetActivityInfo( $id = 1 )
{
    $condition = array("id" => $id);
    $activity = findDataWithCondition("addons_christmas_activity", $condition);
    if (!empty($activity)) {
        $condition = array("activity_id" => $id);
        $activity["goods"] = selectDataWithCondition("addons_christmas_activity_goods", $condition);
    }
    return $activity;
}


/**
 * 获取微信分享函数
 * @param $info
 * @param null $order_id
 * @return array
 */
function addonsGetShareArray( $info , $order_id = null  )
{
    $array = array(
        "title" => "圣诞活动",
        "desc"  => "圣诞活动",
        "img"   => "圣诞活动",
        "url"   => U("Mobile/Addons/christmas", array("pluginName" => "index", "activity_id" => $info["id"])),
    );
    !empty($info["wx_title"]) ? $array["title"] = $info["wx_title"] : false;
    !empty($info["wx_desc"]) ? $array["desc"] = $info["wx_desc"] : false;
    !empty($info["wx_shareimg"]) ? $array["img"] = $info["wx_shareimg"] : false;
    if (!is_null($order_id) && $order_id > 0) {
        $array["url"] = U("Mobile/Addons/christmas", array("pluginName" => "shareInfo", "activity_id" => $info["id"], "order_id" => $order_id));
    }
    return $array;

}


/**
 * 获取订单详情
 * @param $id
 * @return mixed
 */
function addonsGetOrderInfo( $id)
{
    $data = findDataWithCondition("addons_christmas_order", array("id" => $id));
    if( !empty($data)){
        session("addons_christmas_order_id",$id);
        $data["goods"] = selectDataWithCondition("addons_christmas_order_goods",array("order_id"=>$id));
        $data["status"] == 1 ? $data["tag"] = unserialize($data["wx_tag"]) : false ;
        $data["status"] == 2 ? $data["getUserInfo"] = get_user_info( $data["get_user_id"] ) : false ;
    }
    return $data;
}