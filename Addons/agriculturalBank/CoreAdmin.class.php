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
                $condition = "user_id ='". $item["user_id"]."' and add_time >= '".$item['create_time']."' and pay_status = 1";
                $count = getCountWithCondition("order", $condition);
                $list[$key]["orderCount"] = intval($count);
            }
        }
        $this->assignData['list'] = $list;
        $this->assignData['page'] = $show;
        return $this->assignData;
    }
    //初始页面
    public function order()
    {
        $id = I('id');
        $user_id = I('user_id');
        $info = findDataWithCondition(self::TB_LIST, array("id" => $id));
        $condition = "user_id ='".$user_id."' and add_time >= '".$info['create_time']."' and pay_status = 1";
        $count = getCountWithCondition("order", $condition);
        $Page = new \Think\Page($count, 10);
        $show = $Page->show();
        $lists =M("order")->where($condition)->limit($Page->firstRow, $Page->listRows)->order(" add_time desc")->select();
        $orderLogic = new Admin\Logic\OrderLogic();
        $lists = $orderLogic -> getOrderListInfo( $lists );
        $this->assignData['list'] =$lists;
        $this->assignData['page'] = $show;
        return $this->assignData;
    }

}