<?php

namespace Mobile\Controller;
use Common\Logic\UsersLogic;
use Think\Page;
use Think\Verify;

class UserController extends MobileBaseController {

    function exceptAuthActions()
    {
        return array(
            'login',
            'pop_login',
            'do_login',
            'logout',
            'verify',
            'set_pwd',
            'finished',
            'verifyHandle',
            'reg',
            'send_sms_reg_code',
            'find_pwd',
            'check_validate_code',
            'forget_pwd',
            'check_captcha',
            'check_username',
            'send_validate_code',
            'express',
            'sendSmsBindingCode',
            'returnSession',
        );
    }

        /**
        * 初始化操作
        */
    public function _initialize() {
        parent::_initialize();
    }

    /**
     * 用户中心首页
     */
    public function index(){
        $usersLogic = new \Common\Logic\UsersLogic();
        $result = $usersLogic -> getCoupon( $this->user_id);
        $this->assign('couponCount',$result['data']['count']);
        $this -> assign('number', getInviteNumber($this ->user_id) );
        $this->display();
    }


    public function logout(){
        session_unset();
        session_destroy();
        setcookie('cn','',time()-3600,'/');
        setcookie('user_id','',time()-3600,'/');
        //$this->success("退出成功",U('Mobile/Index/index'));
        header("Location:".U('Mobile/Index/index'));
    }

    /*
     * 账户资金
     */
    public function account(){
        //获取账户资金记录
        $logic = new \Common\Logic\UsersLogic();
        $data = $logic->get_account_log($this->user_id,I('get.type'));
        $account_log = $data['result'];

        $this->assign('account_log',$account_log);
        $this->assign('page',$data['show']);
        $this->assign('count',$data['count']);
        $this->assign('limit',$data['limit']);

        if($_GET['is_ajax'])
        {
            $this->display('ajax_account_list');
            exit;
        }
        $this->display();
    }

    public function coupon(){
        //
        $logic = new \Common\Logic\UsersLogic();
        $data = $logic->get_coupon($this->user_id,$_REQUEST['type']);
        $coupon_list = $data['result'];
        $this->assign('coupon_list',$coupon_list);
        $this->assign('page',$data['show']);
        $this->assign('count',$data['count']);
        $this->assign('limit',$data['limit']);
        if($_GET['is_ajax'])
        {
            $this->display('ajax_coupon_list');
            exit;
        }
        $this->display();
    }
    /**
     *  登录
     */
    public function login(){
        if( isLoginState() ){
        	header("Location: ".U('Mobile/User/index'));
        }
        $referurl = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : U("Mobile/User/index");
        $this->assign('referurl',$referurl);
        $this->display();
    }


    public function do_login(){
    	$username = I('post.username');
    	$password = I('post.password');
    	$username = trim($username);
    	$password = trim($password);

        $logic = new \Common\Logic\UsersLogic();
        $result = $logic -> login($username,$password);
        if( callbackIsTrue($result) ){
            $res['url'] =  urldecode(I('post.referurl'));
            session('user',$res['result']);
            $cartLogic = new \Common\Logic\CartLogic();
            $cartLogic->login_cart_handle($this->session_id,session(__UserID__));  //用户登录后 需要对购物车 一些操作
        }
    	exit(json_encode($result));
    }

    /**
     *  注册
     */
    public function reg(){

        if( isLoginState() ){
            header("Location: ".U('Mobile/User/index'));
        }
        if(IS_POST){
            $logic = new \Common\Logic\UsersLogic();
            $data = I('post.');
            $phone_res = $logic->sms_code_verify($data['mobile'],$data['phone_code'],$this->session_id);
            if($phone_res['status'] != 1){
                $this->error($phone_res['msg']);exit;
            }
            $where = "email = '".$data['email']."' OR "."mobile = '".$data['mobile']."'";
            $res = M('users')->where($where)->count();
            if(empty($res)){
                $password = $data['password'];
                $data['password'] = encrypt($data['password']);
                $data['reg_time'] = time();
                if( !empty($data['mobile']) ){
                    $data['mobile_validated'] = 1 ;
                    $username = $data['mobile'];
                }else{
                    $username = $data['email'];
                }
                $res = M('users')->add($data);
                if($res){
                    $logic = new \Common\Logic\UsersLogic();
                    $result = $logic->login($username,$password);
                    if( !callbackIsTrue($result) ){
                        $this->error($result['msg']);
                        exit;
                    }
                    $this->success('注册成功',U('/Mobile/User/index'));
                    exit;
                }
                $this->error('注册失败');
                exit;
            }else{
                $this->error('手机号码或邮箱已注册');
                exit;
            }
            // $logic = new \Common\Logic\UsersLogic();
            // //验证码检验
            // //$this->verifyHandle('user_reg');
            // $username = I('post.username','');
            // $password = I('post.password','');
            // $password2 = I('post.password2','');
            // //是否开启注册验证码机制

            // if(check_mobile($username) && tpCache('sms.regis_sms_enable')){
            //     $code = I('post.mobile_code','');

            //     if(!$code)
            //         $this->error('请输入验证码');
            //     $check_code = $logic->sms_code_verify($username,$code,$this->session_id);
            //     if($check_code['status'] != 1)
            //         $this->error($check_code['msg']);

            // }

            // $data = $logic->reg($username,$password,$password2);
            // if($data['status'] != 1)
            //     $this->error($data['msg']);
            // session('user',$data['result']);
            // setcookie('user_id',$data['result']['user_id'],null,'/');
            // setcookie('is_distribut',$data['result']['is_distribut'],null,'/');
            // $cartLogic = new \Common\Logic\CartLogic();
            // $cartLogic->login_cart_handle($this->session_id,$data['result']['user_id']);  //用户登录后 需要对购物车 一些操作
            // $this->success($data['msg'],U('Mobile/User/index'));
            // exit;
        }
        $this->assign('regis_sms_enable',tpCache('sms.regis_sms_enable')); // 注册启用短信：
        $this->assign('sms_time_out',tpCache('sms.sms_time_out')); // 手机短信超时时间
        $this->display();
    }

