<?php
namespace Index\Controller;
use Common\Logic\UsersLogic;
use Think\Verify;

class ForgetController extends BaseIndexController {

    function exceptAuthActions()
    {
        return array(
            "index",
            'forget_mobile',
            'forget_email',
            'send_email',
            'check_forget',
            'send_sms_reg_code',
            'check_forget_mobile'



        );
    }

    public function _initialize() {
        parent::_initialize();
        
    }

    public function index(){
        if(IS_POST){
            $verify = new \Think\Verify();
            $code=I("verify");
            if(!$verify->check($code,$id)){
                $this->error('验证码输入错误!',U('Index/Forget/index'));
            }
            $genre = I('genre');
            if(is_numeric($genre)){ //数字
                if(!check_mobile($genre)){
                    $this->error('手机格式不对',U('Index/Forget/index'));exit;
                }
                $where['mobile'] = $genre;
            }else{
                if(!check_email($genre)){
                    $this->error('邮箱格式不对',U('Index/Forget/index'));exit;
                }
                $where['email'] = $genre;
            }

            $res = M('users')->where($where)->find();
            if(empty($res)){
                $this->error('帐号不存在，请重新输入',U('Index/Forget/index'));exit;
            }

            if(is_numeric($genre)){
                session('forget_mobile',$res['mobile']);
                session('forget_id',$res['user_id']); //用户id
                header('Location: '.U('Index/Forget/forget_mobile'));exit;
            }else{
                // dd($res['email']);
                session('forget_email',$res['email']); 
                session('forget_id',$res['user_id']); //用户id
                session('forget_email_nickname',$resp['nickname']); //用户名字
                header('Location: '.U('Index/Forget/forget_email'));exit;
            }




        }
        $this->display();
    }


    //手机验证密码修改视图
    public function forget_mobile(){
        $mobile = session('forget_mobile');
        if(empty($mobile)){
            $this->error('参数错误');exit;
        }
        session('forget_time',60);
        $send_email_time = session('send_email_time');
        $res_time = $send_email_time + session('forget_time');
        $now_time = time();
        if($res_time > $now_time){
            $time = $res_time - $now_time;
            session('forget_time',$time);
        }else if($res_time < $now_time){
            session('send_email_time',null);
            $info = time();
            session('send_email_time',$info);
        }

        if(IS_POST){
            $where['mobile'] = session('forget_mobile');
            $where['code'] = I('code');
            $where['session_id'] = session('forget_id');
            $res = M('sms_log')->where($where)->count();
            if($res){
                session('check_forget_mobile',true);
                $this->success('验证码正确，请填写新密码',U('Index/Forget/check_forget_mobile'));
            }else{
                $this->error('验证码错误');
            }
            exit;
        }

        $this->assign('mobile',$mobile);
        $this->display();
    }

    //手机验证密码修改
    public function check_forget_mobile(){
        $check_forget_mobile = session('check_forget_mobile');
        if(empty($check_forget_mobile)){
            $this->error('参数错误',U('Index/Forget/index'));exit;
        }
        if(IS_POST){
            $data['password'] = encrypt(I('password'));
            $data['user_id'] = session('forget_id');
            $detection = M('users')->where($data)->count();
            if($detection){
                $this->error('修改密码不能和旧密码一致');exit;
            }
            $res = M('users')->save($data);
            if($res){
                $wheres['session_id'] = session('forget_id');
                M('sms_log')->where($wheres)->delete();
                session_unset();
                session_destroy();
               $this->success('修改成功,请用新密码登录',U('Index/Index/index'));
            }else{
                $this->error('修改失败',U('Index/Forget/index'));
            }
            exit;
        }

        $this->display();

    }

