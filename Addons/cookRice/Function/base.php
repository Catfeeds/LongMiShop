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
    $tip = "加温至100摄氏度，成功煮饭即可中奖";

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
    }

    $data = array(
        "id"    => $id,
        "tip"   => $tip,
        "state" => $state,
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
        return callback(false,"您已经参与了本次活动");
    }
    $data = array(
        "edition_id"  => $edition,
        "user_id"     => $userId,
        "state"       => "0",
        "create_time" => time()
    );
    $activityId = addData("addons_cookrice_activity", $data);
    $res =  cookRiceHelpAction( $activityId, $userId , $edition);
    if( callbackIsTrue($res)){
        return callback(true,"参加活动成功");
    }else{
        return callback(false,getCallbackMessage($res));
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
        "edition_id"  => $edition,
        "activity_id" => $activityId,
    );
    if (!isExistenceDataWithCondition("addons_cookrice_activity", $data)) {
        return callback(false, "活动不存在");
    }
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
    $array = array(
        "value" => 0,
        "desc"  => "a"
    );
    return $array;
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




