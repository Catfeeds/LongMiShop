<?php
@include 'Addons/partner/Function/base.php';

class partnerMobileController
{

    const TB_LIST = "addons_partner_list";

    public $userInfo = null;
    public $assignData = array();

    //初始化
    public function __construct($userInfo)
    {
        $this->assignData["userInfo"] = $this->userInfo = $userInfo;
        $this->assignData["sharePath"] = "./Addons/partner/Template/Mobile/default/Addons_wxShare.html";
        if (isWeChatBrowser()) {
            $weChatLogic = new \Common\Logic\WeChatLogic();
            $this->assignData["signPackage"] = $weChatLogic->getSignPackage();
            $this->assignData["shareData"] = array(
                "title" => "龙米合伙人",
                "desc"  => "龙米合伙人招募",
                "img"   => "http://" . $_SERVER["HTTP_HOST"] . "/Template/index/default/Static/images/sh-02.png",
                "url"   => "http://" . $_SERVER["HTTP_HOST"] . U("Mobile/Addons/partner")
            );
        }
    }

    //首页
    public function index()
    {
        return $this->assignData;
    }


    //ajax保存部分
    public function save()
    {
        if (IS_POST) {
            $p_name     = I("p_name"    , null);
            $p_city     = I("p_city"    , null);
            $p_sex      = I("p_sex"     , null);
            $p_phone    = I("p_phone"   , null);
            $p_wechat   = I("p_wechat"  , null);
            $p_email    = I("p_email"   , null);
            $p_desc     = I("p_desc"    , null);
            $user_id    = $this->assignData["userInfo"]["user_id"];

            is_null($p_name)        ? exit(json_encode(callback(false, "合伙人姓名不能为空")))       : false;
            is_null($p_city)        ? exit(json_encode(callback(false, "合伙人城市不能为空")))       : false;
            is_null($p_sex)         ? exit(json_encode(callback(false, "合伙人性别不能为空")))       : false;
            is_null($p_phone)       ? exit(json_encode(callback(false, "合伙人手机号不能为空")))      : false;
            !check_mobile($p_phone) ? exit(json_encode(callback(false, "合伙人手机号格式有误")))      : false;
            is_null($p_wechat)      ? exit(json_encode(callback(false, "合伙人微信号不能为空")))      : false;
            is_null($p_email)       ? exit(json_encode(callback(false, "合伙人邮箱不能为空")))       : false;
            !check_email($p_email)  ? exit(json_encode(callback(false, "合伙人邮箱格式有误")))       : false;
            is_null($p_desc)        ? exit(json_encode(callback(false, "合伙人个人优势不能为空")))    : false;

            getCountWithCondition(self::TB_LIST,array("user_id"=>$user_id)) >= 4  ?  exit(json_encode(callback(false, "合伙人申请数量达到上限")))    : false;

            $addData = array(
                "p_name"      => $p_name,
                "p_city"      => $p_city,
                "p_sex"       => $p_sex,
                "p_phone"     => $p_phone,
                "p_wechat"    => $p_wechat,
                "p_email"     => $p_email,
                "p_desc"      => $p_desc,
                "user_id"     => $user_id,
                "create_time" => time(),
                "stuatus"     => "0"
            );
            if (addData(self::TB_LIST, $addData)) {
                exit(json_encode(callback(true, "申请成功")));
            }
            exit(json_encode(callback(false, "申请失败")));
        }
        exit(json_encode(callback(false, "非法访问")));
    }

    //提交结果
    public function submitMsg()
    {
        return $this->assignData;
    }

}