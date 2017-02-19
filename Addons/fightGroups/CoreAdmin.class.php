<?php
@include 'Addons/fightGroups/Function/base.php';

class fightGroupsAdminController
{

    const TB_LIST = "addons_partner_list";

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
        $this->assignData['list'] = M(self::TB_LIST)->limit($Page->firstRow, $Page->listRows)->order(" create_time desc")->select();
        $this->assignData['page'] = $show;
        return $this->assignData;
    }

}