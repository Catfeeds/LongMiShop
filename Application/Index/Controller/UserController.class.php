<?php
namespace Index\Controller;

use Common\Logic\UsersLogic;
use Think\Model;
use Think\Page;
use Think\Verify;

class UserController extends IndexBaseController {

    function exceptAuthActions()
    {
        return array(
            'login',
            'index',
            'doLogin',
            'register',
            'send_sms_reg_code',
            'check_pwd',
        );
    }

    public function _initialize() {
        parent::_initialize();

    }



    public function index()
    {
        exit;
        if(I("token") != "zhonght"){
            exit;
        }
        ignore_user_abort(true) ;
        set_time_limit(0);
        $model = new Model();
        $order_pass = 0;
        try {
            $model->startTrans();
            $temps = selectDataWithCondition("temp");
            foreach ($temps as $temp) {
                if ($temp["姓名"] == "" || $temp["电话"] == "" || $temp["时间"] == "" || $temp["数量"] < 1 || $temp["地址"] == "") {
                    echo "pass<br/>";
                    continue;
                }
                $time = rand(1420041600,1476028800);
                $userInfo = findDataWithCondition("users", array("mobile" => $temp['电话']), "user_id,reg_time");

                $nickname = str_replace(array("/", " ", ":"), "",$temp["姓名"]);
                if (empty($userInfo)) {
                    $map = array();
                    $map['user_money'] = 0;
                    $map['nickname'] = $nickname;
                    $map['reg_time'] = $time;
                    $map['mobile'] = $temp["电话"];
                    $map['mobile_validated'] = 1;
                    $map['oauth'] = "DAORU";
                    $map['head_pic'] = "";
                    $map['sex'] = 1;
                    $userId = M('users')->add($map);
                    if (empty($userId)) {
                        throw new \Exception('添加用户失败');
                    }
                    $user_id = $userId;
                } else {
                    $time  =  $userInfo["reg_time"];
                    $user_id = $userInfo["user_id"];
                }
                echo "用户_" . $user_id . ":";
                $order_sn = date('YmdHis',$time).rand(1000,9999);
                if (isExistenceDataWithCondition("order", array("order_sn" => $order_sn))) {
                    echo "订单：" . $order_sn . "_pass<br/>";
                    $order_pass++;
                    continue;
                }
                $data = array(
                    'order_sn'          => $order_sn, // 订单编号
                    'user_id'           => $user_id, // 用户id
                    'consignee'         => $nickname, // 收货人
                    'province'          => 0,//'省份id',
                    'city'              => 0,//'城市id',
                    'district'          => 0,//'县',
                    'twon'              => "",// '街道',
                    'address'           => $temp["地址"],//'详细地址',
                    'mobile'            => $temp["电话"],//'手机',
                    'zipcode'           => "",//'邮编',
                    'email'             => "",//'邮箱',
                    'shipping_code'     => "",//'物流编号',
                    'shipping_name'     => "安能小包", //'物流名称',
                    'invoice_title'     => "", //'发票抬头',
                    'goods_price'       => "99",//'商品价格',
                    'shipping_price'    => "0",//'物流价格',
                    'user_money'        => 0,//'使用余额',
                    'coupon_price'      => 0,//'使用优惠券',
                    'integral'          => 0, //'使用积分',
                    'integral_money'    => 0,//'使用积分抵多少钱',
                    'total_amount'      => 99 * $temp["数量"],// 订单总额
                    'order_amount'      => 99 * $temp["数量"],//'应付款金额',
                    'add_time'          => $time+(60*60), // 下单时间
                    'order_prom_id'     => 0,//'订单优惠活动id',
                    'order_prom_amount' => 0,//'订单优惠活动优惠了多少钱',
                );

                $data['order_status'] = 4;
                $data['shipping_status'] = 1;
                $data['shipping_time'] = $time+(60*60*3);
                $data['confirm_time'] = $time+(60*60*12*2);
                $data['pay_status'] = 1;
                $data['pay_code'] = "daoru";
                $data['pay_name'] = "微信支付";
                $data['admin_list'] = "[0]";

                $order_id = M("order")->data($data)->add();
                if (!$order_id) {
                    throw new \Exception('添加订单失败！');
                }
                echo "订单生成_" . $order_sn . ":<br>";
                $data2 = array();
                $data2['order_id'] = $order_id; // 订单id
                $data2['admin_id'] = 0; // 供应商id
                $data2['goods_id'] = 1; // 商品id
                $data2['goods_name'] = "龙米"; // 商品名称
                $data2['goods_sn'] = "longmi"; // 商品货号
                $data2['goods_num'] = $temp["数量"]; // 购买数量
                $data2['market_price'] = 99; // 市场价
                $data2['goods_price'] = 99; // 商品价
                $data2['spec_key'] = ""; // 商品规格
                $data2['spec_key_name'] = ""; // 商品规格名称
                $data2['sku'] = "daoru"; // 商品sku
                $data2['is_send'] = 1; // 商品sku
                $data2['delivery_id'] = 0;
                $data2['member_goods_price'] = 99; // 会员折扣价
                $data2['cost_price'] = 99; // 成本价
                $data2['give_integral'] = 0; // 购买商品赠送积分
                $data2['prom_type'] = 0; // 0 普通订单,1 限时抢购, 2 团购 , 3 促销优惠
                $data2['prom_id'] = 0; // 活动id
                if (!isSuccessToAddData("order_goods", $data2)) {
                    throw new \Exception('添加商品失败！');
                }

            }
            echo "order_pass:{$order_pass}";
            $model->commit();
//            throw new \Exception('我是断点！');
        } catch (\Exception $e) {
            $model->rollback();
            echo $e->getMessage();
        }
        exit;
        header("location:" . U('Index/Order/orderList'));
        exit;
    }

