<?php


/***
 * 获取基础配置
 * @return array
 */
function cookRiceGetConfig()
{
    $array = array(
        "edition"   => 1,
        "data"      => array(
            "1" => array(
                "edition" => 1,
                "name"    => "加温啦",
                "theme"   => "default"
            ),
            "2" => array(
                "edition" => 2,
                "name"    => "加温啦2",
                "theme"   => "default2"
            ),
        ),
        "maxNumber" => "100",
    );
    return $array;
}


/**
 * 获取状态数据
 * @param $userId
 * @param $edition
 * @param null $activityId
 * @return array
 */
function cookRiceGetData( $userId , $edition, $activityId = null)
{
    $condition = array("edition_id" => $edition);
    $id = 0;
    $number = 0;
    $tip = "加温至100摄氏度，成功煮饭即可中奖";
    $helpList = array();

    is_null($activityId) ? $condition["user_id"] = $userId : $condition["id"] = $activityId;
    $activityInfo = findDataWithCondition("addons_cookrice_activity", $condition);
    if (empty($activityInfo)) {
        $state = 1;
    } else {
        $id = $activityInfo["id"];
        if ($activityInfo["user_id"] == $userId) {
            if ($activityInfo["state"] == 1) {
                $tip = "即便不在父母身边&nbsp;也可感受家的味道";
                $state = 5;
            } else if ($activityInfo["state"] == 2) {
                $tip = "即便不在父母身边&nbsp;也可感受家的味道";
                $state = 7;
            } else {
                $state = 2;
            }
        } else {
            unset($condition["id"]);
            $condition["user_id"] = $userId;
            $condition["activity_id"] = $activityId;
            if (isExistenceDataWithCondition("addons_cookrice_help_list", $condition)) {
                $state = 4;
            } else {
                $state = 3;
            }
        }
        $helpList = M("addons_cookrice_help_list") -> where(array("activity_id" => $id)) -> order("create_time desc") -> select();
        if (!empty($helpList)) {
            foreach ($helpList as $helpItem) {
                $number += $helpItem['value'];
            }
        }
    }

    $data = array(
        "id"       => $id,
        "tip"      => $tip,
        "status"   => $state,
        "number"   => $number,
        "helpList" => $helpList,
    );

    return $data;
}


/**
 * 创建活动
 * @param $userId
 * @param $edition
 * @return array
 */
function cookRiceCreateActivity( $userId , $edition)
{
    $condition = array("edition_id" => $edition, "user_id" => $userId);
    if (isExistenceDataWithCondition("addons_cookrice_activity", $condition)) {
        return callback(false, "您已经参与了本次活动");
    }
    $data = array(
        "edition_id"  => $edition,
        "user_id"     => $userId,
        "state"       => "0",
        "create_time" => time()
    );
    $activityId = addData("addons_cookrice_activity", $data);
    $res = cookRiceHelpAction($activityId, $userId, $edition);
    if (callbackIsTrue($res)) {
        return callback(true, "参加活动成功");
    } else {
        return callback(false, getCallbackMessage($res));
    }
}


/**
 * 助力动作
 * @param $activityId
 * @param $userId
 * @param $edition
 * @return array
 */
function cookRiceHelpAction( $activityId, $userId , $edition )
{
    $data = array(
        "state"       => "0",
        "edition_id"  => $edition,
        "activity_id" => $activityId,
    );
    if (!isExistenceDataWithCondition("addons_cookrice_activity", $data)) {
        return callback(false, "活动不存在");
    }
    unset($data["state"]);
    $data["user_id"] = $userId;
    if (isExistenceDataWithCondition("addons_cookrice_help_list", $data)) {
        return callback(false, "您已经帮助过这个小伙伴了");
    } else {

        $helpValue = cookRiceGetHelpValue($activityId, $edition);
        $userInfo = findDataWithCondition("users", array('user_id' => $userId), "head_pic");

        $data["value"] = $helpValue["value"];
        $data["desc"] = $helpValue["desc"];
        $data["head_pic"] = $userInfo["head_pic"];
        $data["create_time"] = time();

        addData("addons_cookrice_help_list", $data);
        //分数检测
        cookRiceTesting($activityId);
        return callback(true, "成功帮助小伙伴");
    }
}


/**
 * 获取助力参数
 * @param $activityId
 * @param $edition
 * @return array
 */