    //物流信息
    public function express(){
        $id = I('get.order_id','','int');
//         $result = getExpress($id);
//         if( callbackIsTrue($result) ){
//            if($result['data']['status'] == 1){
//                //支付时间
//                $pay_time = M('order')->field('pay_time')->where("order_id = '".$id."'")->find();
//                $this->assign('pay_time',$pay_time['pay_time']);
//                $this->assign('expressData', $result['data']);
//            }else{
//                $this->assign('expressMessage', $result['data']['message']);
//            }
//         }else{
//             $this->assign('expressMessage', $result['msg'] );
//         }
        $delivery = M('delivery_doc')->where("order_id='$id'")->limit(1)->find();
        if(empty($delivery)){
            $this->assign('expressMessage', '查询物流失败' );
        }
        $result = kuaidi($delivery['invoice_no'],$delivery['shipping_name']);
//        dd($result);
        if( $result['status'] == 200  ){
//            dd($result['data']);
                //支付时间
            $pay_time = M('order')->field('pay_time')->where("order_id = '".$id."'")->find();
            $this->assign('pay_time',$pay_time['pay_time']);
            $this->assign('expressData', $result);
            $this->assign('odd_numbers',$delivery['invoice_no']);
         }else{
             $this->assign('expressMessage', $result['message'] );
         }

        $this->display();
    }




    /*
     * 修改昵称
     */
    public function edit_nickname(){
        if(IS_POST){
            $data['nickname'] = I('nickname');
            $data['user_id'] = $this->user_id;
            $res = M('users')->save($data);
            $res ? exit(json_encode(callback(true,'修改成功',array('status'=>1)))) : exit(json_encode(callback(false,'修改失败')));

        }
    }


    /*
     * 用户地址列表
     */
    public function address_list(){
    	//上一页url indent  center
    	// $skip_url = I('get.source');
    	// if($skip_url == 'cart2'){
    	// 	cookie('skip_url','Cart/'.$skip_url);
    	// }else 
     //    if(is_null(cookie('skip_url'))){
    	// 	cookie('skip_url','User/edit_details');
    	// }
        $skip_url = AddressTheJump();
        $address_lists = get_user_address_list($this->user_id);
        $region_list = get_region_list();
        $this->assign('region_list',$region_list);
        $this->assign('lists',$address_lists);
        $this->assign('skip_url',$skip_url);
        $this->display();
    }

    /*
     * 收货地址视图
     */
    public function edit_address(){
        $id = I('id');
        if(!empty($id)){
            $address = M('user_address')->where(array('address_id'=>$id,'user_id'=> $this->user_id))->find();
            $region_list = get_region_list();
            $citys  = $region_list[$address['province']]['name']." ". $region_list[$address['city']]['name']." ". $region_list[$address['district']]['name'];
            $this->assign('citys',$citys);
            $this->assign('region_list',$region_list);
            $this->assign('address',$address);
        }
        // $region_list = include_once 'Application/Common/Conf/region.js'; 

        // $region_list = json_encode($region_list);
        $skip_url = addressTheJump();
        if($address['twon']){
         $e = M('region')->where(array('parent_id'=>$address['district'],'level'=>4))->select();
         $this->assign('twon',$e);
        }
        $this->assign('address',$address);
        $this->assign('skip_url',$skip_url);
        $this->display();
        
    }

    /*
    *
    *地址修改
    *
    */
    public function save_address(){
        $id = I('address_id');
        $data = I('post.');
        $data['user_id'] = $this->user_id;
        if( !empty($data['is_default']) ){
            M('user_address')->where(array('user_id'=>$this->user_id))->save(array('is_default'=>0));
        }
        $skip_url = addressTheJump();
        $url = U('Mobile/'.$skip_url);
        $address = getCurrentAddress( $this->user_id);
        if( empty($address) ){
            $data['is_default'] = 1;
        }
        // dd($skip_url);
        if($id==0){ //新增
            //收货地址不能超过20个
            $userAddCount = M('user_address')->where(array('user_id'=>$this->user_id))->count();
            if($userAddCount >= 20){
                $this->error('个人收货地址不能超过20个');exit;
            }
            $res = M('user_address')->add($data);
            $res ? $this->success('新增成功',$url) : $this->error('新增失败');
        }else{ //修改
            $res = M('user_address')->save($data);
            $res ? $this->success('修改成功',$url) : $this->error('修改失败');
        }
    }


