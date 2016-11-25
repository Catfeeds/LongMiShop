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
        $username = I("username");
        $password = I("password");
        $condition = array(
            "username" => $username,
            "password" => $password
        );
        $userInfo = findDataWithCondition("addons_lunchfeast_admin",$condition , "id,is_lock");
        if( empty( $userInfo ) ){
            exit(json_encode(callback(false,"没有此用户")));
        }
        if( !empty( $userInfo['is_lock'] ) && $userInfo['is_lock'] == 1 ){
            exit(json_encode(callback(false,"账号已被锁定，请联系管理员")));
        }
        $token = md5( $userInfo["id"]."_".time());
        $condition2 = array(
            "id" => $userInfo["id"]
        );
        $save = array(
            "token" => $token,
            "last_time" => time()
        );
        saveData( "addons_lunchfeast_admin" , $condition2 , $save );
        exit(json_encode(callback(true,"",$token)));
    }
    //验证核销码
    public function verification(){
        if( !lunchFeastApiUserToken(I("token")) ){
            exit(json_encode(callback(false, "签名错误")));
        }
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
    //使用核销码
    public function useCode(){
        if( !lunchFeastApiUserToken(I("token")) ){
            exit(json_encode(callback(false, "签名错误")));
        }
        $userInfo = findDataWithCondition( "addons_lunchfeast_admin" , array('token' => I("token") ));

        $code = I("code");
        $condition = array(
            "code" => $code,
            "is_use" => 0,
            "use_time" => array("eq",""),
            "admin_id" => array("eq",""),
        );
        $codeInfo =  findDataWithCondition( "addons_lunchfeast_order_user" ,$condition );
        if( empty( $codeInfo ) ){
            exit(json_encode(callback(false, "未找到相应的核销码")));
        }
        $condition2 = array(
            "id" => $codeInfo["order_id"]
        );
        $orderInfo =  findDataWithCondition( "addons_lunchfeast_order" ,$condition2  );
        if( empty( $orderInfo ) ){
            exit(json_encode(callback(false, "未找到相应的订单")));
        }
        if( $userInfo["shop_id"] > 0  && $orderInfo['shop_id'] != $userInfo["shop_id"] ){
            exit(json_encode(callback(false, "你不能核销其他店的核销码")));
        }
        $save = array(
            "use_time" => time(),
            "admin_id" => $userInfo["id"],
            "is_use" => 1
        );
        saveData( "addons_lunchfeast_order_user", $condition ,$save );
        exit(json_encode(callback(true)));
    }
}