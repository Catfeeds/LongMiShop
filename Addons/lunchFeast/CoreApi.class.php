<?php
@include 'Addons/lunchFeast/Function/base.php';
class lunchFeastApiController
{

    public function __construct()
    {
    }
    //主页
    public function index()
    {
        include_once  "plugins/payment/weixin/weixin.class.php";
        $code = '\\weixin'; // \alipay
        $payment = new $code();
        $payment -> response();
        exit();
    }
}