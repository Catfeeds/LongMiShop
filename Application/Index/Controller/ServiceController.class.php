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
        $count = M('return_goods')->where("user_id = '{$this->user_id}'") -> count();
        $page = new Page($count,10);
        $list = M('return_goods')->where("user_id = '{$this->user_id}'") -> order("id desc")->limit("{$page->firstRow},{$page->listRows}")->select();
        $goods_id_arr = get_arr_column($list, 'goods_id');
        $goodsList = array();
        if(!empty($goods_id_arr)){
            $goodsList = M('goods')->where("goods_id in (".  implode(',',$goods_id_arr).")")->getField('goods_id,goods_name');
        }
        $this -> assign('goodsList', $goodsList);
        $this -> assign('lists', $list);
        $this -> assign('page', $page->show());// 赋值分页输出
        $this -> display();
    }


    public function serviceDetail(){
        $id = I('get.id');
        $serviceOrderInfo = getServiceOrderInfo($id , $this->user_id);
        if( empty($serviceOrderInfo) ){
            $this->error('没有获取到服务单信息');
            exit;
        }
        $orderId = $serviceOrderInfo['order_id'];
        $orderLogic = new \Common\Logic\OrderLogic();
        $orderInfo =  $orderLogic -> getOrderInfo( $orderId , $this->user_id );
        if( empty($orderInfo) ){
            $this -> error('没有获取到订单信息');
            exit;
        }
        $progressBar = getServiceOrderProgressBar($serviceOrderInfo);
        $this -> assign('orderInfo',$orderInfo);
        $this -> assign('progressBar',$progressBar);
        $this -> assign('serviceOrderInfo',$serviceOrderInfo);
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

//        $goodsList = M('return_goods')->where("order_id = '{$orderInfo['order_id']}'")->getField('goods_id,goods_id');
//        foreach ($data as $key => $dataItem){
//            if( in_array($dataItem['goods_id'],$goodsList) ){
//                unset($data[$key]);
//            }
//        }
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
        $expressList = include_once 'Application/Common/Conf/express.php'; //快递名称
        $this -> assign('expressList',$expressList);
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
        $expressList = include_once 'Application/Common/Conf/express.php'; //快递名称
        $this -> assign('expressList',$expressList);
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