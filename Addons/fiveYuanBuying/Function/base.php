<?php

/**
 * 支付返回
 * @param $orderSn
 * @param $data
 */
function addonsPayNotify( $orderSn , $data )
{
    $condition = array("order_sn" => $orderSn);
    $orderInfo = findDataWithCondition("addons_fiveyuanbuying_order", $condition);
    if (!empty($orderInfo)) {
        if ($orderInfo["status"] != 0) {
            return;
        }
        $save = array(
            'status'   => 1,
            "pay_time" => time(),
            "pay_tag"  => serialize($data),
        );
        saveData("addons_fiveyuanbuying_order", $condition, $save);

        /**
         * 送券
         */
        $giftList = selectDataWithCondition("addons_fiveyuanbuying_gift");
        if (!empty($giftList)) {
            foreach ($giftList as $giftItem) {
                for ($i = 1; $i <= $giftItem["number"]; $i++) {
                    addNewCoupon($giftItem['coupon_id'], $orderInfo['user_id'], 3, false);
                }
            }
        }
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
        $order = findDataWithCondition("addons_fiveyuanbuying_order", array("id" => $id));
        if (!empty($order)) {
            if ($order['status'] != 0) {
                header('Location: ' . U('Mobile/Activity/fiveYuanBuying'));
                exit;
            }
            $order["order_amount"] = $order["money"];
            $payData['order'] = $order;
            $payData['goUrl'] = U('Mobile/Activity/fiveYuanBuyingPayOk');
            $payData['backUrl'] = U('Mobile/Activity/fiveYuanBuying');
            $payData['notifyUrl'] = SITE_URL . '/index.php/Api/Addons/fiveYuanBuying/pluginName/notifyUrl';
            return $payData;
        }
    }
    die("<script>history.go(-1);</script>");
}
