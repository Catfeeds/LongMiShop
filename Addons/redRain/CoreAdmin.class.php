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

    //列表
    public function index()
    {

        $count = getCountWithCondition(self::TB_WINNING);
        $Page = new \Think\Page($count, 10);
        $show = $Page->show();
        $lists = M(self::TB_WINNING)->limit($Page->firstRow, $Page->listRows)->order("state, create_time desc")->select();
        if( !empty($lists)){
            foreach ($lists as $key => $item ){
                $lists[$key]["user"] = findDataWithCondition("users",array("user_id"=>$item["user_id"]),"nickname");
            }
        }
        $this->assignData['list'] = $lists;
        $this->assignData['page'] = $show;
        return $this->assignData;
    }

    //发放红包
    public function grant()
    {
        $id = I("id",null);
        if(is_null($id)){
            dd("参数错误");
        }
        $info = findDataWithCondition(self::TB_WINNING,array('id'=>$id));
        if(empty($info)){
            dd("参数错误");
        }
        $user = get_user_info($info['user_id']);
        if(empty($user)){
            dd("参数错误");
        }
        $res = redRainSendRed( $user , $info['money'] , $info["version"] , true );
        if( callbackIsTrue($res) ){
            dd("发放成功");
        }else{
            dd("发放失败（可能是用户没和公众号互动或者微信支付商户没钱）[".$res['msg']."]");
        }
    }


}