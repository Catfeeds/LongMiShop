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

    public function share(){
        $inviteUserId = I('inviteUserId');
        $isNewUser = false;
        if( $this ->user_id == $inviteUserId ){
            header("Location: ".U('Mobile/User/index'));
            exit;
        }
        if(
            !empty($this ->user) &&
            isExistenceDataWithCondition("users",array("user_id"=>$inviteUserId)) &&
            !isExistenceDataWithCondition("invite_list",array( "user_id" =>$this ->user_id))
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
            $this -> assign('inviteUserInfo',findDataWithCondition( "users",array("user_id"=>$inviteUserId) , " nickname" ));
            $isNewUser = true;
        }
        $inviteData = getGiftInfo( $this -> shopConfig['prize_invite_value'] , $this -> shopConfig['prize_invite'] );
        $beInviteData = getGiftInfo( $this -> shopConfig['prize_invited_to_value'] , $this -> shopConfig['prize_invited_to'] );
        $this -> assign('inviteData',getCallbackData($inviteData));
        $this -> assign('beInviteData',getCallbackData($beInviteData));
        $this -> assign('isNewUser',$isNewUser);
        $this -> display();
    }

}