<?php
class assistWinningMobileController {

    public $assignData = array();
    public $user = array();

    public $key = null;
    public $temArray = array(
        '1'=>'29',
        '2'=>'18',
        '3'=>'10',
        '4'=>'-16',
        '5'=>'5',
    );
    public $hints = array(
        '29' => "自信一调，温度上升29",
        '18' => '小心一转，温度上升18',
        '10' => '努力控温，温度上升10',
        '-16'=> '好奇打开门，温度下降16',
        '5'=>'煽了个风，温度下降5',
    );

    public function __construct($userInfo)
    {
        $this->user = $userInfo;
        $this -> key = md5("42368");
    }

    //初始页面
    public function index(){
        $Uid = I('id');
//        $Uid = '5823';
        $user_id = $this->user['user_id'];
//        $user_id = '5823';

        if( !empty($Uid) ){
            $where['user_id'] = $Uid;
        }else{
            $where['user_id'] = $user_id;

        }
        $list = M('users')->where($where)->find();
        if($user_id == $list['user_id']){
            //是否中过奖
            $prizeRes = M('addons_assistwinning_prize')->where($where)->find();
            if(!empty($prizeRes)){
                $this -> assignData['msg'] = '领取成功';
                $this -> assignData["prize"] = true;
            }
            //自己给自己加温
            $arrData = array(
                'help_uid'=>$user_id,
                'user_id'=>$where['user_id'],
            );
            $helpRes = M('addons_assistwinning_help')->where($arrData)->find();
            if(empty($helpRes)){
                $arrData['temperature'] = $this->temArray[1];
                $arrData['create_time'] = time();
                M('addons_assistwinning_help')->add($arrData);
            }
        }
        $arrData = array(
            'help_uid'=>$user_id,
            'user_id'=>$where['user_id'],
        );
        $helpRes = M('addons_assistwinning_help')->where($arrData)->find();
        if(empty($helpRes)){
            $list['Ishelp'] = true;
        }
        //查询奖品表数量
        $prize = M('addons_assistwinning_setprize')->find();
        if($prize['sum'] <= 0){
            $this -> assignData['End'] = true;
            $this -> assignData['msg'] = '活动结束';
        }

        $helpList = M('addons_assistwinning_help')->where($where)->order('create_time DESC')->limit(5)->select();
        $list['sumTem'] = 0;
        foreach($helpList as $key=>$item){
            $list['help'][$key] =  M('users')->where(array("user_id"=>$item['help_uid']))->find();
            $list['help'][$key]['temperature'] = $this->hints[$item['temperature']];
            $list['sumTem'] +=  $item['temperature'];
        }
        if($list['sumTem'] > 180){
            $list['sumTem'] = 180;
        }

        $list['visitId'] = $user_id;

        $this -> assignData["list"] = $list;
        $isFollow = M('users')->field('is_follow')->where(array("user_id"=>$user_id))->find();
        $this -> assignData['isFollow'] = $isFollow['is_follow'];
        $this -> assignData['sum'] = $prize['sum'];
        return $this -> assignData;
    }

    public function fillIn(){
        $data = I('post.');
        unset($data['pluginName']);
        $prize = M('addons_assistwinning_prize')->where(array('user_id'=>$this->user['user_id']))->find();
        if(!empty($prize)){
            exit(json_encode(callback(false,'您已中过奖了,请不要重复提交')));
        }
        $prizeName = M('addons_assistwinning_setprize')->find();
        $data['prize'] = $prizeName['prize'];
        $data['user_id'] = $this->user['user_id'];
        $data['create_time'] = time();
        $res = M('addons_assistwinning_prize')->add($data);
        if($res){
            M('addons_assistwinning_setprize')->where(array('id'=>$prizeName['id']))->setInc('sum');
            exit(json_encode(callback(true,'资料提交成功')));
        }
        exit(json_encode(callback(false,'提交失败')));

    }


    //加热
    public function help(){
        $Uid = I('id');
        $user_id = $this->user['user_id'];
        if( !empty($Uid) ){
            $where['user_id'] = $Uid;
        }else{
            $where['user_id'] = $user_id;
        }

        //加热
//        $count = M('addons_assistwinning_help')->where($where)->count();
//        if($count > count($this->temArray) + 1 ){
//            $temperature = 0;
//        }else{
//            $temperature = $this->temArray[$count+1];
//        }
        $temperature = array_rand($this->temArray,1);
        $arrData = array(
            'help_uid'=>$user_id,
            'user_id'=>$where['user_id'],
        );
        $helpRes = M('addons_assistwinning_help')->where($arrData)->find();
        if(empty($helpRes)){
            $arrData['temperature'] = $temperature;
            $arrData['create_time'] = time();
            M('addons_assistwinning_help')->add($arrData);
            exit(json_encode(callback(true,'加温成功')));
        }
        exit(json_encode(callback(false,'加温失败')));
    }


}