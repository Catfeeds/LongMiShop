<?php
use Think\AjaxPage;


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

        $count = M('addons_assistwinning_prize')->join($join)->where($condition)->count();
        $Page  = new AjaxPage($count,10);
        //  搜索条件下 分页赋值
        foreach($condition as $key=>$val) {
            $Page->parameter[$key]   =   urlencode($val);
        }
        $userList = M('addons_assistwinning_prize')->join($join)->where($condition)->limit($Page->firstRow.','.$Page->listRows)->select();

        $this -> assignData['page'] = $Page -> show();
        $this -> assignData['userList'] = $userList;
        return $this -> assignData;
    }
}