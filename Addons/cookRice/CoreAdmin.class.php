<?php
@include 'Addons/cookRice/Function/base.php';

class cookRiceAdminController
{
    const TB_ACTIVITY = "addons_cookrice_activity";
    const TB_HELP_LIST = "addons_cookrice_help_list";

    public $assignData = array();

    public function __construct()
    {
    }

    //初始页面
    public function index()
    {

        $count = getCountWithCondition(self::TB_ACTIVITY);
        $Page = new \Think\Page($count, 40);
        $show = $Page->show();
        $lists = M(self::TB_ACTIVITY)->limit($Page->firstRow, $Page->listRows)->order("create_time desc")->select();
        if (!empty($lists)) {
            foreach ($lists as $key => $item) {
                $number = 0;
                $helpList = selectDataWithCondition(self::TB_HELP_LIST, array("activity_id" => $item["id"]));
                if (!empty($helpList)) {
                    foreach ($helpList as $helpItem) {
                        $number += $helpItem["value"];
                    }
                }

                $lists[$key]["help"] = $helpList;
                $lists[$key]["number"] = $number;
            }
        }
        $this->assignData['config'] = cookRiceGetConfig();
        $this->assignData['state'] = array("进行中","待领取","已提交资料");
        $this->assignData['list'] = $lists;
        $this->assignData['page'] = $show;
        return $this->assignData;
    }
}