<?php
/**
 * 订单类定时任务
 */


class OrderCronClass
{

    private $startTime = null;

    public function init()
    {
        define('ORDER_OVERDUE_TIME', 24*60*60); //设置过期时间
        $this -> startTime = time();
        $orderList = getOverdueOrder();
        if(!empty($orderList)){
            $orderLogic = new \Common\Logic\OrderLogic();
            foreach ($orderList as $orderItem){
                if( $this -> startTime - $orderItem['add_time'] >= ORDER_OVERDUE_TIME){
                    $orderLogic -> cancelOrder($orderItem['user_id'],$orderItem['order_id']);
                }
            }
        }
    }
}

$orderCronClassObj = new OrderCronClass();
$orderCronClassObj -> init();