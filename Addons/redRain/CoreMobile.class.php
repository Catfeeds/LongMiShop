<?php
@include 'Addons/redRain/Function/base.php';
class redRainMobileController
{


    public $assignData = array();
    public $userInfo = array();
    public $userId = null;
    public $config = array();

    public $redConfig = array();


    public function __construct($userInfo)
    {
        $this->userInfo = $userInfo;
        $this->userId = $this->userInfo["user_id"];

        $this->assignData["v"] = time();

        $this->redConfig = redRainGetRedConfig();


        $this->assignData["sharePath"]= "./Addons/redRain/Template/Mobile/default/Addons_share.html";
        $this->assignData["headerPath"]= "./Addons/redRain/Template/Mobile/default/Addons_header.html";

        $this->assignData["config"] = array(
            "share_title" => $this->userInfo["nickname"]."叫你一起来抢大红包啦！",
            "share_desc"  => "助力我，一起抢1888大红包！",
            "share_img"   => "http://" . $_SERVER["HTTP_HOST"] . "/Addons/redRain/logo.jpg",
            "share_url"   => "http://" . $_SERVER["HTTP_HOST"] . U('Mobile/Addons/redRain', array('inviteUserId' => $this->userId))
        );

        $this->weChatLogic = new \Common\Logic\WeChatLogic();
        $this->assignData["signPackage"] = $this->weChatLogic->getSignPackage();


        $this->assignData["redConfig"] =  $this->redConfig;
    }

    /**
     * 首页
     * @return array
     */
    public function index()
    {
        //邀请关系
        $inviteUserId = I("inviteUserId", null);
        !is_null($inviteUserId) ? redRainSetInvite($this->userId, $inviteUserId) : false;

        $inviteList = redRainGetMyInviteList($this->userId);
        $this->assignData["inviteList"] = $inviteList;

        //状态
        $currentState = 0;

        $startTime = null;

        $tipMsg = "";
        //获取当前状态数组
        $stateArray = redRainGetCurrentState($this->redConfig, $this->userId);
        switch ($stateArray["state"]) {
            case 1://抢购中
                $currentState = 1;
                $tipMsg = "<b>年年有米，红包多多</b><br>不抢红包非好汉！<br>抢到红包旺一年！";
                break;
            case 2://第一波还没开始
                $tipMsg = "<b>客官您来早啦</b><br>红包雨开始时间<br>" . date( "Y-m-d H:i:s" , $stateArray["data"]["startTime"] );
                $startTime = $stateArray["data"]["startTime"];
                break;
            case 3://下一波还没开始
                $tipMsg = "<b>啊哦，您手慢了，".$stateArray["data"]["lastTitle"]."已经结束</b><br>下一波时间<br>" . date( "Y-m-d H:i:s" , $stateArray["data"]["startTime"] );
                $startTime = $stateArray["data"]["startTime"];
                break;
            case 4://全部结束
                $tipMsg = "<b>本次红包雨活动已经结束</b><br>关注公众号<br>更多活动等你来玩";
                break;
            case 5://领取过
                $tipMsg = "<b>不要贪心哦</b><br>您已经领取过啦";
                break;
            case 6://抢完了
                $tipMsg = "<b>嘤嘤嘤，手太慢了</b><br>红包抢光啦，明天再来吧";
                break;
            default:
                break;
        }




        //关注情况
        $this->assignData["isFollow"] = $this->userInfo["is_follow"];
        $currentState = !$this->userInfo["is_follow"] ? 0 : $currentState;

        $this->assignData["tipMsg"] = $tipMsg;
        $this->assignData["currentState"] = $currentState;

        is_null($startTime)?false: $this->assignData["stateTimeArray"] = array(
            "thisTime"=>time(),
            "startTime"=>$startTime,
            "tipMsg" => "<b>年年有米，红包多多</b><br>不抢红包非好汉！<br>抢到红包旺一年！"
        );



        return $this->assignData;
    }

    /**
     * 获取红包接口
     */
    public function getRed()
    {

        //获取当前状态数组
        $stateArray = redRainGetCurrentState($this->redConfig, $this->userId);
        switch ($stateArray["state"]) {
            case 1://抢购中
                if( redRainAwardQualificationTesting( $this->userId ,$stateArray["data"]["version"] ) ){
                    $money = $stateArray["data"]["minMoney"];
                    addData(
                        "addons_redrain_winning",
                        array(
                            "user_id"     => $this->userId,
                            "version"     => $stateArray["data"]["version"],
                            "money"       => $money,
                            "state"       => 0,
                            "create_time" => time()
                        )
                    );
                    redRainSendRed( $this->userInfo , $money , $stateArray["data"]["version"] );
                    exit(json_encode(callback(true, "恭喜")));
                }else{
                    exit(json_encode(callback(false, "手快有手慢无！红包已被抢完")));
                }

                break;
            case 2://第一波还没开始
                exit(json_encode(callback(false, "活动还没开始")));
                break;
            case 3://下一波还没开始
            case 4://全部结束
                exit(json_encode(callback(false, "活动已经结束")));
                break;
            case 5://领取过
                exit(json_encode(callback(false, "您已经领取过红包")));
                break;
            case 6://领取过
                exit(json_encode(callback(false, "手快有手慢无！红包已被抢完")));
                break;
        }

        exit(json_encode(callback(false, "接口")));

    }




    public function lists(){
        $this->assignData["lists"] = selectDataWithCondition("addons_redrain_winning",array("user_id"=>$this->userId));
        return $this->assignData;
    }
}