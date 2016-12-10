<?php
@include 'Addons/christmas/Function/base.php';
class christmasApiController
{

    public function __construct()
    {
    }


    //主页
    public function index(){
        $this -> notifyUrl();
    }
    //微信异步返回
    public function notifyUrl()
    {
        setLogResult("123","ss","test");
        include  "plugins/payment/weixin/weixin.class.php";
        $code = '\\weixin'; // \alipay
        $payment = new $code();
        $payment -> response();
        exit();
    }
}