function cookRiceGetHelpValue($activityId,$edition)
{
    $value_a = array(
        array("value" => 1, "desc" => ""),
        array("value" => 2, "desc" => ""),
        array("value" => 3, "desc" => ""),
        array("value" => 4, "desc" => ""),
        array("value" => 5, "desc" => ""),
        array("value" => 6, "desc" => ""),
        array("value" => 7, "desc" => ""),
        array("value" => 8, "desc" => ""),
        array("value" => 9, "desc" => ""),
        array("value" => 10, "desc" => ""),
//        array("value" => 11,"desc" => ""),
//        array("value" => 12,"desc" => ""),
//        array("value" => 13,"desc" => ""),
//        array("value" => 14,"desc" => ""),
//        array("value" => 15,"desc" => ""),
//        array("value" => 16,"desc" => ""),
//        array("value" => 17,"desc" => ""),
//        array("value" => 18,"desc" => ""),
//        array("value" => 19,"desc" => ""),
//        array("value" => 20,"desc" => ""),
//        array("value" => 21,"desc" => ""),
//        array("value" => 22,"desc" => ""),
//        array("value" => 23,"desc" => ""),
//        array("value" => 24,"desc" => ""),
//        array("value" => 25,"desc" => ""),
    );
    $value_b = array(
        array("value" => -1, "desc" => ""),
        array("value" => -2, "desc" => ""),
        array("value" => -3, "desc" => ""),
        array("value" => -4, "desc" => ""),
        array("value" => -5, "desc" => ""),
        array("value" => -6, "desc" => ""),
        array("value" => -7, "desc" => ""),
        array("value" => -8, "desc" => ""),
        array("value" => -9, "desc" => ""),
        array("value" => -10, "desc" => ""),
//        array("value" => -11,"desc" => ""),
//        array("value" => -12,"desc" => ""),
//        array("value" => -13,"desc" => ""),
//        array("value" => -14,"desc" => ""),
//        array("value" => -15,"desc" => ""),
//        array("value" => -16,"desc" => ""),
//        array("value" => -17,"desc" => ""),
//        array("value" => -18,"desc" => ""),
//        array("value" => -19,"desc" => ""),
//        array("value" => -20,"desc" => ""),
//        array("value" => -21,"desc" => ""),
//        array("value" => -22,"desc" => ""),
//        array("value" => -23,"desc" => ""),
//        array("value" => -24,"desc" => ""),
//        array("value" => -25,"desc" => ""),
    );
    $value_c = array_merge($value_a, $value_b);
    $value_d = array_merge($value_c, $value_b);

//    $activityInfo = findDataWithCondition("addons_cookrice_activity",array("id"=>$activityId,"edition_id"=>$edition));
    $helpList = selectDataWithCondition("addons_cookrice_help_list", array('activity_id' => $activityId, "edition_id" => $edition));
    $number = 0;
    if (!empty($helpList)) {
        foreach ($helpList as $helpItem) {
            $number += $helpItem["value"];
        }
    }
//    $numberNew = 0;
//    $date = array();
    do {
        if ($number == 0) {
            $date = $value_a[mt_rand(0,count($value_a)-1)];
        } else {
            if ($number > 80) {
                $date = $value_d[mt_rand(0,count($value_d)-1)];
            } else {
                $date = $value_c[mt_rand(0,count($value_c)-1)];
            }
        }
        $numberNew = $number + $date["value"];
    } while ($numberNew <= 0);

    return array(
        "value" => $date["value"],
        "desc"  => $date["desc"]
    );
}


/**
 * 分数检测
 * @param $activityId
 */
function cookRiceTesting( $activityId )
{
    $config = cookRiceGetConfig();
    $condition = array('activity_id' => $activityId);
    $number = M('addons_cookrice_help_list')->where($condition)->sum("value");
    if ($number >= $config["maxNumber"]) {
        saveData("addons_cookrice_activity", $condition, array('state' => 1));
    }
}


/**
 * 领取动作
 * @param $userId
 * @param $edition
 * @param $get
 * @return array
 */
function cookRiceSetData( $userId, $edition,$get)
{
    $userName = $get["user_name"];
    $userPhone = $get["user_phone"];
    $userSite = $get["user_site"];
    if (!$userName || $userName == '') {
        return callback(false, "得奖人姓名不能为空");
    }
    if (!$userPhone || $userPhone == '') {
        return callback(false, "手机号不能为空");
    }
    if (!check_mobile($userPhone)) {
        return callback(false, "请填写正确的手机号");
    }
    if (!$userSite || $userSite == '') {
        return callback(false, "回寄地址不能为空");
    }
    $condition = array("user_id" => $userId, "edition_id" => $edition, "state" => 1);
    if (isExistenceDataWithCondition("addons_cookrice_activity", $condition)) {
        return callback(false, "活动记录有误");
    }
    unset($condition["state"]);
    $data = array(
        "state"      => 2,
        "user_name"  => $userName,
        "user_phone" => $userPhone,
        "user_site"  => $userSite
    );
    saveData("addons_cookrice_activity", $condition, $data);
    return callback(true, "领取成功");
}

