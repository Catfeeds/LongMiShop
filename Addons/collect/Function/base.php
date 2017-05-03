<?php


/***
 * 获取基础配置
 * @return array
 */
function collectGetConfig()
{
    $array = array(
        "edition" => 1,
        "data" => array(
            "1" => array(
                "edition" => 1,
                "name" => "拼图游戏",
                "number" => "1",
                "theme" => "puzzle",
                "endTime" => 1493913600
            ),
        ),
        "maxNumber" =>  array(
            "1" => "100",
        ),
        "name" => array(
            "1" => array(
                "1" => "1",
                "2" => "2",
                "3" => "3",
                "4" => "4",
                "5" => "5",
                "6" => "6",
                "7" => "7",
                "8" => "8",
                "9" => "9",
            ),
        ),
        "getManNumber" => array(
            "1" => "1",
        )
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
function collectGetData($userId, $edition, $activityId = null)
{

    $condition = array("edition_id" => $edition);
    $id = 0;
    $number = array(
        "1" => array(
            "value" => 1,
            "number" => 0
        ),
        "2" => array(
            "value" => 2,
            "number" => 0
        ),
        "3" => array(
            "value" => 3,
            "number" => 0
        ),
        "4" => array(
            "value" => 4,
            "number" => 0
        ),
        "5" => array(
            "value" => 5,
            "number" => 0
        ),
        "6" => array(
            "value" => 6,
            "number" => 0
        ),
        "7" => array(
            "value" => 7,
            "number" => 0
        ),
        "8" => array(
            "value" => 8,
            "number" => 0
        ),
        "9" => array(
            "value" => 9,
            "number" => 0
        ),
    );
    $helpList = array();
    $getList = array();
    $config = collectGetConfig();
    if ($config["data"][$edition]["endTime"] < time()) {
        $state = -1;
        $getList = collectSetGetGiftUserList($getList);
    } else {
        $getList = selectDataWithCondition("addons_collect_activity", array('state' => "2", "edition_id" => $edition));
        if (count($getList) >= $config["getManNumber"][$edition]) {
            $state = -1;
            $getList = collectSetGetGiftUserList($getList);
        } else {
            is_null($activityId) ? $condition["user_id"] = $userId : $condition["id"] = $activityId;
            $activityInfo = findDataWithCondition("addons_collect_activity", $condition);
            if (empty($activityInfo)) {
                $state = 1;
            } else {
                $id = $activityInfo["id"];
                //获奖检测
                collectTesting($id);
                $activityInfo = findDataWithCondition("addons_collect_activity", $condition);
                if ($activityInfo["user_id"] == $userId) {
                    if ($activityInfo["state"] == 1) {
                        $state = 5;
                    } else if ($activityInfo["state"] == 2) {
                        $state = 7;
                    } else {
                        $state = 2;
                    }
                } else {
                    unset($condition["id"]);
                    $condition["user_id"] = $userId;
                    $condition["activity_id"] = $activityId;
                    if ($activityInfo["state"] != 0) {
                        $state = 6;
                    } elseif (isExistenceDataWithCondition("addons_collect_help_list", $condition)) {
                        $state = 4;
                    } else {
                        $state = 3;
                    }
                }
                $helpList = M("addons_collect_help_list")->where(array("activity_id" => $id))->order("create_time desc")->select();

                if (!empty($helpList)) {
                    foreach ($helpList as $helpItem) {
                        $number[$helpItem['value']]["number"]++;
                    }
                }
            }
        }
    }

    $data = array(
        "id" => $id,
        "status" => $state,
        "number" => $number,
        "getList" => $getList,
        "helpList" => $helpList,
    );

    return $data;
}


/**
 * 列表获取
 * @param array $getList
 * @return array
 */
function collectSetGetGiftUserList($getList = array())
{
    $getList[] = array("user_name" => "朱雀堂", "user_phone" => "18818458745");
    $getList[] = array("user_name" => "李丹德", "user_phone" => "13614564563");
    $getList[] = array("user_name" => "周亚纶", "user_phone" => "18976935047");
    $getList[] = array("user_name" => "陈德阳", "user_phone" => "13476933067");
    $getList[] = array("user_name" => "杨雅雯", "user_phone" => "18900570456");
    $getList[] = array("user_name" => "吕洞泽", "user_phone" => "18710625666");
    $getList[] = array("user_name" => "钟亦凡", "user_phone" => "13476933078");
    $getList[] = array("user_name" => "范大梅", "user_phone" => "18710625666");
    $getList[] = array("user_name" => "周大侠", "user_phone" => "15818458482");
    foreach ($getList as $key => $getItem) {
        $len = mb_strlen($getItem['user_name'], 'utf-8');
        if ($len >= 1) {
            $str1 = mb_substr($getItem['user_name'], 0, 1, 'utf-8');
            $str2 = mb_substr($getItem['user_name'], $len - 1, 1, 'utf-8');
            $getList[$key]['user_name'] = $str1 . "*" . $str2;
        }

        $getList[$key]['user_phone'] = substr_replace($getItem['user_phone'], '****', '4', '4');

    }
    return $getList;
}

/**
 * 创建活动
 * @param $userId
 * @param $edition
 * @return array
 */
function collectCreateActivity($userId, $edition)
{
    $condition = array("edition_id" => $edition, "user_id" => $userId);
    if (isExistenceDataWithCondition("addons_collect_activity", $condition)) {
        return callback(false, "您已经参与了本次活动");
    }
    $data = array(
        "edition_id" => $edition,
        "user_id" => $userId,
        "state" => "0",
        "create_time" => time()
    );
    $activityId = addData("addons_collect_activity", $data);
    $res = collectHelpAction($activityId, $userId, $edition);
    if (callbackIsTrue($res)) {
        return callback(true, "参加活动成功", getCallbackData($res));
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
function collectHelpAction($activityId, $userId, $edition)
{
    $data = array(
        "edition_id" => $edition,
        "activity_id" => $activityId,
    );
    $activityInfo = findDataWithCondition("addons_collect_activity", $data);
    if (empty($activityInfo)) {
        return callback(false, "活动不存在");
    }
    if ($activityInfo['state'] != 0) {
        return callback(false, "小伙伴已经中奖啦");
    }
    unset($data["state"]);
    $data["user_id"] = $userId;
    if (isExistenceDataWithCondition("addons_collect_help_list", $data)) {
        return callback(false, "您已经帮助过这个小伙伴了");
    } else {

        $helpValue = collectGetHelpValue($activityId, $edition);
        $userInfo = findDataWithCondition("users", array('user_id' => $userId), "head_pic,nickname");

        $helpValue["desc"] = str_replace("[nickname]", $userInfo["nickname"], $helpValue["desc"]);

        $data["value"] = $helpValue["value"];
        $data["desc"] = $helpValue["desc"];
        $data["head_pic"] = $userInfo["head_pic"];
        $data["create_time"] = time();

        addData("addons_collect_help_list", $data);
        //分数检测
        collectTesting($activityId);
        return callback(true, "成功帮助小伙伴", array('getRose' => 1, 'roseNumber' => $helpValue["value"]));
    }
}


/**
 * 获取助力参数
 * @return array
 */
function collectGetHelpValue()
{
    $config = collectGetConfig();
    $random = array(
        array(
            "value" => 1,
            "keys" => 15,
        ),
        array(
            "value" => 2,
            "keys" => 15,
        ),
        array(
            "value" => 3,
            "keys" => 15,
        ),
        array(
            "value" => 4,
            "keys" => 0,
        ),
        array(
            "value" => 5,
            "keys" => 10,
        ),
        array(
            "value" => 6,
            "keys" => 22,
        ),
        array(
            "value" => 7,
            "keys" => 5,
        ),
        array(
            "value" => 8,
            "keys" => 7,
        ),
        array(
            "value" => 9,
            "keys" => 10,
        ),
    );
    $randoms = array();
    foreach ($random as $randomItem) {
        for ($i = 1; $i < $randomItem["keys"]; $i++) {
            $randoms[] = $randomItem;
        }
    }
    $date = $randoms[mt_rand(0, count($randoms) - 1)];
    $desc_a = array(
        "[nickname]说：嘛哩嘛哩轰！变出了1块碎片！",
        "[nickname]娇羞地从兜里掏出了……1块碎片！",
        "天空忽然变成了黑色，[nickname]手里的碎片点亮了一切！",
        "[nickname]扛着小锄头挖出了1块碎片",
        "[nickname]翻箱倒柜从家里翻出来唯一的1块碎片！",
        "虽然很舍不得，[nickname]还是贡献了1块碎片！",
    );
    $desc = $desc_a[mt_rand(0, count($desc_a) - 1)];
    $desc = str_replace("[value]", $config["name"][$date["value"]], $desc);

    return array(
        "value" => $date["value"],
        "desc" => $desc
    );
}


/**
 * 分数检测
 * @param $activityId
 */
function collectTesting($activityId)
{
    $condition = array('activity_id' => $activityId);
    $numbers = M('addons_collect_help_list')->where($condition)->group("value")->select();
    $number = count($numbers);
    $condition = array('id' => $activityId, 'state' => "0");
    if ($number == 9) {
        saveData("addons_collect_activity", $condition, array('state' => 1));
    }
}


/**
 * 领取动作
 * @param $userId
 * @param $edition
 * @param $get
 * @return array
 */
function collectSetData($userId, $edition, $get)
{
    $condition = array("edition_id" => $edition, "state" => 2);
    if (isExistenceDataWithCondition("addons_collect_activity", $condition)) {
        return callback(false, "奖品已被领完！");
    }
    $userName = $get["user_name"];
    $userPhone = $get["user_phone"];
    $userSite = $get["user_site"];
    $userWechat = $get["user_wechat"];
    if (!$userName || $userName == '') {
        return callback(false, "得奖人姓名不能为空");
    }
    if (!$userPhone || $userPhone == '') {
        return callback(false, "手机号不能为空");
    }
    if (!check_mobile($userPhone)) {
        return callback(false, "请填写正确的手机号");
    }
    if (!$userWechat || $userWechat == '') {
        return callback(false, "微信号不能为空");
    }
    if (!$userSite || $userSite == '') {
        return callback(false, "回寄地址不能为空");
    }
    $condition["user_id"] = $userId;
    $condition["state"] = 1;
    if (!isExistenceDataWithCondition("addons_collect_activity", $condition)) {
        return callback(false, "活动记录有误");
    }
    unset($condition["state"]);
    $data = array(
        "state" => 2,
        "user_name" => $userName,
        "user_phone" => $userPhone,
        "user_wechat" => $userWechat,
        "user_site" => $userSite
    );
    saveData("addons_collect_activity", $condition, $data);
    return callback(true, "领取成功");
}

