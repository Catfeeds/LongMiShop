<?php
@include 'Addons/redRain/Function/base.php';
class redRainAdminController
{

    const TB_WINNING = "addons_redrain_winning";

    public $assignData = array();
    public $redConfig = array();


    public function __construct( )
    {
        $this->assignData["redConfig"] = $this->redConfig = redRainGetRedConfig();
        $this->assignData["stateTest"] = array(
            "0" => "未发放",
            "1" => "已发放",
        );
    }

    public function index()
    {

        $count = getCountWithCondition(self::TB_WINNING);
        $Page = new \Think\Page($count, 10);
        $show = $Page->show();
        $lists = M(self::TB_WINNING)->limit($Page->firstRow, $Page->listRows)->order(" create_time desc")->select();
        if( !empty($lists)){
            foreach ($lists as $key => $item ){
                $lists[$key]["user"] = findDataWithCondition("users",array("user_id"=>$item["user_id"]),"nickname");
            }
        }
        $this->assignData['list'] = $lists;
        $this->assignData['page'] = $show;
        return $this->assignData;
    }

//    public function ()
//    {
//
//        $count = getCountWithCondition(self::TB_WINNING);
//        $Page = new \Think\Page($count, 10);
//        $show = $Page->show();
//        $lists = M(self::TB_WINNING)->limit($Page->firstRow, $Page->listRows)->order(" create_time desc")->select();
//        if( !empty($lists)){
//            foreach ($lists as $key => $item ){
//                $lists[$key]["user"] = findDataWithCondition("users",array("user_id"=>$item["user_id"]),"nickname");
//            }
//        }
//        $this->assignData['list'] = $lists;
//        $this->assignData['page'] = $show;
//        addonsSuccess("发放成功");
//    }


}