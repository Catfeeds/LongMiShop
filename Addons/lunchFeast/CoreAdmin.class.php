<?php

class lunchFeastAdminController {

    public $assignData = array();

    public function __construct()
    {
    }

    //初始页面
    public function index(){
        $this -> assignData["list"] = array(
            array(
                "title" => "店铺列表",
                "act"   => "shopList"
            ),
            array(
                "title" => "订单列表",
                "act"   => "orderList"
            )
        );
        return $this -> assignData;
    }


    public function shopList(){
        return $this -> assignData;
    }
    public function shopDetail(){
        $shopId = I( "id" , 0 );
        if( IS_POST ){

        }
        exit;
        return $this -> assignData;
    }
    public function orderList(){
        return $this -> assignData;
    }
    public function orderDetail(){
        return $this -> assignData;
    }
}