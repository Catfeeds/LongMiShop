<?php
@include 'Addons/christmas/Function/base.php';
class christmasMobileController {

    const TB_ORDER = "addons_christmas_order";
    const TB_ACTIVITY = "addons_christmas_activity";
    const TB_ACTIVITY_GOODS = "addons_christmas_activity_goods";

    public $assignData = array();


    //初始化
    public function __construct()
    {
        $this -> assignData["share"] = "./Addons/christmas/Template/Mobile/default/Addons_share.html";
        $this -> assignData["headerPath"] = "./Addons/christmas/Template/Mobile/default/Addons_header.html";
        $this -> assignData["footerPath"] = "./Addons/christmas/Template/Mobile/default/Addons_footer.html";
        $this -> assignData["activity"] = getActivityInfo();
        $this -> assignData["share"] = getShareArray($this -> assignData["activity"],I("order_id",0));
        $weChatLogic= new \Common\Logic\WeChatLogic();
        $this -> assignData["signPackage"] = $weChatLogic -> getSignPackage();
    }

    //圣诞故事
    public function index(){
        return $this -> assignData;
    }

    //礼包内容
    public function rule(){
        return $this -> assignData;
    }

    //支付页面
    public function weChatPay()
    {
        $id = I("order_id");
        if( $_SESSION['openid'] && strstr($_SERVER['HTTP_USER_AGENT'],'MicroMessenger')){
            $order = findDataWithCondition( self::TB_ORDER , array("id"=>$id));
            if( !empty( $order ) ){
                addonsWeChatPay( $id , "christmas" );
                exit;
            }
        }else{
            exit;
        }
        exit;
    }

    //结果页
    public function results()
    {
        return $this->assignData;
    }

    //订单页面
    public function order()
    {
        return $this->assignData;
    }

    //订单详情页面
    public function orderDetail()
    {
        return $this->assignData;
    }

    //获取礼包
    public function shareInfo(){
        return $this -> assignData;
    }

    //领取礼包
    public function get(){
        return $this -> assignData;
    }

    //领取成功页面
    public function getResults(){
        return $this -> assignData;
    }

}