<?php

@include 'Addons/dragonKitchen/Function/base.php';

class dragonKitchenApiController
{

    public function __construct()
    {
        $signArray = array("sign_time" => I("sign_time"), "sign_str" => I("sign_str"));
        if (!signVerification($signArray)) {
            printJson(false, "验证失败!");
        }
    }


    public function index()
    {
    }


    public function getList(){

    }


}