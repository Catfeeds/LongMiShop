<?php
class assistWinningMobileController {

    public $assignData = array();
    public $user = array();

    public $key = null;


    public function __construct($userInfo)
    {
        $this->user = $userInfo;
        $this -> key = md5("42368");
    }

    //初始页面
    public function index(){
        $Uid = I('id');

        $user_id = $this->user['user_id'];
//        $user_id = '5823';
        $temArray = array(
            '1'=>'100',
            '2'=>'80',
            '3'=>'30',
            '4'=>'10',
            '5'=>'5',
        );
        if( !empty($Uid) ){
            $where['user_id'] = $Uid;
        }else{
            $where['user_id'] = $user_id;
            //是否中过奖
            $prizeRes = M('addons_assistwinning_prize')->where($where)->find();
            if(!empty($prizeRes)){
                $this -> assignData["prize"] = true;
            }
        }


        //加热
        $count = M('addons_assistwinning_help')->where($where)->count();
        if($count > count($temArray) + 1 ){
            $temperature = 0;
        }else{
            $temperature = $temArray[$count+1];
        }
        $arrData = array(
            'help_uid'=>$user_id,
            'user_id'=>$where['user_id'],
        );

        $helpRes = M('addons_assistwinning_help')->where($arrData)->find();
        if(empty($helpRes)){
            $arrData['temperature'] = $temperature;
            $arrData['create_time'] = time();
            M('addons_assistwinning_help')->add($arrData);
        }

        $list = M('users')->where($where)->find();
        $helpList = M('addons_assistwinning_help')->where($where)->order('create_time ASC')->limit(5)->select();
        $list['sumTem'] = 0;
        foreach($helpList as $key=>$item){
            $list['help'][$key] =  M('users')->where(array("user_id"=>$item['help_uid']))->find();
            $list['help'][$key]['temperature'] = $item['temperature'];
            $list['sumTem'] +=  $item['temperature'];
        }
        if($list['sumTem'] > 180){
            $list['sumTem'] = 180;
        }
        $list['visitId'] = $user_id;
        $this -> assignData["list"] = $list;
        return $this -> assignData;
    }

    public function fillIn(){
        $data = I('post.');
        unset($data['pluginName']);
        $prize = M('addons_assistwinning_prize')->where(array('user_id'=>$this->user['user_id']))->find();
        if(!empty($prize)){
            exit(json_encode(callback(false,'您已中过奖了,请不要重复提交')));
        }
        $data['prize'] = '烤箱';
        $data['user_id'] = $this->user['user_id'];
        $data['create_time'] = time();
        $res = M('addons_assistwinning_prize')->add($data);
        if($res){
            exit(json_encode(callback(true,'资料提交成功')));
        }

    }


    public function history(){

    }


}