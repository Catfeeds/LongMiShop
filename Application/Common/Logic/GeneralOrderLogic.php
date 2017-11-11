<?php
/**
 * Created by PhpStorm.
 * User: zhonght
 * Date: 2017/11/2
 * Time: 22:35
 */
namespace Common\Logic;

use Think\Model;

class GeneralOrderLogic extends Model
{

    /**
     * 生成统一订单
     * @param $money  【价格】
     * @param $userId 【用户id】
     * @param $fulfillmentTag 【实现标签】
     * @param $fulfillmentId 【实现关联ID】
     * @param $sign 【标识】
     * @return array
     */
    public function createOrder($money, $userId, $fulfillmentTag, $fulfillmentId, $sign)
    {
        $genericOrder = M("generic_order");
        try {
            $genericOrder->startTrans();
            $data = array(
                "amount" => $money,
                "user_id" => $userId,
                "fulfillment_status" => 0,
                "pay_status" => 0,
                "fulfillment_type" => $fulfillmentTag,
                "fulfillment_id" => $fulfillmentId,
                "payment_method" => "",
                "name" => $sign
            );
            $genericOrderId = $genericOrder->add($data);
            $genericOrder->commit();
            return callback(true, '创建升级订单成功', $genericOrderId);
        } catch (\Exception $e) {
            $genericOrder->rollback();
            return callback(false, $e->getMessage());
        }
    }


    public function toWeChatPay(){
        include_once  "plugins/payment/weixin/weixin.class.php";
        $this->payment = new \weixin();
    }
}
