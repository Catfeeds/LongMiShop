<?php
class assistWinningMobileController {

    public $assignData = array();
    public $user = array();

    public $key = null;


    public function __construct($userInfo)
    {
        $this->user = $userInfo;
        $this -> key = md5("42368");
    }

    //初始页面
    public function index(){
        $Uid = I('id');
        $Uid = '5823';
        $user_id = $this->user['user_id'];

        if($Uid != $user_id){
            $helpList = M('addons_assistwinning_help')->where(array("user_id"=>$Uid))->select();
            $List = M('users')->where(array("user_id"=>$Uid))->find();

            foreach($helpList as $key=>$item){
                $List['help'][$key] =  M('users')->where(array("user_id"=>$item['help_uid']))->find();
            }

        }

        return $List;


    }


    public function history(){

    }


}