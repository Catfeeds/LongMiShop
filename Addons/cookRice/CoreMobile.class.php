<?php
@include 'Addons/cookRice/Function/base.php';

class cookRiceMobileController
{

    const TB_ACTIVITY = "addons_cookrice_activity";
    const TB_HELP_LIST = "addons_cookrice_help_list";

    public $assignData = array();
    public $user = array();

    public $config;
    public $edition;

    public function __construct($userInfo)
    {
        $this->assignData["v"] = "v1.8";
        $this->user = $userInfo;
        $this->config = cookRiceGetConfig();
        $this->edition = $this->config["edition"];
        $this->assignData["__theme"] = $this->config["data"][$this->edition]['theme'];

    }

    //初始页面
    public function index()
    {

        $data = cookRiceGetData($this->user["user_id"], $this->edition, I("activityId", null));

        $this->assignData["id"] = $data["id"];
        $this->assignData["status"] = $data["status"];
        $this->assignData["number"] = $data['number'];
        $this->assignData["surplusNumber"] = 100 - $data['number'];
        $this->assignData["currentNumber"] = ( $data['number'] * 1.5 ) + 3;
        $this->assignData["getList"] = $data['getList'];
        $this->assignData["helpList"] = $data['helpList'];

        $this->assignData["config"] = array(
            "share_title" => "土豪龙米又发福利啦，千元电饭煲免费送！",
            "share_desc"  => "亲爱哒，快来帮我抢千元电饭煲～",
            "share_img"   => "http://" . $_SERVER["HTTP_HOST"] . "/Addons/cookRice/Static/images/share.jpg",
            "share_url"   => "http://" . $_SERVER["HTTP_HOST"] . U('Mobile/Addons/cookRice')
        );

        $weChatLogic = new \Common\Logic\WeChatLogic();
        $this->assignData["signPackage"] = $weChatLogic->getSignPackage();

        if ($_SERVER["HTTP_HOST"] == "www.longmiwang.com") {
            $this->assignData["qrcode"] = "qrcode.jpg";
        } else {
            $this->assignData["qrcode"] = "qecode2.jpg";
        }
        //关注情况
        $this->assignData["isFollow"] = $this->user["is_follow"];

        return $this->assignData;
    }

    //ajax 创建活动
    public function createActivity()
    {
        $res = cookRiceCreateActivity($this->user["user_id"], $this->edition);
        exit(json_encode($res));
    }

    //ajax 助力动作
    public function help()
    {
        $activityId = I("activityId", null);
        if( is_null($activityId)){
            exit(json_encode(callback(false,"参数错误")));
        }
        $res = cookRiceHelpAction($activityId, $this->user["user_id"], $this->edition);
        exit(json_encode($res));
    }


    //领奖
    public function setData(){
        $res = cookRiceSetData($this->user["user_id"], $this->edition,$_GET);
        exit(json_encode($res));
    }


}