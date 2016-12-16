<?php
@include 'Addons/riceGrains/Function/base.php';

class riceGrainsMobileController
{

    const TB_ORDER = "addons_christmas_order";
    const TB_ACTIVITY = "addons_christmas_activity";
    const TB_ORDER_GOODS = "addons_christmas_order_goods";
    const TB_ACTIVITY_GOODS = "addons_christmas_activity_goods";

    public $edition = null;
    public $userInfo = null;
    public $assignData = array();
    public $activityInfo = array();



    //初始化
    public function __construct($userInfo)
    {
        $this->assignData["userInfo"] = $this->userInfo = $userInfo;
        $this->assignData["sharePath"] = "./Addons/riceGrains/Template/Mobile/default/Addons_share.html";
        $this->assignData["headerPath"] = "./Addons/riceGrains/Template/Mobile/default/Addons_header.html";
        $this->assignData["footerPath"] = "./Addons/riceGrains/Template/Mobile/default/Addons_footer.html";
        $this->assignData["isFollow"] = $this -> userInfo["is_follow"];
        $this->edition = $this->assignData["activity"]["id"];
        if ( isWeChatBrowser() ) {
            $weChatLogic = new \Common\Logic\WeChatLogic();
            $this->assignData["signPackage"] = $weChatLogic->getSignPackage();
        }
    }

    //圣诞故事
    public function index()
    {
        return $this->assignData;
    }


}