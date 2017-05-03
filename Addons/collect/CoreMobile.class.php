<?php
@include 'Addons/collect/Function/base.php';


class collectMobileController
{

    const TB_ACTIVITY = "addons_collect_activity";
    const TB_HELP_LIST = "addons_collect_help_list";

    public $assignData = array();
    public $user = array();

    public $config;
    public $edition;

    public function __construct($userInfo)
    {
        $this->assignData["v"] = "v1.2";
        $this->user = $userInfo;
        $this->config = collectGetConfig();
        $this->edition = $this->config["edition"];
        $this->assignData["configs"] = collectGetConfig();
        $this->assignData["__theme"] = $this->config["data"][$this->edition]['theme'];
        $this->assignData["__temp"] = "/Addons/collect/Template/Mobile/" . $this->assignData["__theme"] . "/Static";

    }

    //初始页面
    public function index()
    {
        //分享
        $this->assignData["config"] = array(
            "share_title" => "有饭青年，请把龙米带回家",
            "share_desc" => "江湖救急！动动手指来帮我抢龙米吧，我的奖品分你一口～",
            "share_img" => "http://" . $_SERVER["HTTP_HOST"] . "/Addons/collect/logo.jpg",
            "share_url" => "http://" . $_SERVER["HTTP_HOST"] . U('Mobile/Addons/collect')
        );
        $weChatLogic = new \Common\Logic\WeChatLogic();
        $this->assignData["signPackage"] = $weChatLogic->getSignPackage();

        $data = collectGetData($this->user["user_id"], $this->edition, I("activityId", null));
        $this->assignData["id"] = $data["id"];
        $this->assignData["status"] = $data["status"];
        $this->assignData["numbers"] = $data['number'];
        $this->assignData["surplus"] = $data["surplus"];
        $this->assignData["getList"] = $data['getList'];
        $this->assignData["helpList"] = $data['helpList'];

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
        $res = collectCreateActivity($this->user["user_id"], $this->edition);
        exit(json_encode($res));
    }

    //ajax 助力动作
    public function help()
    {
        $activityId = I("activityId", null);
        if (is_null($activityId)) {
            exit(json_encode(callback(false, "参数错误")));
        }
        $res = collectHelpAction($activityId, $this->user["user_id"], $this->edition);
        exit(json_encode($res));
    }


    //领奖
    public function setData()
    {
        $res = collectSetData($this->user["user_id"], $this->edition, $_GET);
        exit(json_encode($res));
    }


}