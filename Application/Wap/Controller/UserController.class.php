<?php

namespace Wap\Controller;

class UserController extends WapBaseController
{

    function exceptAuthActions()
    {
        return array();
    }

    /**
     * 初始化操作
     */
    public function _initialize()
    {
        parent::_initialize();
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
                "level_name" => getLevelName($this->user["level"]),
                "points"     => $this->user["user_points"],
                "head_img"   => $this->user["head_pic"],
            ),
            "log"       => array(
                "item" => M("points_log")->where(array("user_id" => $this->user_id))->field("*, FROM_UNIXTIME(create_time) as time")->order("create_time desc,id desc")->select()
            ),
            "privilege" => getLevelPrivilege($this->user["level"])
        );
        printJson(true, "", $data);
    }

}