    /**
     * 发送手机注册验证码
     */
    public function send_sms_reg_code(){
        
        $mobile = session('forget_mobile');
        $user_id = session('forget_id');
        $userLogic = new UsersLogic();
        
        if(!check_mobile($mobile))
            exit(json_encode(callback(false,'手机号码格式有误')));
        $code =  rand(1000,9999);
        $send = $userLogic->sms_log($mobile,$code,$user_id);
//        exit(json_encode(callback(false,$send)));
        if($send['status'] != 1)
            exit(json_encode(array('status'=>-1,'msg'=>$send['msg'])));
//            exit(json_encode(callback(false,$send['msg'])));
         exit(json_encode(array('status'=>1,'msg'=>'验证码已发送，请注意查收')));
//        exit(json_encode(callback(true,'验证码已发送，请注意查收',array('status'=>1))));
    }

    //邮件修改视图
    public function forget_email(){
        $email = session('forget_email');
        if(empty($email)){
            $this->error('参数错误',U('Index/Forget/index'));exit;
        }
        session('forget_time',60);
        $send_email_time = session('send_email_time');
        $res_time = $send_email_time + session('forget_time');
        $now_time = time();
        if($res_time > $now_time){
            $time = $res_time - $now_time;
            session('forget_time',$time);
        }else if($res_time < $now_time){
            session('send_email_time',null);
            $info = time();
            session('send_email_time',$info);
        }
        
        $this->display();
    }

    //发送邮件
    public function send_email(){
        $email = session('forget_email');
        $nickname = session('forget_email_nickname');
        $this->email_log = M('email_log');
        $secret_key = sha1(md5(mt_rand(0,999999)).'longmi');
        $data['time'] = time();
        $data['secret_key'] = $secret_key;
        $user_id = session('forget_id');
        
        $res_count = $this->email_log->where("user_id = '".$user_id."'")->find();
        if(!empty($res_count)){
            $time = time() - $res_count['time'];
            if($time < session('forget_time') ){ 
                exit(json_encode(callback(false,'发送失败')));
            }  
        }
        if(!empty($res_count)){ //ajax请求重新发送
            $res = $this->email_log->where("user_id = '".$user_id."'")->save($data);
        }else{
            $data['user_id']  = $user_id;
            $res = $this->email_log->add($data);
        }
        if($res){
            $url = 'http://'.$_SERVER['SERVER_NAME'].U('Index/Forget/check_forget',array('secret_key'=>$secret_key));
            $mail_res = sendMail($email,"密码修改",'尊敬的'.$nickname.'用户您好，请下面链接进行密码修改：'.$url.'<p style="color:red;">有效时间：半小时</p>');
            if($mail_res){
                exit(json_encode(callback(true,'发送成功',array('status'=>1))));
            }else{
                exit(json_encode(callback(false,'服务器繁忙请稍后再试')));
            }

        }else{
            exit(json_encode(callback(false,'发送失败')));
        }

    }

    //密码修改
    public function check_forget(){
        $user_id = session('forget_id');
        $secret_key = I('secret_key');
        $where['secret_key'] = $secret_key;
        $where['user_id'] = $user_id ;
        $email_res = M('email_log')->where($where)->find();
        if(!empty($email_res)){ //有效时间
            $valid_time = time() - $email_res['time'];
            if($valid_time > 1800){ 
                $this->error('链接超过有效期，请重新发送邮件',U('Index/Forget/index'));
            }
        }else{
           $this->error('链接已失效，请重新发送邮件',U('Index/Forget/index')); 
        }
        if(IS_POST){
            $data['password'] = encrypt(I('password'));
            $data['user_id'] = $user_id;
            $detection = M('users')->where($data)->count();
            if($detection){
                $this->error('修改密码不能和旧密码一致');exit;
            }
            $res = M('users')->save($data);
            if($res){
                session_unset();
                session_destroy();
                M('email_log')->where($where)->delete();
               $this->success('修改成功,请用新密码登录',U('Index/Index/index'));
            }else{
                $this->error('修改失败',U('Index/Forget/index'));
            }
            exit;
        }
        $this->assign('secret_key',$secret_key);
        $this->display();

    }






}