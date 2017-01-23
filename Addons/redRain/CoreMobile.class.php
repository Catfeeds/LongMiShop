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

        $this->redConfig = array(
            "1" => array(
                "startTime" => "1484994600",//2017/1/21 20:0:0
                "endTime"   => "1484994900",//2017/1/21 20:05:0
                "number"    => "2",
                "version"   => "1",
                "title"     => "第1波",
                "lastTitle" => "第0波",
                "minMoney"  => "1",
                "maxMoney"  => "1.5",
            ),
            "2" => array(
                "startTime" => "1485000000",//2017/1/21 20:0:0
                "endTime"   => "1485000300",//2017/1/21 20:05:0
                "number"    => "10",
                "version"   => "2",
                "title"     => "第2波",
                "lastTitle" => "第1波",
                "minMoney"  => "1",
                "maxMoney"  => "1.5",
            ),
            "3" => array(
                "startTime" => "1485003600",//2017/1/21 21:0:0
                "endTime"   => "1485003900",//2017/1/21 21:05:0
                "number"    => "10",
                "version"   => "3",
                "title"     => "第3波",
                "lastTitle" => "第2波",
                "minMoney"  => "1",
                "maxMoney"  => "1.5",
            ),
            "4" => array(
                "startTime" => "1485139200",//2017/1/23 10:40
                "endTime"   => "1485139500",//2017/1/23 10:45
                "number"    => "1",
                "version"   => "4",
                "title"     => "第4波",
                "lastTitle" => "第3波",
                "minMoney"  => "1",
                "maxMoney"  => "1.5",
            ),
            "5" => array(
                "startTime" => "1485139800",//2017/1/23 10:50
                "endTime"   => "1485140100",//2017/1/23 10:45
                "number"    => "1",
                "version"   => "5",
                "title"     => "第5波",
                "lastTitle" => "第4波",
                "minMoney"  => "1",
                "maxMoney"  => "1.5",
            ),
            "6" => array(
                "startTime" => "1485140400",//2017/1/23 11:00
                "endTime"   => "1485143400",//2017/1/23 11:05
                "number"    => "2",
                "version"   => "6",
                "title"     => "第6波",
                "lastTitle" => "第5波",
                "minMoney"  => "1",
                "maxMoney"  => "1.5",
            ),
            "7" => array(
                "startTime" => "1485162000",//2017/1/23 11:00
                "endTime"   => "1485165600",//2017/1/23 11:05
                "number"    => "2",
                "version"   => "7",
                "title"     => "第7波",
                "lastTitle" => "第6波",
                "minMoney"  => "1",
                "maxMoney"  => "1.5",
            ),
        );

        $this->assignData["sharePath"]= "./Addons/redRain/Template/Mobile/default/Addons_share.html";
        $this->assignData["headerPath"]= "./Addons/redRain/Template/Mobile/default/Addons_header.html";

        $this->assignData["config"] = array(
            "share_title" => "来和我一起抢红包吧！",
            "share_desc"  => "来和我一起抢红包吧！",
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
//        $currentState = 1;
        $tipMsg = "";
        //获取当前状态数组
        $stateArray = redRainGetCurrentState($this->redConfig, $this->userId);
        switch ($stateArray["state"]) {
            case 1://抢购中
                $currentState = 1;
                $tipMsg = "来啊，抢红包啊！<br>来啊，抢红包啊！<br>来啊，抢红包啊！";
                break;
            case 2://第一波还没开始
                $tipMsg = "红包雨还没开始<br>开始时间<br>" . date( "Y-m-d H:i:s" , $stateArray["data"]["startTime"] );
                break;
            case 3://下一波还没开始
                $tipMsg = $stateArray["data"]["lastTitle"]."已经结束<br>下一波时间<br>" . date( "Y-m-d H:i:s" , $stateArray["data"]["startTime"] );
                break;
            case 4://全部结束
                $tipMsg = "本次红包雨活动已经结束<br>关注公众号<br>更多活动等你来玩";
                break;
            case 5://领取过
                $tipMsg = "您已经领取过<br>这一波的红包啦";
                break;
            case 6://抢完了
                $tipMsg = "手快有手慢无！<br>这波红包已被抢光";
                break;
            default:
                break;
        }

        //关注情况
        $this->assignData["isFollow"] = $this->userInfo["is_follow"];
        

        $this->assignData["tipMsg"] = $tipMsg;
        $this->assignData["currentState"] = $currentState;



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
                    $money = 1;
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