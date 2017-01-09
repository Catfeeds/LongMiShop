<?php
/**
 * 订单类定时任务
 */


class Order2CronClass
{
    public function init()
    {
        $orderInfo =  M("order")->order("rand()")->find();
        $data = $orderInfo;
        unset($data['order_id']);
        $data['order_sn'] = date('YmdHis').rand(1000,9999);
        $data['add_time'] = time();
        $data['pay_time'] = time();
        $data['shipping_status'] = 0;
        $data['order_status'] =1;
        $data['pay_status'] =1;
        $order_id = M("Order")->data($data)->add();
        $order_goods_list = selectDataWithCondition("order_goods",array("order_id"=>$orderInfo["order_id"]));
        if( !empty($order_goods_list)){
            foreach( $order_goods_list as $order_goods_item){
                $data2 = $order_goods_item;
                unset($data2['rec_id']);
                $data2["order_id"] = $order_id;
                isSuccessToAddData("order_goods" , $data2) ;
            }
        }
    }
}

$orderCronClassObj = new Order2CronClass();
$orderCronClassObj -> init();