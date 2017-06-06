<?php
/**
 * 积分类定时任务
 */


class PointsCronClass
{

    public function init()
    {
        /**
         *  3 降 2
         */
        $time = strtotime(date("Y-m-d", strtotime("-1 month")));
        $condition = array(
            "level" => "3",
            "last_buy_time" => array("lt",$time),
        );
        $save = array(
            "level" => 2,
            "discount" => 0.95,
            "upgrade_time" => time()
        );
        $userList = selectDataWithCondition("users",$condition);
        saveData("users",$condition,$save);
        foreach ( $userList as $userItem){
            increasePoints("downgrade2", $userItem["user_id"]);
            changeOrderMemberMoney(2,$userItem["user_id"]);
        }


        /**
         * 清空积分部分
         */
        $condition = array(
            "level" => array("in","2,3"),
            "points_clear_time" => array("lt",time()),
        );
        $save = array(
            "level" => 1,
            "points_clear_time" => 0,
            "discount" => 1,
            "need_show_level" => 0,
            "upgrade_time" => time()
        );
        $userList = selectDataWithCondition("users",$condition);
        saveData("users",$condition,$save);
        foreach ( $userList as $userItem){
            userDowngrade($userItem["user_id"]);
            changeOrderMemberMoney(2,$userItem["user_id"]);
        }
    }

}

$PointsCronClassObj = new PointsCronClass();
$PointsCronClassObj -> init();