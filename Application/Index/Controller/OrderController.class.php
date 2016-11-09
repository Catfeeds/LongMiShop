<?php
namespace Index\Controller;

use Common\Logic\UsersLogic;
use Common\Common\Page;
class OrderController extends IndexBaseController {

    function exceptAuthActions()
    {
        return null;
    }

    public function _initialize() {
        parent::_initialize();
    }

    public function orderList(){
        $where = ' user_id='.$this->user_id;
        //条件搜索
        if(I('get.type')){
            $where .= C(strtoupper(I('get.type')));
        }
        //订单状态条件
        $status = I('get.status','','int');
        $status = !empty($status) ? $status : 0;
        if($status ==2){
            $where .= " AND order_status IN(0,1)";
        }else if($status == 3){
            $where .= C('CANCEL');
        }else if($status == 4){
            $where .= " AND order_status IN(2,4)";
        }

        //订单时间条件
        $add_time  = I('get.time');
        $add_time = !empty($add_time) ? $add_time : 'trimester';
        if($add_time == 'trimester'){ //前三个月
            $tiem = strtotime('-3 months');
            $where .= " AND add_time >= '".$tiem."'"; 
        }else if($add_time == 'thisyear'){ //今年
            $tiem = strtotime(date('Y').'-01-01 00:00:00');
            $where .= " AND add_time >= '".$tiem."'";
        }else if($add_time == 'lastyear'){ //去年
            $tiem = strtotime(date('Y').'-01-01 00:00:00'); 
            $tiem_lastyear = strtotime(date('Y',time()) - 1 .'-01-01 00:00:00' ); 
            $where .= " AND add_time >= ' ".$tiem_lastyear." ' "." AND add_time < ' ".$tiem." ' ";
        }else if($add_time == 'yearbefore'){ //前年
            $tiem_lastyear = strtotime(date('Y',time()) - 1 .'-01-01 00:00:00'); 
            $tiem_yearbefore = strtotime(date('Y',time()) - 2 .'-01-01 00:00:00');  
            $where .= " AND add_time >= '".$tiem_yearbefore."'"." AND add_time < '".$tiem_lastyear."'";
        }



//        // 搜索订单 根据商品名称 或者 订单编号
//        $search_key = trim(I('search_key'));
//        if($search_key)
//        {
//            $where .= " and (order_sn like '%$search_key%' or order_id in (select order_id from `".C('DB_PREFIX')."order_goods` where goods_name like '%$search_key%') ) ";
//        }

        $count = M('order') -> where($where)->count();
        $Page = new Page($count,5);

        $show = $Page -> show();
        $order_str = "order_id DESC";
        $order_list = M('order')->order($order_str) -> where($where)->limit($Page->firstRow.','.$Page->listRows)->select();
        //获取订单商品
        $model = new UsersLogic();
        foreach($order_list as $k=>$v)
        {
            $order_list[$k] = setBtnOrderStatus($v,"INDEX");  // 添加属性  包括按钮显示属性 和 订单状态显示属性
            //$order_list[$k]['total_fee'] = $v['goods_amount'] + $v['shipping_fee'] - $v['integral_money'] -$v['bonus'] - $v['discount']; //订单总额
            $result = $model -> getOrderGoods($v['order_id']);
            $order_list[$k]['goods_list'] = getCallbackData( $result );
        }
        $this -> assign('order_status',C('ORDER_STATUS'));
        $this -> assign('shipping_status',C('SHIPPING_STATUS'));
        $this -> assign('pay_status',C('PAY_STATUS'));
        $this -> assign('page',$show);
        $this -> assign('lists',$order_list);
        $this -> assign('active','order_list');
        $this -> assign('active_status',I('get.type'));
        $this -> assign('status',$status);
        $this -> assign('times',$add_time);
        $this -> display();
    }


    //订单详情
    public function orderDetail(){
        $id = I('get.id');
        $orderLogic = new \Common\Logic\OrderLogic();
        $orderInfo =  $orderLogic -> getOrderInfo( $id , $this->user_id );
        if(!$orderInfo){
            $this->error('没有获取到订单信息');
            exit;
        }
        $data = $orderLogic -> getOrderGoods($orderInfo['order_id']);
        $orderInfo['goods_list'] = $data['data'];
        foreach($orderInfo['goods_list'] as $keys=>$items){
            if($items['is_send'] == 1){
                $deliveryRres = M('delivery_doc') -> where(array('id'=>$items['delivery_id']))->find();
                $orderInfo['goods_list'][$keys]['shipping_name'] =   $deliveryRres['shipping_name'];
                $orderInfo['goods_list'][$keys]['invoice_no'] =   $deliveryRres['invoice_no'];
            }
        }
        $orderInfo   = setBtnOrderStatus($orderInfo,'INDEX');
        $progressBar = getOrderProgressBar($orderInfo);
        $region_list = get_region_list();
        //统计订单条数
        $countGood = M('order_goods') -> where(array('order_id'=>$orderInfo['order_id']))->group('delivery_id')->select();
        $this -> assign('order_status',C('ORDER_STATUS'));
        $this -> assign('shipping_status',C('SHIPPING_STATUS'));
        $this -> assign('pay_status',C('PAY_STATUS'));
        $this -> assign('region_list',$region_list);
        $this -> assign('order_info',$orderInfo);
        $this -> assign('progressBar',$progressBar);
        $this -> assign('countGood',count($countGood));
        $this -> display();
    }

    //取消订单
    public function cancelOrder(){
        $id = I('get.id');
        $orderLogic = new \Common\Logic\OrderLogic();
        $data = $orderLogic -> cancelOrder($this->user_id,$id);
        if( !callbackIsTrue($data) ) {
            $this->error($data['msg']);
        }
        $this->success($data['msg']);
    }

    //确认订单
    public function orderConfirm(){
        $id = I('get.id',0);
        $this->error("暂时不提供此功能，请在微信端操作");exit;
        $orderLogic = new \Common\Logic\OrderLogic();
        $data = $orderLogic -> confirmOrder($id);
        if( !callbackIsTrue($data) ){
            $this->error($data['msg']);
        }
        $this->success($data['msg']);
    }


    //订单支付状态
    public function getOrderPayStatus(){
        $orderId = I('orderId');
        $orderLogic = new \Common\Logic\OrderLogic();
        $orderInfo =  $orderLogic -> getOrderInfo( $orderId , $this->user_id );
        if(!$orderInfo){
            exit(json_encode( callback( false , "没有获取到订单信息" ) ) );
        }
        if( $orderInfo['pay_status'] == 1 ){
            exit(json_encode( callback( true , "订单已支付" ) ) );
        }
        exit(json_encode( callback( false , "订单尚未支付" ) ) );
    }

}