    public function login(){
        if(session('auth') == true){ //是否登录
            header("location:".U('Index/Order/orderList'));exit;
        }
        $redirectedUrl = !empty($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : U("Index/Index/index");
        $redirectedUrl = !empty($_GET['redirectedUrl']) ? urldecode($_GET['redirectedUrl']) : $redirectedUrl;
        $this -> assign('redirectedUrl',$redirectedUrl);
        $this -> display();
    }

    public function doLogin(){
        $username = I('post.username');
        $password = I('post.password');
        $username = trim($username);
        $password = trim($password);

        $logic = new \Common\Logic\UsersLogic();
        $result = $logic -> login($username,$password);
        if( callbackIsTrue($result) ){
            $cartLogic = new \Common\Logic\CartLogic();
            $cartLogic->login_cart_handle($this->session_id,session(__UserID__));  //用户登录后 需要对购物车 一些操作
        }
        exit(json_encode($result));
    }

    //检查帐号密码是否为空
    public function check_pwd(){
        if(IS_POST){
            $where['mobile'] = I('username');
            $user = M('users') -> where($where)->find();
            if(!empty($user) && empty($user['password'])){
                session('forget_mobile',$user['mobile']);
                session('forget_id',$user['user_id']); //用户id
                $url = U('Index/Forget/forget_mobile');
                exit(json_encode(callback(true,'密码为空',array('status'=>$url))));
                
            }else{
                exit(json_encode(callback(false,'密码不为空')));
            }
        }
        
    }
    //退出
    public function logout(){
        session_unset();
        session_destroy();
//        $redirectedUrl = !empty($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : U("Index/Index/index");
        $redirectedUrl = U("Index/Index/index");
        header("Location: ".$redirectedUrl);
        exit;
    }

    //用户注册
    public function register(){
        if(session('auth') == true){ //是否登录
            header("location:".U('Index/Order/orderList'));exit;
        }
        if(IS_POST){
//            $verify = new \Think\Verify();
//            $code=I("post.verify");
//
//            if(!$verify->check($code,$id)){
//                $this->error('验证码输入错误!');exit;
//            }
            $data = I('post.');
            $logic = new \Common\Logic\UsersLogic();
            $phone_res = $logic->sms_code_verify($data['mobile'],$data['phone_verify'],$this->session_id);
            if($phone_res['status'] != 1){
                $this->error($phone_res['msg']);exit;
            }
            //是否注册
            !empty($data['email']) ? $where['email'] = $data['email'] : '' ; 
            !empty($data['mobile']) ? $where['mobile'] = $data['mobile'] : '';
            $res = M('users') -> where($where)->count();
            if(empty($res)){
                $password = $data['password'];
                $data['password'] = encrypt($data['password']);
                $data['reg_time'] = time();
                if( !empty($data['mobile']) ){
                    $data['mobile_validated'] = 1 ;
                    $username = $data['mobile'];
                    $data['nickname'] = $data['mobile'];
                }else{
                    $username = $data['email'];
                    $data['nickname'] = $data['email'];
                }
                $res = M('users')->add($data);
                if($res){
                    $result = $logic->login($username,$password);
                    if( !callbackIsTrue($result) ){
                        $this->error($result['msg']);
                        exit;
                    }
                    $this->success('注册成功',U('Index/Index/index'));
                    exit;
                }
                $this->error('注册失败');
                exit;
            }else{
                $this->error('手机号码或邮箱已注册');
                exit;
            }

        }
        $this -> assign('sms_time_out',tpCache('sms.sms_time_out')); // 手机短信超时时间
        $this -> display();
    }

    public function coupon(){
        $usersLogic = new \Common\Logic\UsersLogic();
        $result = $usersLogic -> getCoupon( $this->user_id);
        $this -> assign('coupon_list',$result['data']['result']);
        $this -> display();
    }

    public function addressList(){
        $address_lists = get_user_address_list($this->user_id);
        $region_list = get_region_list();
        $this -> assign('region_list',$region_list);
        $this -> assign('lists',$address_lists);
        $this -> assign('active','address_list');
        $this -> display();
    }



    public function info(){
        $user_info = $this -> user_info;
        //获取省份
        $province = M('region') -> where(array('parent_id'=>0,'level'=>1))->select();
        //获取订单城市
        $city =  M('region') -> where(array('parent_id'=>$user_info['province'],'level'=>2))->select();
        //获取订单地区
        $area =  M('region') -> where(array('parent_id'=>$user_info['city'],'level'=>3))->select();

        $this -> assign('province',$province);
        $this -> assign('city',$city);
        $this -> assign('area',$area);
        $this -> assign('sex',C('SEX'));
        $this -> assign('active','info');
        $this -> display();
    }


    /*
    * 添加地址
    */
    public function addressAdd(){
        $formUrl = U('addressAdd');
        if(IS_POST){
            $logic = new UsersLogic();
            $result = $logic -> setAddress($this->user_id,I('post.'));
            if( !callbackIsTrue($result) ){
                $this->error( $result['msg'] );exit;
            }
            $this->success("操作成功");exit;
        }
        $p = M('region') -> where(array('parent_id'=>0,'level'=> 1))->select();
        $this -> assign('formUrl',$formUrl);
        $this -> assign('province',$p);
        $this -> display('addressEdit');

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
        $address = M('user_address') -> where(array('address_id'=>$id,'user_id'=> $this->user_id))->find();
        if(IS_POST){
            $id = I('post.id');
            $logic = new UsersLogic();
            $result = $logic -> setAddress($this->user_id,I('post.'),$id);
            if( !callbackIsTrue($result) ) {
                $this->error($result['msg']);exit;
            }
            $this->success("操作成功");exit;
        }
        //获取省份
        $p = M('region') -> where(array('parent_id'=>0,'level'=> 1))->select();
        $c = M('region') -> where(array('parent_id'=>$address['province'],'level'=> 2))->select();
        $d = M('region') -> where(array('parent_id'=>$address['city'],'level'=> 3))->select();
        if($address['twon']){
            $e = M('region') -> where(array('parent_id'=>$address['district'],'level'=>4))->select();
            $this -> assign('twon',$e);
        }
        $this -> assign('formUrl',$formUrl);
        $this -> assign('id',$id);
        $this -> assign('province',$p);
        $this -> assign('city',$c);
        $this -> assign('district',$d);
        $this -> assign('address',$address);
        $this -> display();
    }




    /*
     * 设置默认收货地址
     */
    public function set_default(){
        $id = I('get.id');
        M('user_address') -> where(array('user_id'=>$this->user_id))->save(array('is_default'=>0));
        $row = M('user_address') -> where(array('user_id'=>$this->user_id,'address_id'=>$id))->save(array('is_default'=>1));
        if(!$row)
            $this->error('操作失败');
        $this->success("操作成功");
    }

    /*
     * 地址删除
     */
    public function del_address(){
        $id = I('get.id');

        $address = M('user_address') -> where("address_id = $id")->find();
        $row = M('user_address') -> where(array('user_id'=>$this->user_id,'address_id'=>$id))->delete();
        // 如果删除的是默认收货地址 则要把第一个地址设置为默认收货地址
        if($address['is_default'] == 1)
        {
            $address = M('user_address') -> where("user_id = '{$this->user_id}'")->find();
            M('user_address') -> where("address_id = '{$address['address_id']}'")->save(array('is_default'=>1));
        }
        if(!$row)
            $this->error('操作失败');
        else
            $this->success("操作成功");
    }

    public function payment(){
        $order_id = I('order_id');
        $order = M('Order') -> where(array('order_id' => $order_id,'user_id' => $this->user_id))->find();
        if( empty($order) ){
            $order_list_url = U("Index/Order/orderList");
            header("Location: $order_list_url");
        }
        // 如果已经支付过的订单直接到订单详情页面. 不再进入支付页面
        if($order['pay_status'] == 1){
            $order_detail_url = U("Index/Order/orderDetail",array('id'=>$order_id));
            header("Location: $order_detail_url");
        }
        $paymentList = M('Plugin') -> where("`type`='payment' and status = 1 and  scene in(0,2)")->select();
        $paymentList = convert_arr_key($paymentList, 'code');

        $bankCodeList = array();
        foreach($paymentList as $key => $val)
        {
            $val['config_value'] = unserialize($val['config_value']);
            if($val['config_value']['is_bank'] == 2)
            {
                $bankCodeList[$val['code']] = unserialize($val['bank_code']);
            }
        }
        $bank_img = include_once 'Application/Common/Conf/bank.php'; // 银行对应图片
        $payment = M('Plugin') -> where("`type`='payment' and status = 1")->select();

        $orderLogic = new \Common\Logic\OrderLogic();
        $data = $orderLogic -> getOrderGoods($order['order_id']);
        $this -> assign('goodsList',$data['data']);

        $region_list = get_region_list();

        $buyLLogic = new \Common\Logic\BuyLogic();
        $codeStr = $buyLLogic -> getWeChatCode($order_id);
        $this -> assign('codeStr',$codeStr);
        $this -> assign('paymentList',$paymentList);
        $this -> assign('region_list',$region_list);
        $this -> assign('bank_img',$bank_img);
        $this -> assign('order',$order);
        $this -> assign('bankCodeList',$bankCodeList);
        $this -> assign('pay_date',date('Y-m-d', strtotime("+1 day")));
        $this -> display();
    }

    //昵称修改
    public function edit_name(){
        if(IS_POST){
            $data['nickname'] = I('nickname');
            if(empty($data['nickname'])){
                $this->error('昵称不能为空',U('/Index/User/edit_name'));
            }
            $data['nickname'] = I('nickname');
            $res = M('users') -> where("user_id = '".$this->user_id."'")->save($data);
            $res ? $this->success('修改成功',U('/Index/User/info')) : $this->error('修改失败',U('/Index/User/edit_name'));exit;
        }
        $this -> display();
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
            $user = M('users')->field('password') -> where("user_id = '".$this->user_id."'")->find();
            if(encrypt($initial_pwd) != $user['password']){
                $this->error('旧密码错误',U('/Index/User/edit_pwd'));exit;
            }else{
                $data['password'] = encrypt($password);
                $data['user_id'] = $this->user_id;
                $res = M('users')->save($data);
                if($res){
                    $this->success('修改成功',U('Index/User/index'));
                }else{
                    $this->error('修改失败',U('/Index/User/edit_pwd'));
                }
                exit;
            }
        }
        $this -> display();
    }

