<?php
@include 'Addons/riceGrains/Function/base.php';

class riceGrainsMobileController
{

    const TB_GIFT = "addons_ricegrains_gift";
    const TB_RECORD = "addons_ricegrains_record";

    public $userInfo = null;
    public $assignData = array();

    public $level_array = array(
        "1" => 200,
        "2" => 120,
        "3" => 30
    );

    //初始化
    public function __construct($userInfo)
    {
        $this->assignData["v"] = "v1.1";
        $this->assignData["userInfo"] = $this->userInfo = $userInfo;
        $this->assignData["sharePath"] = "./Addons/riceGrains/Template/Mobile/default/Addons_share.html";
        $this->assignData["headerPath"] = "./Addons/riceGrains/Template/Mobile/default/Addons_header.html";
        $this->assignData["footerPath"] = "./Addons/riceGrains/Template/Mobile/default/Addons_footer.html";
        $this->assignData["isFollow"] = $this->userInfo["is_follow"];
        $this->assignData["share"] = array(
            "url"   => "http://" . $_SERVER['HTTP_HOST'] . U('Mobile/Addons/riceGrains'),
            "img"   => "http://" . $_SERVER['HTTP_HOST'] . "/Addons/riceGrains/logo.jpg",
            "title" => "粒粒接辛苦",
            "desc"  => "你会比我害还牛吗？",
        );
        if (isWeChatBrowser()) {
            $weChatLogic = new \Common\Logic\WeChatLogic();
            $this->assignData["signPackage"] = $weChatLogic->getSignPackage();
        }
    }

    public function index()
    {
        return $this->assignData;
    }

    public function getGift()
    {
        $id = I("id");
        if (empty($id)) {
            header("Location: " . U('Mobile/Addons/riceGrains'));
            exit;
        }
        $condition = array(
            "user_id" => $this->userInfo["user_id"],
            "openid"  => $this->userInfo["openid"],
            "id"      => $id
        );
        $recordInfo = findDataWithCondition(self::TB_RECORD, $condition);
        if (
            empty($recordInfo)
            || $recordInfo["status"] == 1
        ) {
            header("Location: " . U('Mobile/Addons/riceGrains'));
            exit;
        }

        $gift = findDataWithCondition(self::TB_GIFT);
        if ($recordInfo["fraction"] > $this->level_array["1"]) {
            $couponInfo = findDataWithCondition("coupon", array("id" => $gift["coupon_id1"]));
        } elseif ($recordInfo["fraction"] > $this->level_array["2"]) {
            $couponInfo = findDataWithCondition("coupon", array("id" => $gift["coupon_id2"]));
        } elseif ($recordInfo["fraction"] > $this->level_array["3"]) {
            $couponInfo = findDataWithCondition("coupon", array("id" => $gift["coupon_id3"]));
        } else {
            header("Location: " . U('Mobile/Addons/riceGrains'));
            exit;
        }
        if (empty($couponInfo)) {
            header("Location: " . U('Mobile/Addons/riceGrains'));
            exit;
        }


        addNewCoupon($couponInfo['id'], $this->userInfo["user_id"]);


        $save = array(
            "status" => 1
        );
        saveData(self::TB_RECORD, $condition, $save);

        $this->assignData["coupon"] = $couponInfo;
        $this->assignData["record"] = $recordInfo;

        return $this->assignData;
    }

    public function setAchievement()
    {
        if (IS_POST) {
            $fraction = I("fraction", 0);

            if ($fraction > $this->level_array["1"]) {
                $level = 1;
            } elseif ($fraction > $this->level_array["2"]) {
                $level = 2;
            } elseif ($fraction > $this->level_array["3"]) {
                $level = 3;
            } else {
                exit(json_encode(callback(false, "错误访问")));
            }

            $condition = array(
                "user_id" => $this->userInfo["user_id"],
                "openid"  => $this->userInfo["openid"],
                "level"   => $level
            );
            $recordInfo = findDataWithCondition(self::TB_RECORD, $condition);
            if (empty($recordInfo)) {
                $condition["status"] = 0;
                $condition["fraction"] = $fraction;
                $condition["create_time"] = time();
                $id = addData(self::TB_RECORD, $condition);
            } else {
                if ($recordInfo["status"] == 1) {
                    exit(json_encode(callback(false, "您已经领取过这个等级的奖励了")));
                }
                $condition["status"] = 0;
                $condition["fraction"] = $fraction;
                $condition["create_time"] = time();
                saveData(self::TB_RECORD, array("id" => $recordInfo['id']), $condition);
                $id = $recordInfo['id'];
            }
            exit(json_encode(callback(true, "", U('Mobile/Addons/riceGrains', array("pluginName" => "getGift", "id" => $id)))));
        }
        exit(json_encode(callback(false, "错误访问")));
    }
}