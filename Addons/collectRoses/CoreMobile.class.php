<?php
@include 'Addons/collectRoses/Function/base.php';

class collectRosesMobileController
{

    const TB_ACTIVITY = "addons_collectroses_activity";
    const TB_HELP_LIST = "addons_collectroses_help_list";

    public $assignData = array();
    public $user = array();

    public $config;
    public $edition;

    public function __construct($userInfo)
    {
        $this->assignData["v"] = time();
        $this->user = $userInfo;
        $this->config = collectRosesGetConfig();
        $this->edition = $this->config["edition"];
        $this->assignData["configs"] = collectRosesGetConfig();
        $this->assignData["__theme"] = $this->config["data"][$this->edition]['theme'];

    }

    //初始页面
    public function index()
    {
        $data = collectRosesGetData($this->user["user_id"], $this->edition, I("activityId", null));

        $this->assignData["id"] = $data["id"];
        $this->assignData["status"] = $data["status"];
        $this->assignData["numbers"] = $data['number'];
        $this->assignData["getList"] = $data['getList'];
        $this->assignData["helpList"] = $data['helpList'];

        $this->assignData["config"] = array(
            "share_title" => "集齐9朵爱情玫瑰，即可获赠龙米独家“有钱花”",
            "share_desc"  => "情人节，龙米送999元现金,不管单身汪还是情侣喵，集齐9朵就送钱",
            "share_img"   => "http://" . $_SERVER["HTTP_HOST"] . "/Addons/collectRoses/Static/images/share.jpg",
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
        $res = collectRosesCreateActivity($this->user["user_id"], $this->edition);
        exit(json_encode($res));
    }

    //ajax 助力动作
    public function help()
    {
        $activityId = I("activityId", null);
        if( is_null($activityId)){
            exit(json_encode(callback(false,"参数错误")));
        }
        $res = collectRosesHelpAction($activityId, $this->user["user_id"], $this->edition);
        exit(json_encode($res));
    }


    //领奖
    public function setData(){
        $res = collectRosesSetData($this->user["user_id"], $this->edition,$_GET);
        exit(json_encode($res));
    }


}