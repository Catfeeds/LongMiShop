<?php
namespace Index\Controller;

use Common\Logic\UsersLogic;
use Think\Page;
class OrderController extends BaseIndexController {

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
        // 搜索订单 根据商品名称 或者 订单编号
        $search_key = trim(I('search_key'));
        if($search_key)
        {
            $where .= " and (order_sn like '%$search_key%' or order_id in (select order_id from `".C('DB_PREFIX')."order_goods` where goods_name like '%$search_key%') ) ";
        }

        $count = M('order')->where($where)->count();
        $Page = new Page($count,5);

        $show = $Page->show();
        $order_str = "order_id DESC";
        $order_list = M('order')->order($order_str)->where($where)->limit($Page->firstRow.','.$Page->listRows)->select();

        //获取订单商品
        $model = new UsersLogic();
        foreach($order_list as $k=>$v)
        {
            $order_list[$k] = set_btn_order_status($v);  // 添加属性  包括按钮显示属性 和 订单状态显示属性
            //$order_list[$k]['total_fee'] = $v['goods_amount'] + $v['shipping_fee'] - $v['integral_money'] -$v['bonus'] - $v['discount']; //订单总额
            $data = $model->getOrderGoods($v['order_id']);
            $order_list[$k]['goods_list'] = $data['result'];
        }
        $this->assign('order_status',C('ORDER_STATUS'));
        $this->assign('shipping_status',C('SHIPPING_STATUS'));
        $this->assign('pay_status',C('PAY_STATUS'));
        $this->assign('page',$show);
        $this->assign('lists',$order_list);
        $this->assign('active','order_list');
        $this->assign('active_status',I('get.type'));
        $this->display();
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
        $orderInfo   = setBtnOrderStatus($orderInfo,'INDEX');
        $progressBar = getOderProgressBar($orderInfo);
        $region_list = get_region_list();
        $this->assign('order_status',C('ORDER_STATUS'));
        $this->assign('shipping_status',C('SHIPPING_STATUS'));
        $this->assign('pay_status',C('PAY_STATUS'));
        $this->assign('region_list',$region_list);
        $this->assign('order_info',$orderInfo);
        $this->assign('progressBar',$progressBar);
        $this->display();
    }

    //取消订单
    public function cancelOrder(){
        $id = I('get.id');
        $orderLogic = new \Common\Logic\OrderLogic();
        $data = $orderLogic -> cancelOrder($this->user_id,$id);
        if($data['state'] == 0)
            $this->error($data['msg']);
        $this->success($data['msg']);
    }
    //确认订单
    public function orderConfirm(){
        $id = I('get.id',0);
        $orderLogic = new \Common\Logic\OrderLogic();
        $data = $orderLogic -> confirmOrder($this->user_id,$id);
        if($data['status']== 0){
            $this->error($data['msg']);
        }
        $this->success($data['msg']);
    }

}