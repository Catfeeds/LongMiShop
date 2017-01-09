<?php
/**
 * 积分类定时任务
 */


class PointsCronClass
{

    public function init()
    {
        /**
         *  3 降 2
         */
        $time = strtotime(date("Y-m-d", strtotime("-1 month")));
        $condition = array(
            "level" => "3",
            "last_buy_time" => array("lt",$time),
        );
        $save = array(
            "level" => 2,
            "discount" => 0.95,
            "upgrade_time" => time()
        );
        $userList = selectDataWithCondition("users",$condition);
        saveData("users",$condition,$save);
        foreach ( $userList as $userItem){
            increasePoints("downgrade2", $userItem["user_id"]);
            $condition2 = array(
                "user_id" => $userItem["user_id"],
                "pay_status" => "0"
            );
            $orderList = selectDataWithCondition("order",$condition2);
            if( !empty($orderList)){
                foreach ( $orderList as $orderItem){
                    $condition3 = array("order_id" => $orderItem["order_id"]);
                    $orderGoods = selectDataWithCondition("order_goods",$condition3);
                    if( !empty($orderGoods)){
                        $money2 = 0;
                        foreach ( $orderGoods as $orderGoodsItem){
                            $save2 = array(
                                "member_goods_price" => $orderGoodsItem["goods_price"]* 0.95
                            );
                            $money2 += $save2['member_goods_price']*$orderGoodsItem["goods_num"];
                            saveData("order_goods",array('rec_id' => $orderGoodsItem["rec_id"]),$save2);
                        }
                        $save3 = array(
                            "total_amount" => $money2 + $orderItem['shipping_price'],
                            "goods_price" => $money2,
                            "order_amount" => $money2 + $orderItem['shipping_price'] - $orderItem['coupon_price'] ,
                        );
                        saveData("order",$condition3,$save3);
                    }
                }
            }
            M('cart')->execute("update `__PREFIX__cart` set member_goods_price = goods_price * 0.95 where (user_id ='".$userItem["user_id"]."')");

        }


        /**
         * 清空积分部分
         */
        $condition = array(
            "level" => array("in","2,3"),
            "points_clear_time" => array("lt",time()),
        );
        $save = array(
            "level" => 1,
            "points_clear_time" => 0,
            "discount" => 1,
            "upgrade_time" => time()
        );
        $userList = selectDataWithCondition("users",$condition);
        saveData("users",$condition,$save);
        foreach ( $userList as $userItem){
            userDowngrade($userItem["user_id"]);
            
            $condition2 = array(
                "user_id" => $userItem["user_id"],
                "pay_status" => "0"
            );
            $orderList = selectDataWithCondition("order",$condition2);
            if( !empty($orderList)){
                foreach ( $orderList as $orderItem){
                    $condition3 = array("order_id" => $orderItem["order_id"]);
                    $orderGoods = selectDataWithCondition("order_goods",$condition3);
                    if( !empty($orderGoods)){
                        $money2 = 0;
                        foreach ( $orderGoods as $orderGoodsItem){
                            $save2 = array(
                                "member_goods_price" => $orderGoodsItem["goods_price"]
                            );
                            $money2 += $save2['member_goods_price']*$orderGoodsItem["goods_num"];
                            saveData("order_goods",array('rec_id' => $orderGoodsItem["rec_id"]),$save2);
                        }
                        $save3 = array(
                            "total_amount" => $money2 + $orderItem['shipping_price'],
                            "goods_price" => $money2,
                            "order_amount" => $money2 + $orderItem['shipping_price'] - $orderItem['coupon_price'] ,
                        );
                        saveData("order",$condition3,$save3);
                    }
                }
            }
            M('cart')->execute("update `__PREFIX__cart` set member_goods_price = goods_price where (user_id ='".$userItem["user_id"]."')");

        }
    }

}

$PointsCronClassObj = new PointsCronClass();
$PointsCronClassObj -> init();