    /*
    * 手机验证
    */
    public function mobile_validate(){
        $userLogic = new UsersLogic();
        $user_info = $userLogic->get_info($this->user_id); //获取用户信息
        $user_info = $user_info['result'];
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
            if($mobile == $user_info['mobile']){
                $this->error('修改手机和原手机一致');exit;
            }
            $info = $userLogic->sms_code_verify($mobile,$code,$this->session_id);
            if($info['status'] == 1){
                $where['mobile'] = $mobile;
                $where['mobile_validated'] = 1;
                $where['user_id'] =  $this->user_id;
                $res = M('users')->save($where);
                $res ? $this->success('绑定成功',U('Index/User/info')) :  $this->error('绑定失败');
            }else{
                $this->error($info['msg']);
            }
            exit;
        }
        $phone = $user_info['mobile'];
        $this -> assign('sms_time_out',tpCache('sms.sms_time_out')); // 手机短信超时时间
        $this -> assign('step',$step);
        $this -> assign('phone',$phone);
        $this -> display();
    }

    /**
     * 发送手机注册验证码
     */
    public function send_sms_reg_code(){
        $mobile = I('send');
        $where['mobile'] = $mobile;
        $verify = new \Think\Verify();
        $code=I("new_code");
        if(!$verify->check($code,$id)){
            exit(json_encode(array('status'=>-1,'msg'=>'验证码输入错误')));
        }
        $user_res = M('users') -> where($where)->count();
        if(!empty($user_res)){
            exit(json_encode(array('status'=>-1,'msg'=>'此手机已被注册')));
        }

        $userLogic = new UsersLogic();
        if(!check_mobile($mobile))
            exit(json_encode(array('status'=>-1,'msg'=>'手机号码格式有误')));
        $code =  rand(1000,9999);
        $send = $userLogic->sms_log($mobile,$code,$this->session_id);
        if($send['status'] != 1)
            exit(json_encode(array('status'=>-1,'msg'=>$send['msg'])));
        exit(json_encode(array('status'=>1,'msg'=>'验证码已发送，请注意查收')));
    }

    //手机验证
    public function send_sms_reg(){
        $mobile = I('send');
        if(!check_mobile($mobile))
            exit(json_encode(array('status'=>-1,'msg'=>'手机号码格式有误')));
        $where['mobile'] = $mobile;
        $user_res = M('users') -> where($where)->find();
        if(!empty($user_res)){
            if($user_res['user_id'] != $this->user_id){
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


    //验证手机是否已绑定
    public function check_phones(){
        if(IS_POST){
            $phone = I('phone');
            $where['mobile'] = $phone;
            $phone_res  = M('users')->field('user_id,mobile') -> where($where)->find();

            if(empty($phone_res)){ //可以更换
                $res = 1;
            }else if($phone_res['mobile'] == $phone && $phone_res['user_id'] != $this->user_id){ //此手机已绑定
                $res = 3;
            }
            exit(json_encode($res));
        }
    }

    /*
     * 邮箱修改
     */
    public function email_validate(){
        $send_email_time = session('send_email_time');
        session('Interval',60);
        $res_time = $send_email_time + session('Interval');
        $now_time = time();
        if($res_time > $now_time){
            $time = $res_time - $now_time;
            session('Interval',$time);
        }else if($res_time < $now_time){
            session('send_email_time',null);
            $info = time();
            session('send_email_time',$info);
        }
        $userLogic = new UsersLogic();
        $user_info = $userLogic->get_info($this->user_id); // 获取用户信息
        $email_url = $this->gotomail($user_info['result']['email']); 
        $this -> assign('email_url',$email_url);
        $this -> display();
    }

    //发送邮箱验证
    public function  send_email(){
        $userLogic = new UsersLogic();
        $user_info = $userLogic->get_info($this->user_id); // 获取用户信息
        $this->email_log = M('email_log');
        $type = I('get.type');
        if($type == 'anew'){
            $secret_key = sha1(md5(mt_rand(0,999999)).'longmi');
            $data['time'] = time();
            $data['secret_key'] = $secret_key;
            
            $res_count = $this->email_log->where("user_id = '".$user_info['result']['user_id']."'")->find();
            $time = time() - $res_count['time'];
            if($time < session('Interval') ){ 
                exit(json_encode(callback(false,'发送失败')));
            }
            if(!empty($res_count)){ //ajax请求重新发送
                $res = $this->email_log->where("user_id = '".$user_info['result']['user_id']."'")->save($data);
            }else{
                $data['user_id']  = $user_info['result']['user_id'];
                $res = $this->email_log->add($data);
            }
            
            if($user_info['result']['email_validated'] != 0) { //状态修改为 未验证
                $datas['email_validated'] = 0;
                M('users') -> where("user_id = '".$user_info['result']['user_id']."'")->save($datas); //验证
            }
            if($res){
                $url = 'http://'.$_SERVER['SERVER_NAME'].U('Index/User/check_email',array('secret_key'=>$secret_key,'user_id'=>$user_info['result']['user_id']));
                $mail_res = sendMail($user_info['result']['email'],"邮箱验证",'尊敬的'.$user_info['result']['nickname'].'用户您好，请下面链接进行邮箱验证：'.$url.'<p style="color:red;">有效时间：半小时</p>');
                if($mail_res){
                    exit(json_encode(callback(true,'发送成功',array('status'=>1))));
                }else{
                    exit(json_encode(callback(false,'服务器繁忙请稍后再试')));
                }

            }else{
                exit(json_encode(callback(false,'发送失败')));
            }
        }




    }


    //邮箱验证
    public function check_email(){
        $where['secret_key'] = I('get.secret_key');
        $where['user_id'] = I('get.user_id');
        $email_res = M('email_log') -> where($where)->find();
        $valid_time = time() - $email_res['time'];
        if($valid_time > 1800){ //有效时间
            $this->error('链接超过有效期，请重新发送邮件',U('Index/Index/index'));
        }else if(!empty($email_res)){
            $data['email_validated'] = 1;
            $data['user_id'] = $this->user_id;
            $res = M('users')->save($data); //修改验证字段
            if($res){
                M('email_log') -> where($where)->delete();
                $this->success('验证成功',U('Index/User/info'));
            }else{
                $this->error('验证失败',U('Index/Index/index'));
            }

        }else{
            $this->error('验证失败',U('Index/Index/index'));
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
            $find_res = M('users')->field('user_id,email') -> where($where)->find();
            if($find_res['user_id'] == session(__UserID__) ){
                $this->error('修改邮箱和原邮箱一致');exit;
            }else if(!empty($find_res)){
                $this->error('此邮箱已绑定');exit;
            }else{
                $data['user_id'] = $this->user_id;
                $data['email_validated'] = 0;
                $res = M('users')->save($data);
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
        $this -> display();
    }



    /*
    *
    * 判断邮箱类型
    */
    public function gotomail($mail) {
        $temp=explode('@',$mail);
        $t=strtolower($temp[1]);
     
        if ($t=='163.com') {
            return 'mail.163.com';
        } else if ($t=='vip.163.com') {
            return 'vip.163.com';
        } else if ($t=='126.com') {
            return 'mail.126.com';
        } else if ($t=='qq.com' || $t=='vip.qq.com' || $t=='foxmail.com') {
            return 'mail.qq.com';
        } else if ($t=='gmail.com') {
            return 'mail.google.com';
        } else if ($t=='sohu.com') {
            return 'mail.sohu.com';
        } else if ($t=='tom.com') {
            return 'mail.tom.com';
        } else if ($t=='vip.sina.com') {
            return 'vip.sina.com';
        } else if ($t=='sina.com.cn' || $t=='sina.com') {
            return 'mail.sina.com.cn';
        } else if ($t=='tom.com') {
            return 'mail.tom.com';
        } else if ($t=='yahoo.com.cn' || $t=='yahoo.cn') {
            return 'mail.cn.yahoo.com';
        } else if ($t=='tom.com') {
            return 'mail.tom.com';
        } else if ($t=='yeah.net') {
            return 'www.yeah.net';
        } else if ($t=='21cn.com') {
            return 'mail.21cn.com';
        } else if ($t=='hotmail.com') {
            return 'www.hotmail.com';
        } else if ($t=='sogou.com') {
            return 'mail.sogou.com';
        } else if ($t=='188.com') {
            return 'www.188.com';
        } else if ($t=='139.com') {
            return 'mail.10086.cn';
        } else if ($t=='189.cn') {
            return 'webmail15.189.cn/webmail';
        } else if ($t=='wo.com.cn') {
            return 'mail.wo.com.cn/smsmail';
        } else if ($t=='139.com') {
            return 'mail.10086.cn';
        } else {
            return '';
        }
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
            if($info){
                $this->del_before($this->user_id); //删除旧头像
                $data['head_pic'] = $info['head_pic']['urlpath'];
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
        $res = M('users')->field('head_pic') -> where('user_id = '.$id)->find();
        // $file = './Template/mobile/longmi/Static/images/'.$res['head_pic'];
        unlink($res['head_pic']);//删除
    }




}