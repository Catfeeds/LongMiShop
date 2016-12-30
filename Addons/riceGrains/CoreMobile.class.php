<?php
@include 'Addons/riceGrains/Function/base.php';

class riceGrainsMobileController
{

    const TB_GIFT   = "addons_ricegrains_gift";
    const TB_RECORD   = "addons_ricegrains_record";

    public $userInfo = null;
    public $assignData = array();


    //初始化
    public function __construct($userInfo)
    {
        $this->assignData["v"] = time();

        $this->assignData["userInfo"] = $this->userInfo = $userInfo;
        $this->assignData["sharePath"] = "./Addons/riceGrains/Template/Mobile/default/Addons_share.html";
        $this->assignData["headerPath"] = "./Addons/riceGrains/Template/Mobile/default/Addons_header.html";
        $this->assignData["footerPath"] = "./Addons/riceGrains/Template/Mobile/default/Addons_footer.html";
        $this->assignData["isFollow"] = $this->userInfo["is_follow"];
        $this->assignData["share"] = array(
            "url" => "http://".$_SERVER['HTTP_HOST']. U('Mobile/Addons/riceGrains') ,
            "img" => "http://".$_SERVER['HTTP_HOST']."/Addons/riceGrains/logo.jpg",
            "title" => "粒粒接辛苦",
            "desc" => "粒粒接辛苦",
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
        $condition = array(
            "user_id" => $this->userInfo["user_id"],
            "openid"  => $this->userInfo["openid"],
        );
        $recordInfo = findDataWithCondition(self::TB_RECORD, $condition);
        if (
            empty($recordInfo)
            || $recordInfo["status"] == 1
        ) {
            header("Location: ". U('Mobile/Addons/riceGrains') );
            exit;
        }

        $gift = findDataWithCondition(self::TB_GIFT);
        if( $recordInfo["fraction"] > 300 ){
            $couponInfo = findDataWithCondition("coupon", array("coupon_id" => $gift["coupon_id1"]));
        }elseif( $recordInfo["fraction"] > 160 ){
            $couponInfo = findDataWithCondition("coupon", array("coupon_id" => $gift["coupon_id2"]));
        }elseif( $recordInfo["fraction"] > 80 ){
            $couponInfo = findDataWithCondition("coupon", array("coupon_id" => $gift["coupon_id3"]));
        }else{
            header("Location: ". U('Mobile/Addons/riceGrains') );
            exit;
        }
        if( empty( $couponInfo )){
            header("Location: ". U('Mobile/Addons/riceGrains') );
            exit;
        }


        addNewCoupon( $couponInfo['id'] , $this->userInfo["user_id"] );


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
            $fraction = I("fraction");
            $condition = array(
                "user_id"  => $this->userInfo["user_id"],
                "openid"   => $this->userInfo["openid"],
//                "fraction" => $fraction,
//                "status"   => "0"
            );
            $recordInfo = findDataWithCondition( self::TB_RECORD , $condition );
            if( empty($recordInfo) ){
                $condition["status"] = 0;
                $condition["fraction"] = $fraction;
                $condition["create_time"] = time();
                addData( self::TB_RECORD , $condition );
            }else{
                if( $recordInfo["status"] == 1 ){
                    exit(json_encode(callback(false , "您已经领取过奖励了")));
                }
//                if( $fraction > $recordInfo['fraction']){
                    $condition["status"] = 0;
                    $condition["fraction"] = $fraction;
                    $condition["create_time"] = time();
                    saveData( self::TB_RECORD , array("id"=>$recordInfo['id']) , $condition );
//                }
            }
            exit(json_encode(callback(true,"",U('Mobile/Addons/riceGrains',array("pluginName"=>"getGift")))));
        }
        exit(json_encode(callback(false,"错误访问")));
    }
}