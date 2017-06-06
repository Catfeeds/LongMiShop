<?php
@include 'Addons/agriculturalBank/Function/base.php';

class agriculturalBankAdminController
{

    const TB_LIST = "addons_agriculturalbank_list";

    public $assignData = array();

    public function __construct()
    {
    }


    //初始页面
    public function index()
    {
        $count = getCountWithCondition(self::TB_LIST);
        $Page = new \Think\Page($count, 10);
        $show = $Page->show();
        $list =  M(self::TB_LIST)->limit($Page->firstRow, $Page->listRows)->order(" create_time desc")->select();
        if( !empty($list)){
            foreach ($list as $key => $item){
                $list[$key]["user"] = get_user_info($item["user_id"]);
            }
        }
        $this->assignData['list'] = $list;
        $this->assignData['page'] = $show;
        return $this->assignData;
    }

}