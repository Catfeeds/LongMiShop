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
        $code = I("code");
        $condition = array(
            "code" => $code,
            "is_use" => 0,
            "use_time" => array("eq",""),
            "admin_id" => array("eq",""),
        );
        if( isExistenceDataWithCondition( "addons_lunchfeast_order_user" ,$condition ) ){
            exit(json_encode(callback(true)));
        }
        exit(json_encode(callback(false, "未找到相应的核销码")));

    }
    //验证核销码
    public function useCode(){
        exit(json_encode(callback(true)));
    }
}