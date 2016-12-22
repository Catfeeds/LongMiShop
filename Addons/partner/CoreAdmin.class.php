<?php
@include 'Addons/partner/Function/base.php';

class partnerAdminController {

    const TB_LIST = "addons_partner_list";

    public $assignData = array();

    public function __construct()
    {
    }


    //初始页面
    public function index(){
        $this->assignData["list"] = array(
            array(
                "title" => "申请列表",
                "act"   => "myList"
            )
        );
        return $this->assignData;
    }

    public function myList(){
        $count = getCountWithCondition(TB_LIST);
        $Page  = new \Think\Page( $count , 10 );
        $show = $Page -> show();
        $this->assignData['list'] = M(TB_SHOP)->limit($Page->firstRow,$Page->listRows) -> select();
        $this->assignData['page'] = $show;
        return $this->assignData;
        return $this->assignData;
    }

}