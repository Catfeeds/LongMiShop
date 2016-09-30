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
        $this -> display();
    }

    public function recommendList(){
        $list = getInviteList($this ->user);
        $this -> assign('list',$list);
        $this -> display();
    }

    public function share(){
        $inviteUserId = I('inviteUserId');
        if(
            !empty($this ->user) &&
            isExistenceDataWithCondition("user",array("user_id"=>$inviteUserId)) &&
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
        }
        $this -> display();
    }

}