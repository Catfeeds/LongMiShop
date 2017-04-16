<?php
@include 'Addons/breakFast/Function/base.php';

class breakFastAdminController
{

    const TB_CONFIG = "addons_breakfast_config";
    const TB_DATA = "addons_breakfast_data";

    public $assignData = array();

    public function __construct()
    {
    }

    public function index()
    {

        $this->assignData["list"] = array(
            array(
                "title" => "基础设置",
                "act"   => "config"
            ),
            array(
                "title" => "打卡列表",
                "act"   => "lists"
            ),
//            array(
//                "title" => "统计",
//                "act"   => "statistics"
//            ),

        );
        return $this->assignData;
    }

    public function statistics(){
        return $this->assignData;
    }
    public function config()
    {
        if( IS_POST ){
            $dataList = I("config");
            if( !empty($dataList)){
                foreach ($dataList as $dataKey => $dataItem){
                    $condition = array("key_name" => $dataKey);
                    if(getCountWithCondition(self::TB_CONFIG,$condition)>0){
                        $data = array("val" =>$dataItem);
                        saveData(self::TB_CONFIG,$condition,$data);
                    }else{
                        $data = array("key_name" => $dataKey,"val" =>$dataItem);
                        addData(self::TB_CONFIG,$data);
                    }
                }
                return addonsError("保存成功");
            }
            return addonsError("非法访问");
        }
        $this->assignData["config"] = break_fast_get_config();
        return $this->assignData;
    }

    public function lists()
    {
        $count = getCountWithCondition(self::TB_DATA);
        $Page = new \Think\Page($count, 10);
        $show = $Page->show();
        $lists =  M(self::TB_DATA)->limit($Page->firstRow, $Page->listRows)->order(" create_time desc")->select();
        if(!empty($lists)){
            foreach ($lists as $key =>$list){
                $user = findDataWithCondition("users",array('user_id'=>$list['user_id']),"nickname");
                $lists[$key]["nickname"] = $user["nickname"];
            }
        }
        $this->assignData['list'] =$lists;
        $this->assignData['page'] = $show;
        return $this->assignData;
    }

}