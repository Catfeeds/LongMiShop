<?php

class foreverCouponAdminController {

    public $assignData = array();

    public function __construct()
    {
    }

    //初始页面
    public function index(){
        $this->assignData["list"] = array(
            array(
                "title" => "用户列表",
                "act"   => "userList"
            ),
            array(
                "title" => "基础设置",
                "act"   => "config"
            ),

        );
        return $this -> assignData;
    }

    public function userList(){
        return $this -> assignData;
    }

    public function config(){
        return $this -> assignData;
    }
}