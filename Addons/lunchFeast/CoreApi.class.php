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
        $token = I("token");
        $code = I("code");
        lunchFeastApiVerificationCode( $code , $token );
        exit(json_encode(callback(true)));
    }
    //获取信息
    public function getCodeInfo(){
        $token = I("token");
        $code = I("code");
        $date = lunchFeastApiVerificationCode( $code , $token );
        $shopInfo = findDataWithCondition( "addons_lunchfeast_shop" , array("id" => $date['orderInfo']["shop_id"]) );
        if( empty( $shopInfo ) ){
            exit(json_encode(callback(false, "未找到相应的店铺")));
        }
        $diningperInfo = findDataWithCondition( "addons_lunchfeast_diningper" , array("id" => $date['codeInfo']["diningper_id"]) );
        if( empty( $diningperInfo ) ){
            exit(json_encode(callback(false, "未找到相应的用餐人")));
        }
        $mealList = selectMealList();
        $returnData = array(
            "code" => $code,
            "shopName" => $shopInfo['shop_name'],
            "dateTime" => date("Y-m-d",$date['orderInfo']['date']),
            "mealTime" => $mealList[$date['orderInfo']['meal_id']],
            "userData" => $diningperInfo['names'] . ($diningperInfo['mobile']?$diningperInfo['mobile']:""),
        );
        exit(json_encode(callback(true, "" , $returnData)));
    }
    //使用核销码
    public function useCode(){
        $token = I("token");
        $code = I("code");
        $date = lunchFeastApiVerificationCode( $code , $token );
        if( $date['userInfo']["shop_id"] > 0  && $date['orderInfo']['shop_id'] != $date['userInfo']["shop_id"] ){
            exit(json_encode(callback(false, "你不能核销其他店的核销码")));
        }
        $save = array(
            "use_time" => time(),
            "admin_id" => $date['userInfo']["id"],
            "is_use" => 1
        );
        saveData( "addons_lunchfeast_order_user", array("id"=>$date['codeInfo']["id"]) ,$save );
        $useNumber = getCountWithCondition( "addons_lunchfeast_order_user" , array("is_use"=>1,"order_id" =>$date['orderInfo']["id"] ));
        if( $useNumber == $date['orderInfo']["number"]){
            saveData( "addons_lunchfeast_order", array("id" => $date['orderInfo']["id"] ) ,array("status"=>2) );
        }
        exit(json_encode(callback(true)));
    }
}