<?php

namespace Wap\Controller;

class UserController extends WapBaseController
{

    function exceptAuthActions()
    {
        return array(
            "userInfo"
        );
    }

    /**
     * 初始化操作
     */
    public function _initialize()
    {
        parent::_initialize();
    }


    /**
     * 获取用户数据
     */
    public function userInfo()
    {
        $userInfo = $this->user;
        $return = array(
            "is_login" => false
        );
        if (!empty($userInfo)) {
            $return["is_login"] = true;
            $return["userInfo"] = $userInfo;
        }
        printJson(true, "", $return);
    }


    /**
     * 用户中心首页
     */
    public function index()
    {
        $usersLogic = new \Common\Logic\UsersLogic();
        $data = array(
            "head_img"   => $this->user["head_pic"],
            "userMoney"  => $this->user["user_money"],
            "orderCount" => $usersLogic->getOrderCount($this->user_id)
        );
        printJson(true, "", $data);
    }


    /**
     * 会员中心数据
     */
    public function member()
    {
        $data = array(
            "user"      => array(
                "level_name"        => getLevelName($this->user["level"]),
                "points"            => $this->user["user_points"],
                "head_img"          => $this->user["head_pic"],
                "level"             => $this->user["level"],
                "points_clear_time" => date("Y-m-d", $this->user["points_clear_time"]),
                "need_show_level"   => $this->user["need_show_level"],
            ),
            "log"       => array(
                "item" => M("points_log")->where(array("user_id" => $this->user_id))->field("*, FROM_UNIXTIME(create_time) as time")->order("create_time desc,id desc")->select()
            ),
            "privilege" => getLevelPrivilege($this->user["level"])
        );
        if( $this->user["need_show_level"] == 1){
            saveData("users",array("user_id" => $this->user_id),array("need_show_level" =>0));
        }
        printJson(true, "", $data);
    }

}