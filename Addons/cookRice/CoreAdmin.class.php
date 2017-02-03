<?php
use Think\AjaxPage;


class cookRiceAdminController {

    public $assignData = array();

    public function __construct()
    {
    }

    //初始页面
    public function index(){
        $this -> assignData["list"] = array(
            array(
                "title" => "奖品设置",
                "act"   => "setPrize"
            ),
            array(
                "title" => "中奖列表",
                "act"   => "prize"
            )
        );
        return $this -> assignData;

    }
    public function setPrize(){
        $list = M('addons_assistwinning_setprize')->find();
        $this -> assignData['list'] = $list;
        return $this -> assignData;
    }

    public function PostSet(){
        $data = I('post.');
        unset($data['pluginName']);
        $data['uptatetime'] = time();
        $res = M('addons_assistwinning_setprize')->save($data);
        if($res){
            exit(json_encode(callback(true,'修改成功')));
        }
        exit(json_encode(callback(false,'修改失败')));

    }
    public function prize(){
        $prefix = C('DB_PREFIX');
        $join = $prefix."users ON ".$prefix."addons_assistwinning_prize.user_id = ".$prefix."users.user_id";
        I('mobile') ? $condition[$prefix.'users.mobile'] = I('mobile') : false;
        I('nickname') ? $condition[$prefix.'users.nickname'] = array("like" , "%".I('nickname')."%") : false;

        $count = M('addons_assistwinning_prize')->join($join)->where($condition)->count();
        $Page  = new AjaxPage($count,10);
        //  搜索条件下 分页赋值
        foreach($condition as $key=>$val) {
            $Page->parameter[$key]   =   urlencode($val);
        }
        $field = $prefix.'addons_assistwinning_prize.*,'.$prefix.'users.nickname,'.$prefix.'users.user_id';
        $userList = M('addons_assistwinning_prize')->field($field)->join($join)->where($condition)->limit($Page->firstRow.','.$Page->listRows)->select();

        $this -> assignData['page'] = $Page -> show();
        $this -> assignData['userList'] = $userList;
        return $this -> assignData;
    }
}