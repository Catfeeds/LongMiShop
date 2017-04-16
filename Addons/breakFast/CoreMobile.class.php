<?php
@include 'Addons/breakFast/Function/base.php';

class breakFastMobileController
{

    const TB_CONFIG = "addons_breakfast_config";
    const TB_DATA = "addons_breakfast_data";

    public $userInfo = null;
    public $assignData = array();


    //初始化
    public function __construct($userInfo)
    {
        $this->assignData["userInfo"] = $this->userInfo = $userInfo;

        $this->assignData["headerPath"] = "./Addons/breakFast/Template/Mobile/default/Addons_header.html";
        $this->assignData["sharePath"] = "./Addons/breakFast/Template/Mobile/default/Addons_wxShare.html";

        $this->assignData["config"] = $config= break_fast_get_config();

        $weChatLogic = new \Common\Logic\WeChatLogic();
        $this->assignData["signPackage"] = $weChatLogic->getSignPackage();
        $this->assignData["shareData"] = array(
            "title" => empty($config['share_title'])?"龙米早餐打卡计划":$config['share_title'],
            "desc" => empty($config['share_desc'])?"一起来打卡吧！":$config['share_desc'],
            "img" => empty($config['share_img'])?"http://" . $_SERVER["HTTP_HOST"] . "/Template/index/default/Static/images/sh-02.png":$config['share_img'],
            "url" => "http://" . $_SERVER["HTTP_HOST"] . U("Mobile/Addons/breakFast")
        );
    }

    //首页
    public function index()
    {
        return $this->assignData;
    }


    //提交结果
    public function over()
    {
        $status = break_fast_get_status();
        if ($status == 1) {
            $date = break_fast_get_this_day();
            $condition = array("date" => $date, "user_id" => $this->userInfo['user_id']);
            if (getCountWithCondition(self::TB_DATA, $condition) > 0) {
                $newStatus = "2";
            } else {
                if (break_fast_is_hour()) {
                    $condition['create_time'] = time();
                    addData(self::TB_DATA, $condition);
                    if(accountLog($this->userInfo['user_id'],1,0,"早餐打卡")){
                        $text = "打卡成功！1元余额已到达你的账户【<a href='http://" . $_SERVER["HTTP_HOST"] .U('Mobile/User/account')."'>点击查看</a>】";
                        $jsSdkLogic = new \Common\Logic\JsSdkLogic();
                        $jsSdkLogic->push_msg($this->userInfo['openid'], $text);
                    }
                    $newStatus = "1";
                } else {
                    $newStatus = "-3";
                }
            }
        } else if ($status == 2) {
            $newStatus = "-1";
        } else {
            $newStatus = "-2";
        }
        $textArray = array(
            "-3" => "噢，本日打卡时间已过，请明天8:00~10:00再来哦",
            "-2" => "活动已经结束",
            "-1" => "活动还没开始",
            "1" => "打卡成功！1元余额已到达你的账户",
            "2" => "本日已经打卡成功了，请明天再来",
        );
        $this->assignData["status"] = $newStatus;
        $this->assignData["textArray"] = $textArray;

        return $this->assignData;
    }

}