<?php
@include 'Addons/group/Function/base.php';

class groupAdminController
{
    const TB_ACTIVITY = "addons_collectroses_activity";
    const TB_HELP_LIST = "addons_collectroses_help_list";

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
                $helpList = M(self::TB_HELP_LIST) -> where(array('activity_id'=>$item['id']))->group('value')->select();

                $lists[$key]["help"] = $helpList;
            }
        }
        $this->assignData['config'] = collectRosesGetConfig();
        $this->assignData['state'] = array("进行中","待领取","已提交资料");
        $this->assignData['list'] = $lists;
        $this->assignData['page'] = $show;
        return $this->assignData;
    }
}