<?php
@include 'Addons/lunchFeast/Function/base.php';
class lunchFeastApiController
{

    public function __construct()
    {
    }


    //主页
    public function index(){
        $this -> notifyUrl();
    }
    //主页
    public function notifyUrl()
    {
        include  "plugins/payment/weixin/weixin.class.php";
        $code = '\\weixin'; // \alipay
        $payment = new $code();
        $payment -> response();
        exit();
    }
    //登录api
    public function login(){
        exit(json_encode(callback(true)));
    }
    //验证核销码
    public function verification(){
        exit(json_encode(callback(true)));
    }
    //验证核销码
    public function useCode(){
        exit(json_encode(callback(true)));
    }
}