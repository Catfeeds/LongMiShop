<?php
namespace Index\Controller;

use Common\Logic\UsersLogic;
use Think\Page;

class UserController extends BaseIndexController {

    function exceptAuthActions()
    {
        return array(
            'login',
            'doLogin',
            'register'
        );
    }

    public function _initialize() {
        parent::_initialize();
    }

    public function login(){
        $this->display();
    }

    public function doLogin(){
        $username = I('post.username');
        $password = I('post.password');
        $username = trim($username);
        $password = trim($password);

        $logic = new \Common\Logic\UsersLogic();
        $res = $logic->login($username,$password);
        $cartLogic = new \Common\Logic\CartLogic();
        $cartLogic->login_cart_handle($this->session_id, session(__UserID__));  //用户登录后 需要对购物车 一些操作
        exit(json_encode($res));
//        if($res['status'] == 1){
//            $res['url'] =  urldecode(I('post.referurl'));
//            session('user',$res['result']);
//            setcookie('user_id',$res['result']['user_id'],null,'/');
//            setcookie('is_distribut',$res['result']['is_distribut'],null,'/');
//            $nickname = empty($res['result']['nickname']) ? $username : $res['result']['nickname'];
//            setcookie('uname',urlencode($nickname),null,'/');
//            setcookie('cn','',time()-3600,'/');
//        }
//        exit(json_encode($res));
    }

    public function logout(){
        session_unset();
        session_destroy();
        header("location:".U('Index/Index/index'));
        exit;
    }

    public function register(){
        $this->display();
    }

    public function index(){
        header("location:".U('Index/User/orderList'));
        $this->display();
    }

    public function returnGoodsList(){
        $count = M('return_goods')->where("user_id = {$this->user_id}")->count();
        $page = new Page($count,10);
        $list = M('return_goods')->where("user_id = {$this->user_id}")->order("id desc")->limit("{$page->firstRow},{$page->listRows}")->select();
        $goods_id_arr = get_arr_column($list, 'goods_id');
        if(!empty($goods_id_arr))
            $goodsList = M('goods')->where("goods_id in (".  implode(',',$goods_id_arr).")")->getField('goods_id,goods_name');
        $this->assign('goodsList', $goodsList);
        $this->assign('list', $list);
        $this->assign('page', $page->show());// 赋值分页输出
        $this->display();
    }

    public function coupon(){
        $logic = new UsersLogic();
        $data = $logic->get_coupon($this->user_id,$_REQUEST['type']);
        $coupon_list = $data['result'];
        $this->assign('coupon_list',$coupon_list);
        $this->assign('page',$data['show']);
        $this->assign('active','coupon');
        $this->display();
    }

    public function addressList(){
        $address_lists = get_user_address_list($this->user_id);
        $region_list = get_region_list();
        $this->assign('region_list',$region_list);
        $this->assign('lists',$address_lists);
        $this->assign('active','address_list');
        $this->display();
    }



    public function info(){
        $this->display();
    }


    /*
    * 添加地址
    */
    public function addressAdd(){
        if(IS_POST){
            $logic = new UsersLogic();
            $data = $logic->add_address($this->user_id,0,I('post.'));
            if($data['status'] != 1){
                $this->error('操作失败');exit;
            }
            $this->success("操作成功");exit;
        }
        $p = M('region')->where(array('parent_id'=>0,'level'=> 1))->select();
        $this->assign('province',$p);
        $this->display('addressEdit');

    }

    /*
     * 地址编辑
     */
    public function addressEdit(){
        $id = I('get.id');
        if( !empty($id) ) {
            $formUrl = U('addressEdit');
        }else{
            $formUrl = U('addressAdd');
        }
        $address = M('user_address')->where(array('address_id'=>$id,'user_id'=> $this->user_id))->find();
        if(IS_POST){
            $id = I('post.id');
            $logic = new UsersLogic();
            $data = $logic->add_address($this->user_id,$id,I('post.'));
            if($data['status'] != 1) {
                $this->error('操作失败');exit;
            }
            $this->success("操作成功");exit;
        }
        //获取省份
        $p = M('region')->where(array('parent_id'=>0,'level'=> 1))->select();
        $c = M('region')->where(array('parent_id'=>$address['province'],'level'=> 2))->select();
        $d = M('region')->where(array('parent_id'=>$address['city'],'level'=> 3))->select();
        if($address['twon']){
            $e = M('region')->where(array('parent_id'=>$address['district'],'level'=>4))->select();
            $this->assign('twon',$e);
        }
        $this->assign('formUrl',$formUrl);
        $this->assign('id',$id);
        $this->assign('province',$p);
        $this->assign('city',$c);
        $this->assign('district',$d);
        $this->assign('address',$address);
        $this->display();
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
        $Page       = new Page($count,5);

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
        $orderInfo = $orderLogic -> getOrderInfo( $id , $this->user_id );
        if(!$orderInfo){
            $this->error('没有获取到订单信息');
            exit;
        }
        $data = $orderLogic->getOrderGoods($orderInfo['order_id']);
        $orderInfo['goods_list'] = $data['data'];
        $orderInfo = set_btn_order_status($orderInfo);
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


    /*
     * 设置默认收货地址
     */
    public function set_default(){
        $id = I('get.id');
        M('user_address')->where(array('user_id'=>$this->user_id))->save(array('is_default'=>0));
        $row = M('user_address')->where(array('user_id'=>$this->user_id,'address_id'=>$id))->save(array('is_default'=>1));
        if(!$row)
            $this->error('操作失败');
        $this->success("操作成功");
    }

    /*
     * 地址删除
     */
    public function del_address(){
        $id = I('get.id');

        $address = M('user_address')->where("address_id = $id")->find();
        $row = M('user_address')->where(array('user_id'=>$this->user_id,'address_id'=>$id))->delete();
        // 如果删除的是默认收货地址 则要把第一个地址设置为默认收货地址
        if($address['is_default'] == 1)
        {
            $address = M('user_address')->where("user_id = {$this->user_id}")->find();
            M('user_address')->where("address_id = {$address['address_id']}")->save(array('is_default'=>1));
        }
        if(!$row)
            $this->error('操作失败',U('/Mobile/User/address_list'));
        else
            $this->success("操作成功",U('/Mobile/User/address_list'));
    }

    public function payment(){
        $order_id = I('order_id');
        $order = M('Order')->where("order_id = $order_id")->find();
        // 如果已经支付过的订单直接到订单详情页面. 不再进入支付页面
        if($order['pay_status'] == 1){
            $order_detail_url = U("Index/User/order_detail",array('id'=>$order_id));
            header("Location: $order_detail_url");
        }

        $paymentList = M('Plugin')->where("`type`='payment' and status = 1 and  scene in(0,2)")->select();
        $paymentList = convert_arr_key($paymentList, 'code');

        foreach($paymentList as $key => $val)
        {
            $val['config_value'] = unserialize($val['config_value']);
            if($val['config_value']['is_bank'] == 2)
            {
                $bankCodeList[$val['code']] = unserialize($val['bank_code']);
            }
        }
        $bank_img = include_once 'Application/Common/Conf/bank.php'; // 银行对应图片
        $payment = M('Plugin')->where("`type`='payment' and status = 1")->select();
        $this->assign('paymentList',$paymentList);
        $this->assign('bank_img',$bank_img);
        $this->assign('order',$order);
        $this->assign('bankCodeList',$bankCodeList);
        $this->assign('pay_date',date('Y-m-d', strtotime("+1 day")));
        $this->display();
    }
}