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

}