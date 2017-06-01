<?php

class foreverCouponAdminController {

    public $assignData = array();

    const TB_USER = "addons_forevercoupon_user";
    const TB_CONFIG = "addons_forevercoupon_config";

    public function __construct()
    {
    }

    //初始页面
    public function index(){
        $this->assignData["list"] = array(
//            array(
//                "title" => "用户列表",
//                "act"   => "userList"
//            ),
            array(
                "title" => "基础设置",
                "act"   => "config"
            ),

        );
        return $this -> assignData;
    }

    public function userList(){
        $count = getCountWithCondition( self::TB_USER);
        $Page  = new \Think\Page( $count , 10 );
        $show = $Page -> show();
        $this->assignData['list'] = M(self::TB_USER)->limit($Page->firstRow,$Page->listRows) -> select();
        $this->assignData['page'] = $show;
        return $this -> assignData;
    }

    public function config(){

        if (($_GET['is_ajax'] == 1) && IS_POST) {
            C('TOKEN_ON', false);
            $couponId = I('coupon_id');
            $data = array(
                "coupon_id" => $couponId
            );
            if( isExistenceDataWithCondition( self::TB_CONFIG ) ){
                saveData( self::TB_CONFIG , array() , $data);
            }else{
                $data['create_time'] = time();
                addData( self::TB_CONFIG , $data);
            }

            $return_arr = array(
                'status' => 1,
                'msg'    => '操作成功',
                'data'   => array('url' => U('Admin/Addons/foreverCoupon', array("pluginName" => "config"))),
            );
            exit(json_encode($return_arr));
        }
        $config = findDataWithCondition( self::TB_CONFIG );
        $this->assignData["config"] = $config;
        return $this -> assignData;
    }
}