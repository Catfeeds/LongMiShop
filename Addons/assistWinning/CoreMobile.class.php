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
            '1'=>'25',
            '2'=>'20',
            '3'=>'15',
            '4'=>'10',
            '5'=>'5',
        );
        if( !empty($Uid) ){
            $where['user_id'] = $Uid;
        }else{
            $where['user_id'] = $user_id;
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
//        dd($list);
        $list['visitId'] = $user_id;
        $this -> assignData["list"] = $list;
        return $this -> assignData;
    }


    public function history(){

    }


}