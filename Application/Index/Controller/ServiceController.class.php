<?php
namespace Index\Controller;

use Common\Common\Page;

class ServiceController extends BaseIndexController {

    function exceptAuthActions()
    {
        return null;
    }

    public function _initialize() {
        parent::_initialize();
    }

    public function returnGoodsList(){
        $count = M('return_goods')->where("user_id = '{$this->user_id}'") -> group('order_id')->select();
        $count = count($count);
        $page = new Page($count,10);
        $list = M('return_goods')->where("user_id = '{$this->user_id}'")  -> group('order_id')->order("id desc")->limit("{$page->firstRow},{$page->listRows}")->select();
        dd($list);
        $goods_id_arr = get_arr_column($list, 'goods_id');
        $goodsList = array();
        if(!empty($goods_id_arr)){
            $goodsList = M('goods')->where("goods_id in (".  implode(',',$goods_id_arr).")")->getField('goods_id,goods_name');
        }
        $this -> assign('goodsList', $goodsList);
//        dd($list);
        $this -> assign('lists', $list);
        $this -> assign('page', $page->show());// 赋值分页输出
        $this -> display();
    }


    public function applicationService(){
        $id = I('get.order_id');
        $orderLogic = new \Common\Logic\OrderLogic();
        $orderInfo =  $orderLogic -> getOrderInfo( $id , $this->user_id );
        if(!$orderInfo){
            $this -> error('没有获取到订单信息');
            exit;
        }
        $data = $orderLogic -> getOrderGoods($orderInfo['order_id']);
        $orderInfo['goods_list'] = $data['data'];
        $this -> assign('orderInfo',$orderInfo);
        $this -> display();
    }


    public function refund(){
        $id = I('get.id');
        $orderLogic = new \Common\Logic\OrderLogic();
        $result =  $orderLogic -> getOrderInfoByRecId( $id , $this->user_id );
        if( !callbackIsTrue($result) ){
            $this -> error( $result['msg'] );
            exit;
        }
        $orderInfo = $result['data'];
        $this -> assign('recId',$id);
        $this -> assign('orderInfo',$orderInfo);
        $this -> display();
    }

    public function exchange(){
        $id = I('get.id');
        $orderLogic = new \Common\Logic\OrderLogic();
        $result =  $orderLogic -> getOrderInfoByRecId( $id , $this->user_id );
        if( !callbackIsTrue($result) ){
            $this -> error( $result['msg'] );
            exit;
        }
        $orderInfo = $result['data'];
        $this -> assign('recId',$id);
        $this -> assign('orderInfo',$orderInfo);
        $this -> display();
    }

    public function applicationFinish(){
        if(IS_POST){
            $serviceLogic = new \Common\Logic\BuyLogic();
            $result =  $serviceLogic -> createServiceOrder();
            if( !callbackIsTrue($result) ){
                $this -> error( $result['msg'] );
                exit;
            }
            $this -> success( $result['msg'] );
            exit;
        }
        $id = I('get.id');
        $orderLogic = new \Common\Logic\OrderLogic();
        $result =  $orderLogic -> getOrderInfoByRecId( $id , $this->user_id );
        if( !callbackIsTrue($result) ){
            $this -> error( $result['msg'] );
            exit;
        }
        $orderInfo = $result['data'];
        $this -> assign('orderInfo',$orderInfo);
        $this -> display();
    }
}