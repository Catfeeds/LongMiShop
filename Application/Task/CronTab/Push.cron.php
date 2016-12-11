<?php

class PushCronClass
{

    private $thisTime = null;
    private $nineItem = null;

    public function init()
    {

        $current = time();
        $this->thisTime = date("Y-m-d", $current);
        $this->nineItem = date("H", $current);

        //设置过期订单
        saveData("addons_lunchfeast_order", array("date" => array("lt", $this->thisTime)), array("status" => 3));

        if ($this->nineItem == '09') {
            $orderInfo = M('addons_lunchfeast_order')->where(array('status' => 1))->select();
            foreach ($orderInfo as $item) {
                $orderTime = date('Y-m-d', $item['date']);
                if ($orderTime == $this->thisTime) {
                    $user = findDataWithCondition('users', array("user_id" => $item['user_id']), 'openid,mobile,mobile_validated');
                    $shopInfo = findDataWithCondition("addons_lunchfeast_shop", array('id' => $item['shop_id']), 'shop_name');
                    //$mealInfo = findDataWithCondition("addons_lunchfeast_meal_list",array('id'=>$orderInfo['meal_id']),'name');
                    $text = "今天中午12:30，宴午" . $shopInfo['shop_name'] . "期待您的大驾！人数：" . $item['number'];
                    $jsSdkLogic = new \Common\Logic\JsSdkLogic();
                    $jsSdkLogic->push_msg($user['openid'], $text);
                    if ($user["mobile_validated"] == 1 && $user["mobile"]) {
                        $mobileData = array(
                            "time"   => "中午12:30",
                            "place"  => "宴午" . $shopInfo['shop_name'],
                            "number" => $item['number']
                        );
                        sendMobileMessages($user["mobile"], $mobileData, "SMS_32650055");
                    }
                }
            }
        }
    }
}

$supplierCronClassObj = new PushCronClass();
$supplierCronClassObj -> init();