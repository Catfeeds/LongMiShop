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
        $this->assignData["v"] = time();
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
        $this->assignData["tip"] = $data["tip"];
        $this->assignData["status"] = $data["state"];


        $this->assignData["config"] = array(
            "share_title" => "煮饭小游戏！",
            "share_desc"  => "助力我！",
            "share_img"   => "http://" . $_SERVER["HTTP_HOST"] . "/Addons/cookRice/logo.jpg",
            "share_url"   => "http://" . $_SERVER["HTTP_HOST"] . U('Mobile/Addons/cookRice')
        );

        $weChatLogic = new \Common\Logic\WeChatLogic();
        $this->assignData["signPackage"] = $weChatLogic->getSignPackage();

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

}