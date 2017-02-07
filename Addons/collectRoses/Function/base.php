<?php


/***
 * 获取基础配置
 * @return array
 */
function collectRosesGetConfig()
{
    $array = array(
        "edition"   => 1,
        "data"      => array(
            "1" => array(
                "edition" => 1,
                "name"    => "煮饭游戏",
                "number"  => "5",
                "theme"   => "default",
                "endTime" => 1487174400
            ),
            "2" => array(
                "edition" => 2,
                "name"    => "加温啦2",
                "number"  => "5",
                "theme"   => "default",
                "endTime" => 1487174400
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
function collectRosesGetData( $userId , $edition, $activityId = null)
{

    $condition = array("edition_id" => $edition);
    $id = 0;
    $number = 0;
    $helpList = array();
    $getList = array();
    $config = collectRosesGetConfig();
    if ($config["data"][$edition]["endTime"] < time()) {
        $state = -1;
        $getList = collectRosesSetGetGiftUserList($getList);
    } else {
        $getList = selectDataWithCondition("addons_collectroses_activity", array('state' => "2", "edition_id" => $edition));
        if (count($getList) >= 5) {
            $state = -1;
            $getList = collectRosesSetGetGiftUserList($getList);
        } else {
            is_null($activityId) ? $condition["user_id"] = $userId : $condition["id"] = $activityId;
            $activityInfo = findDataWithCondition("addons_collectroses_activity", $condition);
            if (empty($activityInfo)) {
                $state = 1;
            } else {
                $id = $activityInfo["id"];
                //获奖检测
                collectRosesTesting($id);
                $activityInfo = findDataWithCondition("addons_collectroses_activity", $condition);
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
                    if($activityInfo["state"] != 0 ){
                        $state = 6;
                    }elseif ( isExistenceDataWithCondition("addons_collectroses_help_list", $condition)) {
                        $state = 4;
                    } else {
                        $state = 3;
                    }
                }
                $helpList = M("addons_collectroses_help_list")->where(array("activity_id" => $id))->order("create_time desc")->select();
                if (!empty($helpList)) {
                    foreach ($helpList as $helpItem) {
                        $number += $helpItem['value'];
                    }
                }
                $number = $number > 100 ? 100 : $number;
            }
        }
    }

    $data = array(
        "id"       => $id,
        "status"   => $state,
        "number"   => $number,
        "getList"  => $getList,
        "helpList" => $helpList,
    );

    return $data;
}


/**
 * 列表获取
 * @param array $getList
 * @return array
 */
function collectRosesSetGetGiftUserList( $getList = array() ){
    $getList[] = array("user_name"=>"李文龙","user_phone"=>"13476933067");
    $getList[] = array("user_name"=>"陈雅西","user_phone"=>"18900570106");
    $getList[] = array("user_name"=>"黄海华","user_phone"=>"18710625666");
    $getList[] = array("user_name"=>"陈圆圆","user_phone"=>"18818458745");
    $getList[] = array("user_name"=>"廖德宝","user_phone"=>"13614565845");
    foreach ($getList as $key =>  $getItem){
        $len = mb_strlen($getItem['user_name'],'utf-8');
        if($len>=1){
            $str1 = mb_substr($getItem['user_name'],0,1,'utf-8');
            $str2 = mb_substr($getItem['user_name'],$len-1,1,'utf-8');
            $getList[$key]['user_name'] =  $str1."*".$str2;
        }

        $getList[$key]['user_phone'] =  substr_replace($getItem['user_phone'],'****','4','4');

    }
    return $getList;
}

/**
 * 创建活动
 * @param $userId
 * @param $edition
 * @return array
 */
function collectRosesCreateActivity( $userId , $edition)
{
    $condition = array("edition_id" => $edition, "user_id" => $userId);
    if (isExistenceDataWithCondition("addons_collectroses_activity", $condition)) {
        return callback(false, "您已经参与了本次活动");
    }
    $data = array(
        "edition_id"  => $edition,
        "user_id"     => $userId,
        "state"       => "0",
        "create_time" => time()
    );
    $activityId = addData("addons_collectroses_activity", $data);
    $res = collectRosesHelpAction($activityId, $userId, $edition);
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
function collectRosesHelpAction( $activityId, $userId , $edition )
{
    $data = array(
        "edition_id"  => $edition,
        "activity_id" => $activityId,
    );
    $activityInfo = findDataWithCondition("addons_collectroses_activity", $data);
    if( empty($activityInfo)){
        return callback(false, "活动不存在");
    }
    if( $activityInfo['state'] != 0){
        return callback(false, "小伙伴已经中奖啦");
    }
    unset($data["state"]);
    $data["user_id"] = $userId;
    if (isExistenceDataWithCondition("addons_collectroses_help_list", $data)) {
        return callback(false, "您已经帮助过这个小伙伴了");
    } else {

        $helpValue = collectRosesGetHelpValue($activityId, $edition);
        $userInfo = findDataWithCondition("users", array('user_id' => $userId), "head_pic,nickname");

        $helpValue["desc"] = str_replace("[nickname]",$userInfo["nickname"],$helpValue["desc"]);

        $data["value"] = $helpValue["value"];
        $data["desc"] = $helpValue["desc"];
        $data["head_pic"] = $userInfo["head_pic"];
        $data["create_time"] = time();

        addData("addons_collectroses_help_list", $data);
        //分数检测
        collectRosesTesting($activityId);
        return callback(true, "成功帮助小伙伴");
    }
}


/**
 * 获取助力参数
 * @param $activityId
 * @param $edition
 * @return array
 */
function collectRosesGetHelpValue($activityId,$edition)
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

//    $activityInfo = findDataWithCondition("addons_collectroses_activity",array("id"=>$activityId,"edition_id"=>$edition));
    $helpList = selectDataWithCondition("addons_collectroses_help_list", array('activity_id' => $activityId, "edition_id" => $edition));
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


    $desc_a = array(
        "[nickname]使出了洪荒之力，帮你加温了[value]度",
        "[nickname]轻轻一按，帮你加温了[value]度",
        "[nickname]发现了加温小秘诀，帮你加温了[value]度",
        "[nickname]吃了大力菠菜，帮你加温了[value]度",
    );
    $desc_b = array(
        "[nickname]一个不小心犯错了，减掉了[value]度",
        "[nickname]闭着眼睛乱点，减掉了[value]度",
        "[nickname]碰掉了电插头，减掉[value]度",
    );
    if( $number == 0){
        $desc = "[nickname]使出了洪荒之力，温度上升了[value]度";
    }else{
        if( $date["value"] > 0){
            $desc =  $desc_a[mt_rand(0,count($desc_a)-1)];
        }else{
            $desc =  $desc_b[mt_rand(0,count($desc_b)-1)];
        }
    }
    $desc = str_replace("[value]",$date["value"],$desc);
    return array(
        "value" => $date["value"],
        "desc"  => $desc
    );
}


/**
 * 分数检测
 * @param $activityId
 */
function collectRosesTesting( $activityId )
{
    $config = collectRosesGetConfig();
    $condition = array('activity_id' => $activityId);
    $number = M('addons_collectroses_help_list')->where($condition)->sum("value");
    $condition = array('id' => $activityId,'state' => "0");
    if ($number >= $config["maxNumber"]) {
        saveData("addons_collectroses_activity", $condition, array('state' => 1));
    }
}


/**
 * 领取动作
 * @param $userId
 * @param $edition
 * @param $get
 * @return array
 */
function collectRosesSetData( $userId, $edition,$get)
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
    if (!isExistenceDataWithCondition("addons_collectroses_activity", $condition)) {
        return callback(false, "活动记录有误");
    }
    unset($condition["state"]);
    $data = array(
        "state"      => 2,
        "user_name"  => $userName,
        "user_phone" => $userPhone,
        "user_site"  => $userSite
    );
    saveData("addons_collectroses_activity", $condition, $data);
    return callback(true, "领取成功");
}

