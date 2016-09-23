<?php

namespace Mobile\Controller;
use Think\Page;
class OrderController extends MobileBaseController {


    function exceptAuthActions()
    {
        return null;
    }


    public function  _initialize() {
        parent::_initialize();

        $order_status_coment = array(
            'WAITPAY'=>'待付款 ', //订单查询状态 待支付
            'WAITSEND'=>'待发货', //订单查询状态 待发货
            'WAITRECEIVE'=>'待收货', //订单查询状态 待收货
            'WAITCCOMMENT'=>'待评价', //订单查询状态 待评价
        );
        $this->assign('order_status_coment',$order_status_coment);
    }



    public function toWeChatPay(){

    }


    /*
     * 订单列表
     */
    public function order_list()
    {
        $where = ' user_id='.$this->user_id;
        $_GET['type'] = $type = I('type','WAITPAY');
        //条件搜索
//        if(in_array(strtoupper($type), array('WAITCCOMMENT','COMMENTED')))
//        {
//           $where .= " AND order_status in(1,4) "; //代评价 和 已评价
//        }else{
        $where .= C(strtoupper($type));
//        }
        $count = M('order')->where($where)->count();
        $Page = new Page($count,10);

        $show = $Page->show();
        $order_str = "order_id DESC";
        $order_list = M('order')->order($order_str)->where($where)->limit($Page->firstRow.','.$Page->listRows)->select();

        //获取订单商品
        $model = new \Common\Logic\UsersLogic();
//        $model = new UsersLogic();
        foreach($order_list as $k=>$v)
        {
            $order_list[$k] = set_btn_order_status($v);  // 添加属性  包括按钮显示属性 和 订单状态显示属性
            //$order_list[$k]['total_fee'] = $v['goods_amount'] + $v['shipping_fee'] - $v['integral_money'] -$v['bonus'] - $v['discount']; //订单总额
            $data = $model -> getOrderGoods($v['order_id']);
            $order_list[$k]['goods_list'] = $data['data'];
        }
        $this->assign('order_status',C('ORDER_STATUS'));
        $this->assign('shipping_status',C('SHIPPING_STATUS'));
        $this->assign('pay_status',C('PAY_STATUS'));
        $this->assign('page',$show);
        $this->assign('lists',$order_list);
        $this->assign('active','order_list');
        $this->assign('active_status',I('get.type'));
        if($_GET['is_ajax'])
        {
            $this->display('ajax_order_list');
            exit;
        }
        $this->display();
    }


    /*
     * 订单列表
     */
    public function ajax_order_list(){

    }

    /*
     * 订单详情
     */
    public function order_detail(){
        $id = I('get.id');
        $map['order_id'] = $id;
        $map['user_id'] = $this->user_id;
        $order_info = M('order')->where($map)->find();
        $order_info = set_btn_order_status($order_info);  // 添加属性  包括按钮显示属性 和 订单状态显示属性
        if(!$order_info){
            $this->error('没有获取到订单信息');
            exit;
        }
        //获取订单商品
        $model = new \Common\Logic\UsersLogic();
        $data = $model -> getOrderGoods($order_info['order_id']);
        $order_info['goods_list'] = $data['data'];
        //$order_info['total_fee'] = $order_info['goods_price'] + $order_info['shipping_price'] - $order_info['integral_money'] -$order_info['coupon_price'] - $order_info['discount'];

        $region_list = get_region_list();
        $invoice_no = M('DeliveryDoc')->where("order_id = $id")->getField('invoice_no',true);
        $order_info[invoice_no] = implode(' , ', $invoice_no);
        //获取订单操作记录
        $order_action = M('order_action')->where(array('order_id'=>$id))->select();
        $this->assign('order_status',C('ORDER_STATUS'));
        $this->assign('shipping_status',C('SHIPPING_STATUS'));
        $this->assign('pay_status',C('PAY_STATUS'));
        $this->assign('region_list',$region_list);
        $this->assign('order_info',$order_info);
        $this->assign('order_action',$order_action);
        $this->display();
    }

    public function order_confirm(){
        $id = I('get.id',0);
        $data = confirm_order($id);
        if(!$data['status'])
            $this->error($data['msg']);
        else
            $this->success($data['msg']);
    }

}
