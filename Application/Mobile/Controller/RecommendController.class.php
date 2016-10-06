<?php

namespace Mobile\Controller;

class RecommendController extends MobileBaseController {

    function exceptAuthActions()
    {
        return array(
            'share',
        );
    }
    public function  _initialize() {
        parent::_initialize();
    }

    public function index(){
        $inviteData = getGiftInfo( $this -> shopConfig['prize_invite_value'] , $this -> shopConfig['prize_invite'] );
        $beInviteData = getGiftInfo( $this -> shopConfig['prize_invited_to_value'] , $this -> shopConfig['prize_invited_to'] );
        $this -> assign('inviteData',getCallbackData($inviteData));
        $this -> assign('beInviteData',getCallbackData($beInviteData));
        $this -> assign('number', getInviteNumber($this ->user_id) );
        $this -> display();
    }

    public function recommendList(){
        $list = getInviteList($this ->user_id);
        $this -> assign('list',$list);
        $this -> display();
    }


    public function rule(){
        $inviteData = getGiftInfo( $this -> shopConfig['prize_invite_value'] , $this -> shopConfig['prize_invite'] );
        $beInviteData = getGiftInfo( $this -> shopConfig['prize_invited_to_value'] , $this -> shopConfig['prize_invited_to'] );
        $this -> assign('inviteData',getCallbackData($inviteData));
        $this -> assign('beInviteData',getCallbackData($beInviteData));
        $this -> display();
    }

    public function share(){
        if(IS_POST){
            $inviteUserId  = I('inviteUserId');
            $mobile  = I('new_mobile');
            $code = I('phone_code');
            $userLogic = new \Common\Logic\UsersLogic();
            $info = $userLogic -> sms_code_verify($mobile,$code,$this->session_id);
            if($info['status'] == 1){
                $where['mobile'] = $mobile;
                $where['mobile_validated'] = 1;
                $where['user_id'] =  $this -> user_id;
                $res = M('users')->save($where);
                if($res){
                    $this->success('绑定成功',U('Mobile/Recommend/result',array("inviteUserId"=>$inviteUserId)));exit;
                }else{
                    $this->success('绑定失败',U('Mobile/Recommend/share',array("inviteUserId"=>$inviteUserId)));exit;
                }
            }else{
                $this->success($info['msg'],U('Mobile/Recommend/share',array("inviteUserId"=>$inviteUserId)));exit;
            }
            exit;
        }

        $inviteUserId = I('inviteUserId');
        if( empty($inviteUserId) ){
            header("Location: ".U('Mobile/Index/index'));
            exit;
        }
        if( $this -> user_id == $inviteUserId){
            header("Location: ".U('Mobile/Recommend/index'));
            exit;
        }

        if(
            !empty($this ->user) &&
            isExistenceDataWithCondition("users",array("user_id"=>$inviteUserId)) &&
            !isExistenceDataWithCondition("invite_list",array( "user_id" =>$this ->user_id)) &&
            !isExistenceDataWithCondition('order',array("user_id" => $this ->user_id,"pay_status" => 1))
        ){
            $inviteData = getGiftInfo( $this -> shopConfig['prize_invite_value'] , $this -> shopConfig['prize_invite'] );
            $beInviteData = getGiftInfo( $this -> shopConfig['prize_invited_to_value'] , $this -> shopConfig['prize_invited_to'] );
            $this -> assign('inviteData',getCallbackData($inviteData));
            $this -> assign('beInviteData',getCallbackData($beInviteData));
            $this -> assign('sms_time_out',tpCache('sms.sms_time_out'));
            $this -> assign('inviteUserId',$inviteUserId);
            $this -> display();
            exit;
        }
        header("Location: ".U('Mobile/Recommend/result' ,array('inviteUserId'=>$inviteUserId)));
        exit;
    }

    public function result(){
        $inviteUserId = I('inviteUserId');
        if( empty($inviteUserId) ){
            header("Location: ".U('Mobile/Index/index'));
            exit;
        }
        $isNewUser = false;
        $inviteData = getGiftInfo( $this -> shopConfig['prize_invite_value'] , $this -> shopConfig['prize_invite'] );
        $beInviteData = getGiftInfo( $this -> shopConfig['prize_invited_to_value'] , $this -> shopConfig['prize_invited_to'] );

        if(
            !empty($this ->user) &&
            $this ->user_id != $inviteUserId &&
            isExistenceDataWithCondition("users",array("user_id"=>$inviteUserId)) &&
            !isExistenceDataWithCondition("invite_list",array( "user_id" =>$this ->user_id)) &&
            !isExistenceDataWithCondition('order',array("user_id" => $this ->user_id,"pay_status" => 1))
        ){
            $addData = array(
                "user_id"           => $this ->user_id,
                "parent_user_id"    => $inviteUserId,
                "create_time"       => time(),
                "update_time"       => time(),
            );
            if(isSuccessToAddData( "invite_list" , $addData )){
                giveBeInviteGift($this ->user_id);
            }
            $inviteUserInfo = findDataWithCondition( "users",array("user_id"=>$inviteUserId) , " nickname" );
            if(  $this -> shopConfig['prize_invited_to'] == 1 ){
                sendWeChatMessageUseUserId( $this ->user_id , "送券" , array("couponId" => $this -> shopConfig['prize_invited_to_value']) );
            }
            if(  $this -> shopConfig['prize_invite'] == 2 ){
                sendWeChatMessageUseUserId( $inviteUserId , "成功邀请" , array("userName" => $this ->user['nickname'],"money" => $this -> shopConfig['prize_invite_value']) );
            }
            $this -> assign('inviteUserInfo',$inviteUserInfo);
            $isNewUser = true;
        }
        $this -> assign('inviteData',getCallbackData($inviteData));
        $this -> assign('beInviteData',getCallbackData($beInviteData));
        $this -> assign('isNewUser',$isNewUser);
        $this -> display();
    }



    public function sendSms(){
        if(empty($this -> user_id)){
            exit( json_encode(callback( false , "用户信息有误" ) ) );
        }
        $mobile = I('send');
        if(!check_mobile($mobile)){
            exit( json_encode(callback( false , "手机号码格式有误" ) ) );
        }
        if( isExistenceDataWithCondition("users",array('mobile'=>$mobile,'user_id'=>array('neq',$this -> user_id))) ){
            exit( json_encode(callback( false , "这个手机号码已经绑定了另外一个龙米账户<br>请换个手机号码" ) ) );
        }
        if( isExistenceDataWithCondition("users",array('mobile'=>$mobile,'user_id'=>$this -> user_id)) ){
            exit( json_encode(callback( false , "系统错误" ) ) );
        }
        $userLogic = new \Common\Logic\UsersLogic();
        $code =  rand(1000,9999);
        $send = $userLogic -> sms_log($mobile,$code,$this->session_id);
        if( $send['status'] != 1 ){
            exit( json_encode(callback( false , $send['msg'] ) ) );
        }
        exit( json_encode(callback( true , "验证码已发送，请注意查收" ) ) );
    }
}