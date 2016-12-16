<?php

class riceGrainsAdminController {

    public $assignData = array();

    public function __construct()
    {
    }
    //初始页面
    public function index(){
        $this->assignData["list"] = array(
            array(
                "title" => "基础设置",
                "act"   => "activitySet"
            ),
            array(
                "title" => "订单列表",
                "act"   => "orderList"
            )
        );
        return $this->assignData;
    }
    //基础设置
    public function activitySet(){
        return $this->assignData;
    }
    //订单列表
    public function orderList(){
        return $this->assignData;
    }
    //订单详情
    public function orderDetail(){
        return $this->assignData;
    }
}