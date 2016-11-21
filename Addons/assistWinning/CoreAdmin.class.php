<?php
class assistWinningAdminController {

    public $assignData = array();

    public function __construct()
    {
    }

    //初始页面
    public function index(){

        $prefix = C('DB_PREFIX');
        $join = $prefix."users ON ".$prefix."addons_assistwinning_prize.user_id = ".$prefix."users.user_id";
        I('mobile') ? $condition[$prefix.'users.mobile'] = I('mobile') : false;
        I('nickname') ? $condition[$prefix.'users.nickname'] = array("like" , "%".I('nickname')."%") : false;
        $userList = M('addons_assistwinning_prize')->join($join)->where($condition)->select();
        $this -> assignData['userList'] = $userList;
        return $this -> assignData;
    }
}