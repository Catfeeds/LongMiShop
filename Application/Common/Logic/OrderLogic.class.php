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
        if( !is_null($userId) ){
            $condition['user_id'] = $userId;
        }
        return M('order')->where($condition)->find();
    }


    //获取订单商品
    public function getOrderGoods($order_id = null ){
        if( is_null($order_id) ){
            return callback(false,'',array());
        }
        $sql = "SELECT og.*,g.original_img FROM __PREFIX__order_goods og LEFT JOIN __PREFIX__goods g ON g.goods_id = og.goods_id WHERE order_id = '".$order_id."'";
        $goods_list = $this->query($sql);
        return callback(true,'',$goods_list);
    }



    /**
     * 取消订单
     * @param $userId
     * @param $orderId
     * @param $isOverdue
     * @return array
     */
    public function cancelOrder($userId,$orderId,$isOverdue = false){
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




        //退回优惠券
        $condition = array(
            "order_id" => $orderId,
            "uid" => $userId
        );
        if( isExistenceDataWithCondition('coupon_list',$condition) ){
            $save = array(
                "order_id" => 0,
                "use_time" => "",
            );
            $result = M('coupon_list') -> where($condition) -> save($save);
            if(empty($result)){
                return callback(false,'操作失败','');
            }
        }



        $data['order_id'] = $orderId;
        $data['action_user'] = $userId;
        $data['action_note'] = '您取消了订单';
        $data['order_status'] = 3;
        $data['pay_status'] = $order['pay_status'];
        $data['shipping_status'] = $order['shipping_status'];
        $data['log_time'] = time();
        $data['action_note'] = '您取消了订单';
        $data['status_desc'] = '用户取消订单';
        if($isOverdue){
            $data['action_note'] = '订单过期';
            $data['status_desc'] = '系统取消订单';
        }

        M('order_action')->add($data);//订单操作记录
        $row = M('order')->where(array('order_id'=>$orderId,'user_id'=>$userId))->save(array('order_status'=>3));

        if(!$row){
            return callback(false,'操作失败','');
        }
        //订单数量加回库存
        minus_stock($orderId,$stockBuildup = 1);
        return callback(true,'操作成功','');

    }

    public function confirmOrder($id){
        $data = confirm_order($id);
        if( !$data['status'])
            return callback(false,$data['msg']);
        else
            return callback(true,'操作成功','');
    }


    /**
     * 通过rec_id找订单详情
     * @param $recId
     * @param $userId
     * @param $needGoodsList
     * @return array
     */
    public function getOrderInfoByRecId( $recId ,$userId ,$needGoodsList = false){
        $condition = array();
        $condition["rec_id"] = $recId;
        $orderGoodsInfo = M('order_goods')->where( $condition )->find();
        if( empty($orderGoodsInfo) ){
            return callback(false,'找不到订单商品');
        }
        $orderId = $orderGoodsInfo['order_id'];
        $orderInfo = $this -> getOrderInfo( $orderId , $userId );
        if( empty($orderInfo) ){
            return callback(false,'找不到订单');
        }
        $orderInfo['recGoodsInfo'] = $orderGoodsInfo;
        if( $needGoodsList == true ){
            $result = $this -> getOrderGoods( $orderId );
            if( callbackIsTrue($result) ){
                $orderInfo['goodsList'] = $result['data'];
            }
        }
        return callback(true,'',$orderInfo);
    }





}