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
        saveData("addons_christmas_order", array("order_sn" => $orderSn), array('status' => 1, "pay_time" => time(), "pay_tag" => serialize($data)));
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
        "url"   => "http://".$_SERVER["HTTP_HOST"].U("Mobile/Addons/christmas", array("pluginName" => "index", "activity_id" => $info["id"])),
    );
    !empty($info["wx_title"]) ? $array["title"] = $info["wx_title"] : false;
    !empty($info["wx_desc"]) ? $array["desc"] = $info["wx_desc"] : false;
    !empty($info["wx_shareimg"]) ? $array["img"] = "http://".$_SERVER["HTTP_HOST"].$info["wx_shareimg"] : false;
    if (!is_null($order_id) && $order_id > 0) {
        if(isExistenceDataWithCondition("addons_christmas_order",array("id"=>$order_id,"status"=>array("neq","0")))){
            $array["url"] = "http://".$_SERVER["HTTP_HOST"].U("Mobile/Addons/christmas", array("pluginName" => "shareInfo", "activity_id" => $info["id"], "order_id" => $order_id));
        }
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
    if( !empty($data)) {
        session("addons_christmas_order_id", $id);
        $data["goods"] = selectDataWithCondition("addons_christmas_order_goods", array("order_id" => $id));
        $data["status"] == 1 ? $data["tag"] = unserialize($data["wx_tag"]) : false;
        $data["status"] == 2 ? $data["getUserInfo"] = get_user_info($data["get_user_id"]) : false;
        $data["getList"] = selectDataWithCondition("addons_christmas_order_get_list", array("order_id" => $id));
        if (!empty($data["getList"])) {
            foreach ($data["getList"] as $getItem) {
                if ($getItem["type"] == 2) {
                    $couponInfo = findDataWithCondition("coupon",array("id"=>$getItem['get_id']),"name");
                    $getItem["coupon_name"] = $couponInfo["name"];
                } elseif ($getItem["type"] == 1) {

                } else {

                }
            }
        }

    }
    return $data;
}

//
///**
// * 中将部分
// * @param int $total
// * @return mixed
// */
//function addonsGetReward( $total=1000)
//{
//
//
//    $win1 = 0.1*$total;
//    $win2 = 0.05*$total;
//    $other = $total-$win1-$win2;
//
//    $count =  getCountWithCondition("addons_christmas_order_goods",array("status"=>"2","gift_type"=>1));
//    $count = intval($count);
//    $win1 -= $count;
//
//    $count =  getCountWithCondition("addons_christmas_order_goods",array("status"=>"2","gift_type"=>2));
//    $count = intval($count);
//    $win2 -= $count;
//
//    $count =  getCountWithCondition("addons_christmas_order_goods",array("status"=>"2","gift_type"=>3));
//    $count = intval($count);
//    $other -= $count;
//
//    $return = array();
//    for ($i=0;$i<$win1;$i++)
//    {
//        $return[] = 1;
//    }
//    for ($j=0;$j<$win2;$j++)
//    {
//        $return[] = 2;
//    }
//    for ($n=0;$n<$other;$n++)
//    {
//        $return[] = 3;
//    }
//    shuffle($return);
//    return $return[array_rand($return)];
//}


function  addonsNewGetReward($odds){
    $returnData = array();
    foreach ( $odds as $oddsKey => $oddsItem){
        $node = $oddsItem["chance"];
        $other = 100;
        $return = array();

        if(
            $oddsItem["number"] == 0 ||
            empty( $oddsItem["number"]) ||
            $oddsItem["number"] > getCountWithCondition("addons_christmas_order_get_list" ,array("get_key"=>$oddsKey))
        ){
            for ($i=0;$i<$node;$i++)
            {
                $other -- ;
                $return[] = array(
                    "isGet" => true,
                    "key" => intval($oddsKey),
                );
            }
        }
        for ($j=0;$j<$other;$j++)
        {
            $return[] = array(
                "isGet" => false,
                "key" => intval($oddsKey),
            );
            $return[] = false;
        }
        shuffle($return);
        $res =  $return[array_rand($return)]["isGet"];
        $key = null;
        $res2 = null;
        if( $res == true ){
            $key =  $return[array_rand($return)]["key"];
            if( !empty($oddsItem['in_odds'])){
                $return2 = array();
                foreach ($oddsItem['in_odds'] as $minOddsKey =>  $minOddsItem){
                    if(
                        $minOddsItem["number"] == 0 ||
                        empty( $minOddsItem["number"]) ||
                        $minOddsItem["number"] > getCountWithCondition("addons_christmas_order_get_list" ,array("get_key"=>$oddsKey,"get_key2"=>$minOddsKey))
                    ){
                        for ($j=0;$j<$minOddsItem["chance"];$j++)
                        {
                            $return2[] = intval($minOddsKey);
                        }
                    }
                }
                if( empty($return2)){
                    $res2 = null;
                    $res = false;
                }else{
                    shuffle($return2);
                    $res2 =  $return2[array_rand($return2)];
                }
            }
        }
        $returnData[$oddsKey]=array(
            "isGet" => $res,
            "key"=> $key,
            "key2"=> $res2,
        );
    }
    return $returnData;
}
