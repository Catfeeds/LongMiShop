<?php
@include 'Addons/agriculturalBank/Function/base.php';

class agriculturalBankMobileController
{

    const TB_LIST = "addons_agriculturalbank_list";
    const TB_USER = "addons_agriculturalbank_user";

    public $userInfo = null;
    public $assignData = array();

    //初始化
    public function __construct($userInfo)
    {
        $this->assignData["userInfo"] = $this->userInfo = $userInfo;

        $weChatLogic = new \Common\Logic\WeChatLogic();
        $this->assignData["signPackage"] = $weChatLogic->getSignPackage();
        $this->assignData["shareData"] = array(
            "title" => "龙米",
            "desc"  => "龙米",
            "img"   => "http://" . $_SERVER["HTTP_HOST"] . "/Template/index/default/Static/images/sh-02.png",
            "url"   => "http://" . $_SERVER["HTTP_HOST"] . U("Mobile/Index/index")
        );

    }

    //首页
    public function index()
    {
        if( !isExistenceDataWithCondition(self::TB_USER,array("user_id" =>  $this->userInfo["user_id"]))){
            $come = I('come',null);
            if( !is_null($come) && $come == "weChat"){
                header("Location: ".U("Mobile/Addons/agriculturalBank",array("pluginName"=>"tip")) );exit;
            }else{
                addData(self::TB_USER,array("user_id" =>  $this->userInfo["user_id"]));
            }
        }

        //关注情况
        $this->assignData["isFollow"] = $this->userInfo["is_follow"];
        if ($_SERVER["HTTP_HOST"] == "www.longmiwang.com") {
            $this->assignData["qrcode"] = "qrcode.jpg";
        } else {
            $this->assignData["qrcode"] = "qecode2.jpg";
        }
        return $this->assignData;
    }


    //ajax保存部分
    public function save()
    {
        if (IS_POST) {
            $p_name = I("p_name", null);
            $p_phone = I("p_phone", null);
//            $p_branch = I("p_branch", null);
            $user_id = $this->assignData["userInfo"]["user_id"];

            is_null($p_name) ? exit(json_encode(callback(false, "姓名不能为空"))) : false;
            is_null($p_phone) ? exit(json_encode(callback(false, "手机号不能为空"))) : false;
            !check_mobile($p_phone) ? exit(json_encode(callback(false, "手机号格式有误"))) : false;
//            is_null($p_branch) ? exit(json_encode(callback(false, "支行信息不能为空"))) : false;

            isExistenceDataWithCondition(self::TB_LIST, array("user_id" => $user_id)) ? exit(json_encode(callback(false, "已经领取过"))) : false;

            $addData = array(
                "p_name" => $p_name,
                "p_phone" => $p_phone,
//                "p_branch"    => $p_branch,
                "user_id" => $user_id,
                "create_time" => time(),
                "status" => "0"
            );
            if (addData(self::TB_LIST, $addData)) {
                if ($_SERVER["HTTP_HOST"] == "www.longmiwang.com") {
                    $inviteUserId = 32516;
                    $sendNewsCouponsId = 25;
                } else {
                    $inviteUserId = 5962;
                    $sendNewsCouponsId = 15;
                }

                createInviteRelationship($user_id, $inviteUserId, $this->assignData["userInfo"]['nickname'], getShopConfig());
                addNewCoupon($sendNewsCouponsId, $user_id);
                exit(json_encode(callback(true, "申请成功")));
            }
            exit(json_encode(callback(false, "领取失败")));
        }
        exit(json_encode(callback(false, "非法访问")));
    }

    //提交结果
    public function submitMsg()
    {
        return $this->assignData;
    }
    //提示页面
    public function tip()
    {
        return $this->assignData;
    }

}