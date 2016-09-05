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
        $this->users = M('users');

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
        header("location:".U('Index/Order/orderList'));
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

        if(!$order){
            $this->error('没有获取到订单信息');
            exit;
        }
        $orderLogic = new \Common\Logic\OrderLogic();
        $data = $orderLogic -> getOrderGoods($order['order_id']);
        $goodsList = $data['data'];
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
        $this->assign('goodsList',$goodsList);
        $this->assign('bankCodeList',$bankCodeList);
        $this->assign('pay_date',date('Y-m-d', strtotime("+1 day")));
        $this->display();
    }

    //昵称修改
    public function edit_name(){
        if(IS_POST){
            $data['nickname'] = I('nickname');
            if(empty($data['nickname'])){
                $this->error('昵称不能为空',U('/Index/User/edit_name'));
            }
            $data['nickname'] = I('nickname');
            $res = M('users')->where("user_id = '".$this->user_id."'")->save($data);
            $res ? $this->success('修改成功',U('/Index/User/info')) : $this->error('修改失败',U('/Index/User/edit_name'));exit;
        }
        $this->display();
    }

    //密码修改
    public function edit_pwd(){
        if(IS_POST){
            $initial_pwd = trim(I('initial_pwd')); //旧密码
            $password = trim(I('password')); //新密码
            $verify_pwd = trim(I('verify_pwd')); //密码确认
            if(empty($initial_pwd)){
                $this->error('旧密码不能为空',U('/Index/User/edit_pwd'));exit;
            }else if(empty($password) || empty($verify_pwd)){
                 $this->error('新密码不能为空',U('/Index/User/edit_pwd'));exit;
            }else if($password != $verify_pwd){
                $this->error('新密码两次输入不一致',U('/Index/User/edit_pwd'));exit;
            }else if($initial_pwd == $password){
                $this->error('新密码和旧密码一致',U('/Index/User/edit_pwd'));exit;
            }
            $user = $this->users->field('password')->where("user_id = '".$this->user_id."'")->find();
            if(encrypt($initial_pwd) != $user['password']){
                $this->error('旧密码错误',U('/Index/User/edit_pwd'));exit;
            }else{
                $data['password'] = encrypt($password);
                $data['user_id'] = $this->user_id;
                $res = $this->users->save($data);
                $res ? $this->success('修改成功',U('/Index/User/info')) : $this->error('修改失败',U('/Index/User/edit_pwd'));exit;
            }
        }
        $this->display();
    }

    /*
    * 手机验证
    */
    public function mobile_validate(){
        $userLogic = new UsersLogic();
        $user_info = $userLogic->get_info($this->user_id); //获取用户信息
        $user_info = $user_info['result'];
        $config = tpCache('sms');
        $sms_time_out = $config['sms_time_out'];
        $step = I('get.step',1);
        //验证是否未绑定过
        if($user_info['mobile_validated'] == 0)
            $step = 2;
        //原手机验证是否通过
        if($user_info['mobile_validated'] == 1 && session('mobile_step1') == 1)
            $step = 2;
        if($user_info['mobile_validated'] == 1 && session('mobile_step1') != 1)
            $step = 1;
        if(IS_POST){
            $mobile = I('post.mobile');
            $old_mobile = I('post.old_mobile');
            $code = I('post.code');
            $info = session('mobile_code');
            if(!$info)
                $this->error('非法操作');
            //检查原手机是否正确
            if($user_info['mobile_validated'] == 1 && $old_mobile != $user_info['mobile'])
                $this->error('原手机号码错误');
            //验证手机和验证码
            if($info['mobile'] == $mobile && $info['code'] == $code){
                session('mobile_code',null);
                //验证有效期
                if($info['time'] < time())
                    $this->error('验证码已失效');
                if(!$userLogic->update_email_mobile($mobile,$this->user_id,2))
                    $this->error('手机已存在');
                $this->success('绑定成功',U('Index/User/info'));
                exit;
            }
            $this->error('手机验证码不匹配');
        }
        $phone = $user_info['mobile'];
        $this->assign('time',$sms_time_out);
        $this->assign('step',$step);
        $this->assign('phone',$phone);
        $this->display();
    }

    /**
     * 发送手机注册验证码
     */
    public function send_sms_reg_code(){
        $mobile = I('get.mobile');
        exit(json_encode(array('status'=>$mobile,'msg'=>'验证码已发送，请注意查收')));exit;
        $mobile = I('post.mobile');
        $userLogic = new Common\Logic\UsersLogic();
        if(!check_mobile($mobile))
            exit(json_encode(array('status'=>-1,'msg'=>'手机号码格式有误')));
        $code =  rand(1000,9999);
        $send = $userLogic->sms_log($mobile,$code,$this->session_id);
        if($send['status'] != 1)
            exit(json_encode(array('status'=>-1,'msg'=>$send['msg'])));
        exit(json_encode(array('status'=>1,'msg'=>'验证码已发送，请注意查收')));
    }

    //验证手机是否已绑定
    public function check_phones(){
        echo json_encode(I('get.phone'));exit;
        if(IS_POST){
            // $phone = I('phones');

            $where['mobile'] = $phone;
            $phone_res  = $this->users->field('user_id,mobile')->where($where)->fetChSql(true)->find();
            
            if(empty($phone_res)){ //可以更换
                $res = 1; 
            }else if($phone_res['mobile'] == $phone && $phone_res['user_id'] == $this->user_id){  //手机号和之前相同
                $res = 2;
            }else if($phone_res['mobile'] == $phone && $phone_res['user_id'] != $this->user_id){ //此手机已绑定
                $res = 3;
            }

             

        }
    }



}