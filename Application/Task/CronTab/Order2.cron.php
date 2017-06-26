<?php

/**
 * 订单类定时任务2
 */
class Order2CronClass
{
    public function init()
    {
        $work_time = intval(date("Hi"));
        $rand = rand(0,100);
        if ($work_time > "900" && $work_time < "2100") {
            if( $rand > 85){
                $sql = "SELECT r1.* 
 FROM lm_order AS r1 JOIN
    (SELECT ROUND(RAND() * 
           (SELECT MAX(order_id) 
            FROM lm_order)) AS order_id) 
    AS r2 
WHERE r1.order_id >= r2.order_id 
ORDER BY r1.order_id ASC
LIMIT 1;";
                $orderInfo = M("order")->query($sql);
                $orderInfo = $orderInfo[0];
                $data = $orderInfo;
                unset($data['order_id']);
                $data['order_sn'] = date('YmdHis') . rand(1000, 9999);
                $data['add_time'] = time();
                $data['pay_time'] = time();
                $data['shipping_status'] = 0;
                $data['order_status'] = 1;
                $data['pay_status'] = 1;
                $order_id = M("order")->add($data);
                $order_goods_list = selectDataWithCondition("order_goods", array("order_id" => $orderInfo["order_id"]));
                if (!empty($order_goods_list)) {
                    foreach ($order_goods_list as $order_goods_item) {
                        $data2 = $order_goods_item;
                        unset($data2['rec_id']);
                        $data2["order_id"] = $order_id;
                        isSuccessToAddData("order_goods", $data2);
                    }
                }
            }
            if( $rand > 20) {
                $nameLogic = new \Common\Logic\NameLogic();
                $nameLogic->rndChinaName();
                $map = array();
                $map['user_money'] = 0;
                $map['nickname'] = $nameLogic->getName(2);
                $map['reg_time'] = time();
                $map['mobile'] = "";
                $map['mobile_validated'] = 0;
                $map['oauth'] = "dingshi";
                $map['head_pic'] = "";
                $map['sex'] = 1;
                $userId = M('users')->add($map);
                dd($map);
            }
        }

    }
}

$orderCronClassObj = new Order2CronClass();
$orderCronClassObj->init();

