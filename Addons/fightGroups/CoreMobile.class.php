<?php
@include 'Addons/fightGroups/Function/base.php';

class fightGroupsMobileController
{
    const TB_ORDER = "addons_fightgroups_order";

    public $userInfo = null;
    public $assignData = array();

    //初始化
    public function __construct($userInfo)
    {
        $this->assignData["userInfo"] = $this->userInfo = $userInfo;
        $this->assignData["v"]= time();

        $this->assignData["headerPath"]= "./Addons/fightGroups/Template/Mobile/default/Addons_header.html";
        $this->assignData["jsPath"]= "./Addons/fightGroups/Template/Mobile/default/Addons_js.html";

        $weChatLogic = new \Common\Logic\WeChatLogic();
        $this->assignData["signPackage"] = $weChatLogic->getSignPackage();
        $this->assignData["shareData"] = array(
            "title" => "龙米合伙人",
            "desc"  => "龙米合伙人招募",
            "img"   => "http://" . $_SERVER["HTTP_HOST"] . "/Template/index/default/Static/images/sh-02.png",
            "url"   => "http://" . $_SERVER["HTTP_HOST"] . U("Mobile/Addons/partner")
        );
    }

    //首页
    public function index()
    {
        return $this->assignData;
    }


    public function tip()
    {
        return $this->assignData;
    }

    //支付页面
    public function pay()
    {
        if (IS_POST) {
            $start_time = $this->activityInfo["start_time"];
            $end_time = $this->activityInfo["end_time"];
            if (time() < $start_time) {
                exit(json_encode(callback(false, "活动还未开始")));
            }
            if ($end_time < time()) {
                exit(json_encode(callback(false, "活动已经结束")));
            }
            $number = getCountWithCondition(self::TB_ORDER, array("activity_id" => $this->edition, "status" => array("neq", "0")));
            if ($number >= $this->activityInfo['number']) {
                exit(json_encode(callback(false, "礼包名额已满")));
            }
            $notPayOrderInfo = findDataWithCondition(self::TB_ORDER, array("user_id" => $this->userInfo["user_id"], "status" => "0"), "id");
            if (!empty($notPayOrderInfo)) {
                $save = array(
                    "id" => $notPayOrderInfo["id"]
                );
                $data = array(
                    "message" => I("message", "")
                );
                saveData(self::TB_ORDER, $save, $data);
                exit(json_encode(callback(true, "", $notPayOrderInfo["id"])));
            }
            $data = array(
                "user_id"     => $this->userInfo["user_id"],
                "activity_id" => $this->edition,
                "order_sn"    => date('YmdHis') . rand(1000, 9999),
                "status"      => 0,
                "money"       => $this->assignData["activity"]["money"],
                "message"     => I("message", ""),
                "create_time" => time(),
            );
            $orderId = addData(self::TB_ORDER, $data);
            foreach ($this->activityInfo["goods"] as $goodsItem) {
                $goods = array(
                    "activity_id"   => $this->edition,
                    "order_id"      => $orderId,
                    "admin_id"      => $goodsItem['admin_id'],
                    "goods_id"      => $goodsItem['goods_id'],
                    "goods_sn"      => $goodsItem['goods_sn'],
                    "goods_name"    => $goodsItem['goods_name'],
                    "spec_key"      => $goodsItem['spec_key'],
                    "spec_key_name" => $goodsItem['spec_key_name'],
                    "goods_num"     => $goodsItem['goods_num'],
                    "goods_money"   => $goodsItem['goods_money'],
                    "create_time"   => time()
                );

                addData(self::TB_ORDER_GOODS, $goods);
            }
            exit(json_encode(callback(true, "", $orderId)));
        }
        exit(json_encode(callback(false, "ERROR")));
    }

    //支付页面
    public function weChatPay()
    {
        $id = I("order_id");
        if ($_SESSION['openid'] && strstr($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger')) {
            $order = findDataWithCondition(self::TB_ORDER, array("id" => $id, "user_id" => $this->userInfo["user_id"]));
            if (!empty($order)) {
                addonsWeChatPay($id, "christmas");
                exit;
            }
        } else {
            exit;
        }
        exit;
    }

}