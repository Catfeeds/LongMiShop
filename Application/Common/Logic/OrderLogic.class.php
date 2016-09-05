<?php
namespace Common\Logic;

use Common\Logic\Base\BaseLogic;
use Think\Model;

class OrderLogic extends BaseLogic
{

    public $orderModel = null;

    public function __construct()
    {
        parent::__construct();
        $this -> orderModel = new Model('order');
    }


    //获取订单数据
    public function getOrderInfo($id , $userId = null){
        $condition['order_id'] = $id;
        if( is_null($userId) ){
            $condition['user_id'] = $userId;
        }
        return M('order')->where($condition)->find();
    }


    //获取订单商品
    public function getOrderGoods($order_id = null){
        if( is_null($order_id) ){
            return callback(false,'',array());
        }
        $sql = "SELECT og.*,g.original_img FROM __PREFIX__order_goods og LEFT JOIN __PREFIX__goods g ON g.goods_id = og.goods_id WHERE order_id = '".$order_id."'";
        $goods_list = $this->query($sql);
        return callback(true,'',$goods_list);
    }



    /**
     * 取消订单
     */
    public function cancelOrder($userId,$orderId){
        $order = M('order')->where(array('order_id'=>$orderId,'user_id'=>$userId))->find();
        //检查是否未支付订单 已支付联系客服处理退款
        if(empty($order)){
            return callback(false,'订单不存在','');
        }
        //检查是否未支付的订单
        if($order['pay_status'] > 0 || $order['order_status'] > 0){
            return callback(false,'支付状态或订单状态不允许');
        }
        //获取记录表信息
        //$log = M('account_log')->where(array('order_id'=>$order_id))->find();
        //有余额支付的情况
//        if($order['user_money'] > 0 || $order['integral'] > 0){
//            accountLog($userId,$order['user_money'],$order['integral'],"订单取消，退回{$order['user_money']}元,{$order['integral']}积分");
//        }

        $row = M('order')->where(array('order_id'=>$orderId,'user_id'=>$userId))->save(array('order_status'=>3));

        $data['order_id'] = $orderId;
        $data['action_user'] = $userId;
        $data['action_note'] = '您取消了订单';
        $data['order_status'] = 3;
        $data['pay_status'] = $order['pay_status'];
        $data['shipping_status'] = $order['shipping_status'];
        $data['log_time'] = time();
        $data['status_desc'] = '用户取消订单';
        M('order_action')->add($data);//订单操作记录

        if(!$row){
            return callback(false,'操作失败','');
        }
        return callback(true,'操作成功','');

    }

    public function confirmOrder($id){
        $order = M('order')->where(array('order_id'=>$id))->find();
        if($order['order_status'] != 1){
            return callback(false,'该订单不能收货确认','');
        }

        $data['order_status'] = 2; // 已收货
        $data['pay_status'] = 1; // 已付款
        $data['confirm_time'] = time(); //  收货确认时间
        $row = M('order')->where(array('order_id'=>$id))->save($data);
        if(!$row){
            return callback(false,'操作失败','');
        }
        order_give($order);// 调用送礼物方法, 给下单这个人赠送相应的礼物

        //分销设置
        M('rebate_log')->where("order_id = $id")->save(array('status'=>2));
        return callback(true,'操作成功','');
    }

}