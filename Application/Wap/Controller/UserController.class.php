<?php

namespace Wap\Controller;
use Common\Logic\UsersLogic;
use Think\Page;
use Think\Verify;

class UserController extends WapBaseController {

    function exceptAuthActions()
    {
        return array(
            'login',
            'pop_login',
            'do_login',
            'logout',
            'verify',
            'set_pwd',
            'finished',
            'verifyHandle',
            'reg',
            'send_sms_reg_code',
            'find_pwd',
            'check_validate_code',
            'forget_pwd',
            'check_captcha',
            'check_username',
            'send_validate_code',
            'express',
            'sendSmsBindingCode',
            'returnSession',
        );
    }

        /**
        * 初始化操作
        */
    public function _initialize() {
        parent::_initialize();
    }

    /**
     * 用户中心首页
     */
    public function index(){
        $usersLogic = new \Common\Logic\UsersLogic();
        $result = $usersLogic -> getCoupon( $this->user_id);
        $this -> assign('couponCount', $result['data']['count']);
        $this -> assign('orderCount' , $usersLogic -> getOrderCount( $this->user_id));
        $this -> assign('number', getInviteNumber($this ->user_id) );
        $this -> display();
    }


    public function member(){
        $data = array(
            "user" => array(
                "level_name" => getLevelName( $this -> user["level"] ),
                "points" => $this -> user["user_points"],
                "head_img" => $this -> user["head_pic"],
            ),
            "log" => array(
                "item" => M("points_log") -> where( array("user_id" => $this -> user_id ))->field("*, FROM_UNIXTIME(create_time) as time")->order("create_time desc")->select()
            ),
            "privilege" => getLevelPrivilege( $this -> user["level"] )
        );
        printJson(true,"",$data);
    }

}