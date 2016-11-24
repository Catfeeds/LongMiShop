<?php
@include 'Addons/lunchFeast/Function/base.php';
class lunchFeastApiController
{

    public function __construct()
    {
    }
    //主页
    public function notifyUrl()
    {
        setLogResult( "11" , "支付1" , "test");
        include  "plugins/payment/weixin/weixin.class.php";
        $code = '\\weixin'; // \alipay
        $payment = new $code();
        setLogResult( $payment , "支付22" , "test");
        $payment -> response();
        exit();
    }
}