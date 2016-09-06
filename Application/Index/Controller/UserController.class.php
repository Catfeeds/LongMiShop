<?php
namespace Index\Controller;

use Common\Logic\UsersLogic;
use Think\Page;
use Think\Verify;
use Think\Upload;

class UserController extends BaseIndexController {

    function exceptAuthActions()
    {
        return array(
            'login',
            'doLogin',
            'register',
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
        $cartLogic->login_cart_handle($this->session_id,session(__UserID__));  //用户登录后 需要对购物车 一些操作
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

    //退出
    public function logout(){
        session_unset();
        session_destroy();
        header("location:".U('Index/Index/index'));
        exit;
    }

    //用户注册
    public function register(){
        // session_start();
        $config = tpCache('sms');
        $sms_time_out = $config['sms_time_out'];
        if(IS_POST){
            $verify = new \Think\Verify();
            $code=I("post.verify");
            if(!$verify->check($code,$id)){
                $this->error('验证码输入错误!',U('Index/User/register'),3);
            }
            $data = I('post.');
            //手机号码是否注册
            $new_res = $this->users->where("mobile = '".$data['mobile']."'")->count();
            if(empty($new_res)){
                $data['password'] = encrypt($data['password']);
                $data['reg_time'] = time();
                $res = $this->users->add($data);
                $res ? $this->success('注册成功',U('Index/Index/index')) : $this->error('注册失败');exit;
            }else{
                $this->error('手机号码已注册');
                exit;
            }

        }
        $this->assign('time',$sms_time_out);
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

        $user_info = $this -> user_info;

        if(IS_POST){
        }
        //  获取省份
        $province = M('region')->where(array('parent_id'=>0,'level'=>1))->select();
        //  获取订单城市
        $city =  M('region')->where(array('parent_id'=>$user_info['province'],'level'=>2))->select();
        //获取订单地区
        $area =  M('region')->where(array('parent_id'=>$user_info['city'],'level'=>3))->select();

        $this->assign('province',$province);
        $this->assign('city',$city);
        $this->assign('area',$area);
        $this->assign('sex',C('SEX'));
        $this->assign('active','info');
        $this->display();
    }


    /*
    * 添加地址
    */
    public function addressAdd(){
        if(IS_POST){
            $logic = new UsersLogic();
            $post = I('post.');
            $data = $logic->add_address($this->user_id,0,$post);
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
        // 如果已经支付过的订单直接到订单详情页面. 不再进入支付页面
        if($order['pay_status'] == 1){
            $order_detail_url = U("Index/Order/OrderDetail",array('id'=>$order_id));
            header("Location: $order_detail_url");
        }

        $orderLogic = new \Common\Logic\OrderLogic();
        $data = $orderLogic -> getOrderGoods($order['order_id']);
        $this->assign('goodsList',$data['data']);
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
        if(IS_POST){ //修改绑定手机
            $mobile = I('post.mobile');
            $code = I('post.code');
            $info = $userLogic->sms_code_verify($mobile,$code,$this->session_id);
            if($info['status'] == 1){
                $where['mobile'] = $mobile;
                $where['mobile_validated'] = 1;
                $where['user_id'] =  $this->user_id;
                $res = $this->users->save($where);
                $res ? $this->success('绑定成功',U('Index/User/info')) :  $this->error('绑定失败');
            }else{
                $this->error($info['msg']);
            }
            exit;
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
        exit(json_encode(array('status'=>1,'msg'=>'验证码已发送，请注意查收')));exit;
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
        if(IS_POST){
            $phone = I('phone');
            $where['mobile'] = $phone;
            $phone_res  = $this->users->field('user_id,mobile')->where($where)->find();

            if(empty($phone_res)){ //可以更换
                $res = 1;
            }else if($phone_res['mobile'] == $phone && $phone_res['user_id'] != $this->user_id){ //此手机已绑定
                $res = 3;
            }
            exit(json_encode($res));
        }
    }

    /*
     * 邮箱验证
     */
    public function email_validate(){
        $send_email_time = session('send_email_time');
        $res_time = $send_email_time + 300;
        $now_time = time();
        if($res_time > $now_time){
            $time = $res_time - $now_time;
        }else if($res_time < $now_time){
            session('send_email_time',null);
            $time = 300;
            $info = time();
            session('send_email_time',$info);
        }

        $this->assign('time',$time);
        $this->display();
    }

    //发送邮箱验证
    public function  send_email(){
        $userLogic = new UsersLogic();
        $user_info = $userLogic->get_info($this->user_id); // 获取用户信息
        $this->email_log = M('email_log');
        $type = I('type');
        $secret_key = sha1(md5(mt_rand(0,999999)).'longmi');
        $data['time'] = time();
        $data['secret_key'] = $secret_key;

        if($type=='anew'){ //ajax请求重新发送
            $res = $this->email_log->where("user_id = '".$user_info['result']['user_id']."'")->save($data);
        }else{
            $data['user_id']  = $user_info['result']['user_id'];
            $res = $this->email_log->add($data);
        }
        if($user_info['result']['email_validated'] != 0) { //状态修改为 未验证
            $datas['email_validated'] = 0;
            $this->users->where("user_id = '".$user_info['result']['user_id']."'")->save($datas); //验证
        }

        if($res){
            $url = 'http://'.$_SERVER['SERVER_NAME'].U('Index/User/check_email',array('secret_key'=>$secret_key,'user_id'=>$user_info['result']['user_id']));
            send_email($user_info['result']['email'],'邮箱验证','尊敬的'.$user_info['result']['nickname'].'用户您好，请下面链接进行邮箱验证：'.$url);
            exit(json_encode(callback(true,'发送成功',array('status'=>1))));
        }else{
            exit(json_encode(callback(false,'发送失败')));
        }



    }


    //邮箱验证
    public function check_email(){
        $where['secret_key'] = I('get.secret_key');
        $where['user_id'] = I('get.user_id');
        $email_res = M('email_log')->where($where)->find();
        if(!empty($email_res)){
            $data['email_validated'] = 1;
            $data['user_id'] = $this->user_id;
            $res = $this->users->save($data); //修改验证字段
            if($res){
                M('email_log')->where($where)->delete();
                $this->success('验证成功',U('Index/User/Info'));
            }else{
                $this->error('验证失败');
            }

        }else{
            $this->error('验证失败');
        }
        exit;
    }

    /*
     * 修改邮箱
     */

    public function edit_email(){
        if(IS_POST){

            $data['email'] = I('email');
            $where['email'] = I('email');
            $find_res = $this->users->field('user_id,email')->where($where)->find();
            if($find_res['email'] == $data['email']){
                $this->error('修改邮箱和原邮箱一致');exit;
            }else if(!empty($find_res)){
                $this->error('此邮箱已绑定');exit;
            }else{
                $data['user_id'] = $this->user_id;
                $data['email_validated'] = 0;
                $res = $this->users->save($data);
                if($res){
                    $info = time();
                    session('send_email_time',$info);
                    $this->success('修改成功',U('Index/User/email_validate'));
                }else{
                    $this->error('修改失败');
                }
                exit;
            }

        }
        $this->display();
    }

    //修改头像
    public function upload_lcon(){
        if(IS_POST){
            if(empty($this->user_id)){
                exit(json_encode(callback(false,"请先登录")));
            }
            $dirName = './Public/upload/head_pic/';
            if(!is_dir($dirName)){
                mkdir($dirName,0777,true);
            }
            $uploadConfig = array(
                "rootPath"  => $dirName,
                "exts"      => array('jpg','gif','png','jpeg'),
                "saveName"  => $this->user_id.'_'.mt_rand(),
                "replace"   => True,
                "maxSize"   => 1024*1024,

            );
            $upload = new \Think\Upload($uploadConfig);//实例化上传类
            $info = $upload->upload();
            if($info){
                $this->del_before($this->user_id); //删除旧头像
                $data['head_pic'] = $dirName.$info['head_pic']['savename'];
                $data['user_id'] = $this->user_id;
                M('users')->save($data);
                exit(json_encode(callback(true,"上传成功")));
            }
            exit(json_encode(callback(false,$upload->getError())));
        }
        exit(json_encode(callback(false,"上传出错啦")));
    }

    /*修改删除文件*/
    public function del_before($id){
        $res = M('user')->field('head_pic')->where('user_id = '.$id)->find();
        // $file = './Template/mobile/longmi/Static/images/'.$res['head_pic'];
        unlink($res['head_pic']);//删除
    }




}