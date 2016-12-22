<?php
@include 'Addons/riceGrains/Function/base.php';

class riceGrainsMobileController
{

    public $userInfo = null;
    public $assignData = array();


    //初始化
    public function __construct($userInfo)
    {
        $this->assignData["v"] = 1;

        $this->assignData["userInfo"] = $this->userInfo = $userInfo;
        $this->assignData["sharePath"] = "./Addons/riceGrains/Template/Mobile/default/Addons_share.html";
        $this->assignData["headerPath"] = "./Addons/riceGrains/Template/Mobile/default/Addons_header.html";
        $this->assignData["footerPath"] = "./Addons/riceGrains/Template/Mobile/default/Addons_footer.html";
        $this->assignData["isFollow"] = $this->userInfo["is_follow"];
        if (isWeChatBrowser()) {
            $weChatLogic = new \Common\Logic\WeChatLogic();
            $this->assignData["signPackage"] = $weChatLogic->getSignPackage();
        }
    }

    public function index()
    {
        return $this->assignData;
    }
    public function index2()
    {
        return $this->assignData;
    }

    public function game()
    {
        return $this->assignData;
    }

    public function detail()
    {
        return $this->assignData;
    }

    public function shareInfo()
    {
        return $this->assignData;
    }

    public function getGift()
    {
        return $this->assignData;
    }
//    public function


}