    /*
     * 设置默认收货地址
     */
    public function set_default(){
        $id = I('get.id');
        $source = I('get.source');
        M('user_address')->where(array('user_id'=>$this->user_id))->save(array('is_default'=>0));
        $row = M('user_address')->where(array('user_id'=>$this->user_id,'address_id'=>$id))->save(array('is_default'=>1));
        if($source == 'cart2')
        {
            header("Location:".U('Mobile/Cart/cart2'));
            exit;
        }else{
            header("Location:".U('Mobile/User/address_list'));
        }
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
            M('user_address')->where("address_id = '{$address['address_id']}'")->save(array('is_default'=>1));
        }

        if(!$row)
            $this->error('操作失败',U('/Mobile/User/address_list'));
        else
            $this->success("操作成功",U('/Mobile/User/address_list'));
    }

    /*
     * 评论晒单
     */
    public function comment(){
    	$user_id = $this->user_id;
    	$status = I('get.status');
    	$logic = new \Common\Logic\UsersLogic();
    	$result = $logic->get_comment($user_id,$status); //获取评论列表
    	$this->assign('comment_list',$result['result']);
        if($_GET['is_ajax'])
        {
            $this->display('ajax_comment_list');
            exit;
        }
    	$this->display();
    }

    /*
     *添加评论
     */
    public function add_comment(){
    	if(IS_POST){
    		$user_info = $this->user;
    		$logic = new \Common\Logic\UsersLogic();
    		$add['goods_id'] = I('goods_id');
            $orderId = I('order_id');
    		$add['email'] = $user_info['email'];
    		$hide_username = I('hide_username');
    		if(empty($hide_username)){
    			$add['username'] = $user_info['nickname'];
    		}
    		$add['order_id'] = I('order_id');
    		$add['service_rank'] = I('score');
    		$add['deliver_rank'] = I('score');
    		$add['goods_rank'] = I('score');
    		//$add['content'] = htmlspecialchars(I('post.content'));
    		$add['content'] = I('content');
    		$add['add_time'] = time();
    		$add['ip_address'] = getIP();
    		$add['user_id'] = $this->user_id;

    		//添加评论
    		$row = $logic->add_comment($add);
    		if($row[status] == 1)
    		{
                $this->redirect('Mobile/Order/order_list',0);
                exit();
    		}
    		else
    		{
    			$this->error($row[msg]);
    		}
    	}
        $rec_id = I('rec_id');
        $order_goods = M('order_goods')->where("rec_id = $rec_id")->find();
        $this->assign('order_goods',$order_goods);
        $this->display();
    }

    /*
     * 个人信息
     */
    public function userinfo(){
        if( isBinding( $this -> user_id ) ){
            $bindingAccountInfo = getBindingAccountData( $this -> user );
            $this->assign('bindingAccountInfo',$bindingAccountInfo);
        }
        $this->display();
    }

    public function switchAccount(){
        if( isBinding( $this -> user_id ) ){
            $bindingAccountInfo = getBindingAccountData( $this -> user , " user_id " );
            if( !empty( $bindingAccountInfo['user_id'] )  ){
                setBindingCurrentAccount( $this -> user_id , $bindingAccountInfo['user_id'] );
                $openid = session("openid");
                session(null);
                session("openid",$openid);
                $this->redirect('Mobile/User/index',0);
                exit;
            }
        }
        $this -> error("非法访问");
    }

    public function relieveBinding(){
        relieveBinding( $this -> user_id );
        $this ->success("解除成功");
    }


    //修改个人信息
    public function edit_details(){
        AddressTheJump(ACTION_NAME);
        $userLogic = new \Common\Logic\UsersLogic();
        $user_info = $userLogic->get_info($this->user_id); // 获取用户信息
        $this->assign('user_info',$user_info['result']);
        $this->display();
    }

    //修改头像
    public function upload_lcon(){
        if(IS_POST){
            if(empty($this->user_id)){
                exit(json_encode(callback(false,"请先登录")));
            }
            $uploadConfig = array(
                "savePath" =>"head_pic/",
                "exts"     => array('jpg','gif','png','jpeg'),
                "saveName" => $this->user_id.'_'.mt_rand(),
                "replace"  => True,
                "maxSize"  => 1024*1024,
            );
            $upload = new \Think\Upload($uploadConfig);//实例化上传类
            $info = $upload->upload();
            // exit(json_encode(callback(true,"上传成功",array('path'=>$info))));
            if($info){
                $this->del_before($this->user_id); //删除旧头像
                $data['head_pic'] = $info['file']['urlpath'];
                $data['user_id'] = $this->user_id;
                M('users')->save($data);
                exit(json_encode(callback(true,"上传成功",array('path'=>$data['head_pic']))));
            }
            exit(json_encode(callback(false,$upload->getError())));
        }
        exit(json_encode(callback(false,"上传出错啦")));
    }

    /*修改删除文件*/
    public function del_before($id){
        $res = M('users')->field('head_pic')->where('user_id = '.$id)->find();
        unlink($res['head_pic']);//删除
    }

    //

    //修改手机号码
    public function edit_mobile(){
        if(IS_POST){
            $mobile  = I('mobile');
            $code = I('phone_code');
            // dd($code);
            $userLogic = new UsersLogic();
            $info = $userLogic->sms_code_verify($mobile,$code,$this->session_id);
            if($info['status'] == 1){
                $where['mobile'] = $mobile;
                $where['mobile_validated'] = 1;
                $where['user_id'] =  $this->user_id;
                $res = M('users')->save($where);
                if($res){
                  $this->success('绑定成功',U('Mobile/User/userinfo'));exit;
                }else{
                    $this->error('绑定失败');exit;
                }
            }else{
                $this->error($info['msg']);
            }
            exit;

        }
        $userLogic = new \Common\Logic\UsersLogic();
        $user_info = $userLogic->get_info($this->user_id); // 获取用户信息
        $this->assign('user_info',$user_info['result']);
        $this->assign('sms_time_out',tpCache('sms.sms_time_out')); // 手机短信超时时间
        $this->display();
    }

    //修改密码
    public function edit_password(){
        if(IS_POST){
            $mobile  = I('mobile');
            $code = I('phone_code');
            $pwd = I('password');
            $password = encrypt($pwd);
            $user = M('users')->field('password')->where("user_id = '".$this->user_id."'")->find();
            if($user['password'] == $password){
                $this->error('新密码和旧密码一致');exit;
            }
            $userLogic = new UsersLogic();
            $info = $userLogic->sms_code_verify($mobile,$code,$this->session_id);
            if($info['status'] == 1){
                $where['password'] = $password;
                $where['user_id'] =  $this->user_id;
                $res = M('users')->save($where);
                if($res){
                  $this->success('修改成功',U('Mobile/User/userinfo'));
                }else{
                    $this->error('修改失败');
                }
            }else{
                $this->error($info['msg']);
            }
            exit;
        }

        $this->assign('sms_time_out',tpCache('sms.sms_time_out')); // 手机短信超时时间
        $this->display();
    }

    /*
     * 邮箱验证
     */
    public function email_validate(){
        $userLogic = new \Common\Logic\UsersLogic();
        $user_info = $userLogic->get_info($this->user_id); // 获取用户信息
        $user_info = $user_info['result'];
        $step = I('get.step',1);
        //验证是否未绑定过
        if($user_info['email_validated'] == 0)
            $step = 2;
        //原邮箱验证是否通过
        if($user_info['email_validated'] == 1 && session('email_step1') == 1)
            $step = 2;
        if($user_info['email_validated'] == 1 && session('email_step1') != 1)
            $step = 1;
        if(IS_POST){
            $email = I('post.email');
            $code = I('post.code');
            $info = session('email_code');
            if(!$info)
                $this->error('非法操作');
            if($info['email'] == $email || $info['code'] == $code){
                if($user_info['email_validated'] == 0 || session('email_step1') == 1){
                    session('email_code',null);
                    session('email_step1',null);
                    if(!$userLogic->update_email_mobile($email,$this->user_id))
                        $this->error('邮箱已存在');
                    $this->success('绑定成功',U('Home/User/index'));
                }else{
                    session('email_code',null);
                    session('email_step1',1);
                    redirect(U('Home/User/email_validate',array('step'=>2)));
                }
                exit;
            }
            $this->error('验证码邮箱不匹配');
        }
        $this->assign('step',$step);
        $this->display();
    }

    // /*
    // * 手机验证
    // */
    // public function mobile_validate(){
    //     $userLogic = new UsersLogic();
    //     $user_info = $userLogic->get_info($this->user_id); //获取用户信息
    //     $user_info = $user_info['result'];
    //     $config = tpCache('sms');
    //     $sms_time_out = $config['sms_time_out'];
    //     $step = I('get.step',1);
    //     //验证是否未绑定过
    //     if($user_info['mobile_validated'] == 0)
    //         $step = 2;
    //     //原手机验证是否通过
    //     if($user_info['mobile_validated'] == 1 && session('mobile_step1') == 1)
    //         $step = 2;
    //     if($user_info['mobile_validated'] == 1 && session('mobile_step1') != 1)
    //         $step = 1;
    //     if(IS_POST){ //修改绑定手机
    //         $mobile = I('post.mobile');
    //         $code = I('post.code');
    //         $info = $userLogic->sms_code_verify($mobile,$code,$this->session_id);
    //         if($info['status'] == 1){
    //             $where['mobile'] = $mobile;
    //             $where['mobile_validated'] = 1;
    //             $where['user_id'] =  $this->user_id;
    //             $res = $this->users->save($where);
    //             $res ? $this->success('绑定成功',U('Index/User/info')) :  $this->error('绑定失败');
    //         }else{
    //             $this->error($info['msg']);
    //         }
    //         exit;
    //     }
    //     $phone = $user_info['mobile'];
    //     $this->assign('time',$sms_time_out);
    //     $this->assign('step',$step);
    //     $this->assign('phone',$phone);
    //     $this->display();
    // }

    //手机修改验证码发送
    public function send_sms_reg(){
        $mobile = I('send');
        $type = I('type');
        if(!check_mobile($mobile)){
            exit(json_encode(array('status'=>-1,'msg'=>'手机号码格式有误')));
        }
        if($type == 'edit'){ //修改手机
            $where['mobile'] = $mobile;
            $user_res = M('users')->where($where)->find();
            if($user_res['user_id'] == $this->user_id ){
                exit(json_encode(array('status'=>-1,'msg'=>'修改号码和旧号码一致')));
            }else if($user_res['user_id'] != $this->user_id && $user_res['mobile'] == $mobile){
                exit(json_encode(array('status'=>-1,'msg'=>'此手机已被注册')));   
            }
        }
        $userLogic = new UsersLogic();
        $code =  rand(1000,9999);
        $send = $userLogic->sms_log($mobile,$code,$this->session_id);
        if($send['status'] != 1)
            exit(json_encode(array('status'=>-1,'msg'=>$send['msg'])));
        exit(json_encode(array('status'=>1,'msg'=>'验证码已发送，请注意查收')));
    }
    //手机注册验证码
   public function send_sms_reg_code(){
        //调试
        // exit(json_encode(array('status'=>1,'msg'=>'验证码已发送，请注意查收')));
        $mobile = I('send');
        $where['mobile'] = $mobile;
        $code=I("code");
        $verify = new \Think\Verify();
        if(!$verify->check($code,'user_login')){
            exit(json_encode(array('status'=>-1,'msg'=>'验证码输入错误')));
        }
        if(!check_mobile($mobile)){
            exit(json_encode(array('status'=>-1,'msg'=>'手机号码格式有误')));
        }
        $user_res = M('users')->where($where)->count();
        if(!empty($user_res)){
            exit(json_encode(array('status'=>-1,'msg'=>'此手机已被注册')));
        }

        $userLogic = new UsersLogic();
        $code =  rand(1000,9999);
        
         $send = $userLogic->sms_log($mobile,$code,$this->session_id);
        if($send['status'] != 1)
            exit(json_encode(array('status'=>-1,'msg'=>$send['msg'])));
        exit(json_encode(array('status'=>1,'msg'=>'验证码已发送，请注意查收')));
    }


    public function collect_list(){
    	$userLogic = new \Common\Logic\UsersLogic();
    	$data = $userLogic->get_goods_collect($this->user_id);
    	$this->assign('page',$data['show']);// 赋值分页输出
    	$this->assign('goods_list',$data['result']);
        $this->assign('count',$data['count']);
        $this->assign('limit',$data['limit']);
        if($_GET['is_ajax'])
        {
            $this->display('ajax_collect_list');
            exit;
        }
    	$this->display();
    }

    /*
     *取消收藏
     */
    public function cancel_collect(){
       $collect_id = I('collect_id');
       $user_id = $this->user_id;
       if(M('goods_collect')->where("collect_id = $collect_id and user_id = $user_id")->delete()){
       		$this->success("取消收藏成功",U('User/collect_list'));
       }else{
       		$this->error("取消收藏失败",U('User/collect_list'));
       }
    }

    public function message_list()
    {
    	C('TOKEN_ON',true);
    	if(IS_POST)
    	{
                $this->verifyHandle('message');

    		$data = I('post.');
    		$data['user_id'] = $this->user_id;
    		$user = session('user');
    		$data['user_name'] = $user['nickname'];
    		$data['msg_time'] = time();
    		if(M('feedback')->add($data)){
    			$this->success("留言成功",U('User/message_list'));
                        exit;
    		}else{
    			$this->error('留言失败',U('User/message_list'));
                        exit;
    		}
    	}
    	$msg_type = array(0=>'留言',1=>'投诉',2=>'询问',3=>'售后',4=>'求购');
    	$count = M('feedback')->where("user_id=".$this->user_id)->count();
    	$Page = new Page($count,100);
    	$Page->rollPage = 2;
    	$message = M('feedback')->where("user_id=".$this->user_id)->limit($Page->firstRow.','.$Page->listRows)->select();
    	$showpage = $Page->show();
    	header("Content-type:text/html;charset=utf-8");
    	$this->assign('page',$showpage);
    	$this->assign('message',$message);
    	$this->assign('msg_type',$msg_type);
    	$this->display();
    }

    public function points(){
        $condition = "pay_points != 0 and user_id=".$this->user_id;
        $count = M('account_log')->where($condition)->count();
        $limit = 16;
        $Page = new Page($count,$limit);
    	$account_log = M('account_log')->where($condition)->order('log_id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
        $showpage = $Page->show();
    	$this->assign('account_log',$account_log);
        $this->assign('page',$showpage);
        $thi->assign('count',$count);
        $thi->assign('limit',$limit);
        if($_GET['is_ajax'])
        {
            $this->display('ajax_points');
            exit;
        }
    	$this->display();
    }
    /*
     * 密码修改
     */
    public function password(){
        //检查是否第三方登录用户
        $logic = new \Common\Logic\UsersLogic();
        $data = $logic->get_info($this->user_id);
        $user = $data['result'];
        if($user['mobile'] == ''&& $user['email'] == '')
            $this->error('请先到电脑端绑定手机',U('/Mobile/User/index'));
        if(IS_POST){
            $userLogic = new \Common\Logic\UsersLogic();
            $data = $userLogic->password($this->user_id,I('post.old_password'),I('post.new_password'),I('post.confirm_password')); // 获取用户信息
            if($data['status'] == -1)
                $this->error($data['msg']);
            $this->success($data['msg']);
            exit;
        }
        $this->display();
    }

    function forget_pwd(){
        if($this->user_id > 0){
    		header("Location: ".U('User/Index'));
    	}
    	$username = I('username');
    	if(IS_POST){
    		if(!empty($username)){
    			$this->verifyHandle('forget');
    			$field = 'mobile';
    			if(check_email($username)){
    				$field = 'email';
    			}
    			$user = M('users')->where("email='$username' or mobile='$username'")->find();
    			if($user){
    				session('find_password',array('user_id' => $user['user_id'],'username' =>$username,
    				'email' => $user['email'],'mobile' => $user['mobile'],'type'=>$field));
    				header("Location: ".U('User/find_pwd'));
    				exit;
    			}else{
    				$this->error("用户名不存在，请检查");
    			}
    		}
    	}
    	$this->display();
    }

    function find_pwd(){
    	if($this->user_id > 0){
    		header("Location: ".U('User/Index'));
    	}
    	$user = session('find_password');
    	if(empty($user)){
    		$this->error("请先验证用户名",U('User/forget_pwd'));
    	}
    	$this->assign('user',$user);
    	$this->display();
    }


    public function set_pwd(){
    	if($this->user_id > 0){
    		header("Location: ".U('User/Index'));
    	}
    	$check = session('validate_code');
    	if(empty($check)){
    		header("Location:".U('User/forget_pwd'));
    	}elseif($check['is_check']==0){
    		$this->error('验证码还未验证通过',U('User/forget_pwd'));
    	}
    	if(IS_POST){
    		$password = I('post.password');
    		$password2 = I('post.password2');
    		if($password2 != $password){
    			$this->error('两次密码不一致',U('User/forget_pwd'));
    		}
    		if($check['is_check']==1){
    			//$user = get_user_info($check['sender'],1);
                        $user = M('users')->where("mobile = '{$check['sender']}' or email = '{$check['sender']}'")->find();
    			M('users')->where("user_id=".$user['user_id'])->save(array('password'=>encrypt($password)));
    			session('validate_code',null);
    			//header("Location:".U('User/set_pwd',array('is_set'=>1)));
                        $this->success('新密码已设置行牢记新密码',U('User/index'));
                        exit;
    		}else{
    			$this->error('验证码还未验证通过',U('User/forget_pwd'));
    		}
    	}
    	$is_set = I('is_set',0);
    	$this->assign('is_set',$is_set);
    	$this->display();
    }

    //发送验证码
    public function send_validate_code(){
        $type = I('type');
        $send = I('send');
        $logic = new \Common\Logic\UsersLogic();
        $logic->send_validate_code($send, $type);
    }

    public function check_validate_code(){
    	$code = I('post.code');
    	$send = I('send');
    	$logic = new \Common\Logic\UsersLogic();
    	$logic->check_validate_code($code, $send);
    }
    
    /**
     * 验证码验证
     * $id 验证码标示
     */
    private function verifyHandle($id)
    {
        $verify = new Verify();
        if (!$verify->check(I('post.verify_code'), $id ? $id : 'user_login')) {
            $this->error("验证码错误");
        }
    }

    /**
     * 验证码获取
     */
    public function verify()
    {
        //验证码类型
        $type = I('get.type') ? I('get.type') : 'user_login';
        $config = array(
            'fontSize' => 40,
            'length' => 4,
            'useCurve' => true,
            'useNoise' => false,
        );
        $Verify = new Verify($config);
        $Verify->entry($type);
    }
    /**
     * 账户管理
     */
    public function accountManage()
    {
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
    
    /**
     * 申请退货
     */
    public function return_goods()
    {

        $orderId = I('order_id','','int'); //订单id
        $goodsId = I('goods_id','','int'); //订单id
        $order = M('order')->where("order_id = '".$orderId."'")->find();
        $orderSn = $order['order_sn'];
        if(IS_POST){
            $data['order_id'] = $orderId; 
            $data['order_sn'] = $orderSn; 
            $data['goods_id'] = $goodsId; 
            $data['addtime'] = time(); 
            $data['user_id'] = $this->user_id;
            $data['reason'] = I('reason'); // 问题描述
            M('return_goods')->add($data);   
            $this->redirect('Mobile/Order/order_list',0);         
            // $this->success('申请成功,客服第一时间会帮你处理',U('Mobile/Order/order_list'));
            exit;

        }
        
        $this->assign('orderId',$orderId);
        $this->assign('orderSn',$orderSn);
        $this->assign('goodsId',$goodsId);
        $this->display();
     //    $order_id = I('order_id',0);
     //    $order_sn = I('order_sn',0);
     //    $goods_id = I('goods_id',0);        
	    // $spec_key = I('spec_key');        
     //    $return_goods = M('return_goods')->where("order_id = $order_id and goods_id = $goods_id and status in(0,1)  and spec_key = '$spec_key'")->find();            
     //    if(!empty($return_goods))
     //    {
     //        $this->success('已经提交过退货申请!',U('Mobile/User/return_goods_info',array('id'=>$return_goods['id'])));
     //        exit;
     //    }       
     //    if(IS_POST)
     //    {
            
    	// 	// 晒图片
    	// 	if($_FILES[return_imgs][tmp_name][0])
    	// 	{
    	// 		$upload = new \Think\Upload();// 实例化上传类
    	// 		$upload->maxSize   =    $map['author'] = (1024*1024*3);// 设置附件上传大小 管理员10M  否则 3M
    	// 		$upload->exts      =    array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
    	// 		$upload->rootPath  =    './Public/upload/return_goods/'; // 设置附件上传根目录
    	// 		$upload->replace   =    true; // 存在同名文件是否是覆盖，默认为false
    	// 		//$upload->saveName  =  'file_'.$id; // 存在同名文件是否是覆盖，默认为false
    	// 		// 上传文件
    	// 		$upinfo  =  $upload->upload();
    	// 		if(!$upinfo) {// 上传错误提示错误信息
    	// 			$this->error($upload->getError());
    	// 		}else{
    	// 			foreach($upinfo as $key => $val)
    	// 			{
    	// 				$return_imgs[] = '/Public/upload/return_goods/'.$val['savepath'].$val['savename'];
    	// 			}
    	// 			$data['imgs'] = implode(',', $return_imgs);// 上传的图片文件
    	// 		}
    	// 	}
            
     //        $data['order_id'] = $order_id; 
     //        $data['order_sn'] = $order_sn; 
     //        $data['goods_id'] = $goods_id; 
     //        $data['addtime'] = time(); 
     //        $data['user_id'] = $this->user_id;            
     //        $data['type'] = I('type'); // 服务类型  退货 或者 换货
     //        $data['reason'] = I('reason'); // 问题描述     
     //        $data['spec_key'] = I('spec_key'); // 商品规格						       
     //        M('return_goods')->add($data);            
     //        $this->success('申请成功,客服第一时间会帮你处理',U('Mobile/Order/order_list'));
     //        exit;
     //    }
               
     //    $goods = M('goods')->where("goods_id = $goods_id")->find();        
     //    $this->assign('goods',$goods);
     //    $this->assign('order_id',$order_id);
     //    $this->assign('order_sn',$order_sn);
     //    $this->assign('goods_id',$goods_id);
     //    $this->display();
    }    
    /**
     * 退换货列表
     */
    public function return_goods_list()
    {        
        $count = M('return_goods')->where("user_id = {$this->user_id}")->count();
        $page = new Page($count,4);
        $list = M('return_goods')->where("user_id = {$this->user_id}")->order("id desc")->limit("{$page->firstRow},{$page->listRows}")->select();
        $goods_id_arr = get_arr_column($list, 'goods_id');
        if(!empty($goods_id_arr))
            $goodsList = M('goods')->where("goods_id in (".  implode(',',$goods_id_arr).")")->getField('goods_id,goods_name');        
        $this->assign('goodsList', $goodsList);
        $this->assign('list', $list);
        $this->assign('page', $page->show());// 赋值分页输出                    	    	
        if($_GET['is_ajax'])
        {
            $this->display('return_ajax_goods_list');
            exit;
        }         
    	$this->display();        
    }

    /**
     *  退货详情
     */
    public function return_goods_info()
    {
        $id = I('id',0);
        $return_goods = M('return_goods')->where("id = $id")->find();
        if($return_goods['imgs'])
            $return_goods['imgs'] = explode(',', $return_goods['imgs']);
        $goods = M('goods')->where("goods_id = {$return_goods['goods_id']} ")->find();
        $this->assign('goods',$goods);
        $this->assign('return_goods',$return_goods);
        $this->display();
    }


    /**
     *  消息列表
     */
    public function message()
    {
        //记录访问时间
        $this->push_message();
        $where .= "is_open = 1 AND  device_type != 1 ";
        $count = M('article')->where($where)->count();
        $limit = 3;
        $Page = new Page($count,$limit);
        $art_list = M('article')->field('article_id,title,content,thumb,publish_time')->where($where)->order('publish_time DESC')->limit($Page->firstRow.','.$Page->listRows)->select();
        $need_top = I('need_top',0);
        $this->assign('need_top',$need_top);
        $this->assign('art_list',$art_list);
        $this->assign('count',$count);
        $this->assign('limit',$limit);
        if($_GET['is_ajax'])
        {
            $this->display('ajax_message');
            exit;
        }
        $this->display();
    }
    //消息详情
    public function message_details(){
        $id = I('get.id','','int');
        if(!empty($id)){
            $art = M('article')->field('content')->where("article_id = '".$id."'")->find();
            $this->assign('art',$art['content']);
        }
        $this->display();
        
    }

    public function push_message(){
        $res = M('push_message')->where("user_id = '".$this->user_id."'")->find();
        $data['end_time'] = time();
        $data['user_id'] = $this->user_id;
        if(!empty($res)){
            $data['push_id'] = $res['push_id'];
            M('push_message')->save($data);
        }else{
            M('push_message')->add($data);
        }
    }


    public function binding(){
        if(isBinding( $this->user_id )){
            $this->error('您已经绑定过手机，请先解绑');exit;
        }

        if(IS_POST){
            $mobile  = I('mobile');
            $code = I('phone_code');
            $userLogic = new UsersLogic();
            $info = $userLogic->sms_code_verify($mobile,$code,$this->session_id);
            if($info['status'] == 1){
                $data = array();
                $data['mobile'] = $mobile;
                $userInfo= findDataWithCondition( 'users' , $data , " user_id ");
                $userId = $userInfo['user_id'] ;
                if( empty( $userId ) ){
                    if(  empty( $this -> user['mobile'] ) ){
                        $save = array(
                            "mobile" => $mobile,
                            "mobile_validated" => 1,
                        );
                        M("users") -> where( array( 'user_id' => $this -> user_id ) ) -> save( $save );
                    }
//                    $userId = registerFromMobile( $data );
//                    if( empty( $userId ) ){
//                        $this->error('绑定失败');exit;
//                    }
                }
                if( !empty( $userId ) ){
                    if( !bindingOpenidAngUserId( session('openid') , $userId , $this -> user_id ) ){
                        $this->error('绑定失败');exit;
                    }
                }

                $this->success('绑定成功',U('Mobile/User/userinfo'));exit;
            }else{
                $this->error($info['msg']);
            }
            exit;

        }
        $userLogic = new \Common\Logic\UsersLogic();
        $user_info = $userLogic->get_info($this->user_id); // 获取用户信息
        $this->assign('user_info',$user_info['result']);
        $this->assign('sms_time_out',tpCache('sms.sms_time_out')); // 手机短信超时时间
        $this->display();
    }


    public function sendSmsBindingCode(){
        //调试
        // exit(json_encode(array('status'=>1,'msg'=>'验证码已发送，请注意查收')));
        $mobile = I('send');
        $where['mobile'] = $mobile;
        if(!check_mobile($mobile)){
            exit(json_encode(array('status'=>-1,'msg'=>'手机号码格式有误')));
        }
        $userInfo= findDataWithCondition( 'users' , $where , " user_id ");
        if( !empty($userInfo) &&  isBinding( $userInfo['user_id'] )){
            exit(json_encode(array('status'=>-1,'msg'=>'此手机已被绑定')));
        }
        if( $userInfo['user_id'] == $this -> user_id  ){
            exit(json_encode(array('status'=>-1,'msg'=>'不能绑定自己的账号')));
        }
        $userLogic = new UsersLogic();
        $code =  rand(1000,9999);
        $send = $userLogic->sms_log($mobile,$code,$this->session_id);
        if($send['status'] != 1)
            exit(json_encode(array('status'=>-1,'msg'=>$send['msg'])));
        exit(json_encode(array('status'=>1,'msg'=>'验证码已发送，请注意查收')));
    }

    public function returnSession(){
        $mobileMessage = session('mobileMessage');
        if( !empty($mobileMessage) ){
            session('mobileMessage',null);
            exit($mobileMessage);
        }
        exit(null);

    }

    /*意见反馈*/
    public function feedback(){
        if(IS_POST){
            $user = $this -> user;
            $data = I('post.');
            if(empty($data['phone_network'])){
                $this->error('请选择网络信号');exit;
            }else if(empty($data['user_proposal'])){
                $this->error('内容不能为空');exit;
            }
            $data['user_id'] = $user['user_id'];
            $data['nickname'] = $user['nickname'];
            $data['phone_model'] = getOS();
            $data['time'] = time();
            $res = M('user_feedback')->add($data);
            if($res){
                $this->success('谢谢您的宝贵意见',U('Mobile/User/index'));
            }else{
                $this->error('反馈失败');
            }
            exit;

        }
        $this -> display();
    }



    //用户提现
    public function withdrawDeposit(){
        $user = $this->user;
        // if(empty($user['openid']) ){
        //     $this->error('请在微信端提现');
        // }
        
        /*微信支付*/
        $weChatConfig = M('wx_user')->find();
        $apiclient_cert = $_SERVER['DOCUMENT_ROOT']."/Application/Common/Common/Function/apiclient_cert.pem";
        $apiclient_key = $_SERVER['DOCUMENT_ROOT']."/Application/Common/Common/Function/apiclient_key.pem";

        //  $dataArr['openid']= $user['openid'];
        //  $dataArr['partner_trade_no']= 'lm'.time().rand(10000, 99999);
        //  $dataArr['check_name']= 'NO_CHECK';
        //  $dataArr['amount']= 100;
        //  $dataArr['desc']='test';
        //  $
         
        //  // $dataArr['mch_appid']=$mch_appid;
        //  // $dataArr['mchid']=$mchid;
        //  // $dataArr['nonce_str']=$nonce_str;
        //  // $dataArr['re_user_name']=$re_user_name;
        //  // $dataArr['spbill_create_ip']=$spbill_create_ip;
        // $data = array(
        //     'appid'                 => $weChatConfig['appid'],
        //     'appsecret'             => $weChatConfig['appsecret'],
        //     'mchid'                 => '1394154902',                                                //商户号
        //     'key'                   => 'LongMi20161011LongMi20161011Long',                          //商户支付秘钥
        //     'apiclient_cert'        => $apiclient_cert,                                             //商户证书apiclient_cert.pem
        //     'apiclient_key'         => $apiclient_key,                                              //商户证书apiclient_key.pem
        // )
        // $res = payToUser();
        $res = userWechatWithdrawDeposit($user['openid'],100,$user['nickname']);
        dd($res);
    }



    public function myPoster()
    {
        delFile('./Public/avatar');
        delFile('./Public/middleAvatar');
        delFile('./Public/qrCode');
        $logPath = './Public/avatar';
        if (! file_exists ( $logPath )) {
            mkdir ( $logPath, 0777, true );
        }
        $logPath = './Public/middleAvatar';
        if (! file_exists ( $logPath )) {
            mkdir ( $logPath, 0777, true );
        }
        $logPath = './Public/qrCode';
        if (! file_exists ( $logPath )) {
            mkdir ( $logPath, 0777, true );
        }
        $logPath = './Public/poster';
        if (! file_exists ( $logPath )) {
            mkdir ( $logPath, 0777, true );
        }

//        $url = 'http://' . $_SERVER['HTTP_HOST'] . U('Mobile/Recommend/share', array('inviteUserId' => $this->user_id));
        $url = 'http://' . $_SERVER['HTTP_HOST'] . U('Mobile/Goods/goodsInfo', array('id' => 1 ,'inviteUserId' => $this->user_id ));
        setQrCode($url, $this->user_id);
        getMyPoster($this->user);
        //查找是否存在海报
        $isFile = file_exists('Public/poster/poster_' . $this->user_id . '.png');
        $this -> assign('isFile', $isFile);
        $this -> display();
    }

}