<?php


/**
 * 设置关系
 * @param $userId
 * @param $parentUserId
 * @return bool
 */
function redRainSetInvite($userId , $parentUserId)
{
    if ($parentUserId == $userId) {
        return false;
    }
    if (!isExistenceDataWithCondition("users", array("user_id" => $parentUserId))) {
        return false;
    }
    if (!isExistenceDataWithCondition("users", array("user_id" => $userId))) {
        return false;
    }
    if ( isExistenceDataWithCondition("addons_redrain_invite_list", array("user_id" => $userId))) {
        return false;
    }
    $data = array(
        "user_id"        => $userId,
        "parent_user_id" => $parentUserId,
        "create_time"    => time()
    );
    addData("addons_redrain_invite_list", $data);
    return true;
}


/**
 * 获取当前状态
 * @param $configs
 * @param $userId
 * @return array
 */
function redRainGetCurrentState( $configs , $userId )
{
    $stop = findDataWithCondition("addons_redrain_stop");
    $currentTime = time();
    $isFirst = true;
    foreach ($configs as $config) {
        if ($currentTime < $config["startTime"]) {
            if ($isFirst) {
                return array("state" => 2, "data" => $config);
            } else {
                return array("state" => 3, "data" => $config);
            }
        } else {
            if ($currentTime < $config["endTime"]) {
                if( $stop['stop'] == 1){
                    return array("state" => 6, "data" => $config);
                }
                if (isExistenceDataWithCondition("addons_redrain_winning", array("user_id" => $userId, "version" => $config["version"]))) {
                    return array("state" => 5, "data" => $config);
                }
                $winningNumber = getCountWithCondition("addons_redrain_winning", array("version" => $config["version"]));
                if ($winningNumber < $config["number"]) {
                    return array("state" => 1, "data" => $config);
                }
                return array("state" => 6, "data" => $config);
            }
        }
        $isFirst = false;
    }
    return array("state" => 4);
}

/**
 * 发送微信红包
 * @param $userInfo
 * @param $money
 * @param $version
 * @param bool $needReturn
 * @return bool
 */
function redRainSendRed( $userInfo , $money , $version ,$needReturn = false )
{
    $condition = array(
        "user_id" => $userInfo['user_id'],
        "version" => $version,
        "state"   => "0"
    );
    if (isExistenceDataWithCondition("addons_redrain_winning", $condition)) {
        $result = sendWeChatRed($userInfo['openid'], $money);
        if ( callbackIsTrue($result) && $result["data"]["postData"]['result_code'] != "FAIL") {
            saveData("addons_redrain_winning", $condition, array("state" => "1"));
            if( $needReturn ){
                return true;
            }
        } else {
            if( $needReturn ){
                return false;
            }else{
                setLogResult($result, "红包雨", "addons");
                $jsSdkLogic = new \Common\Logic\JsSdkLogic();
                $jsSdkLogic->push_msg($userInfo['openid'], "恭喜你获得微信红包，工作人员会在两个工作日内将红包发送给你");
            }
        }
    }
    if( $needReturn ){
        return false;
    }
}

/**
 * 红包资格检测
 * @param $userId
 * @param $version
 * @param int $singleLimit
 * @return bool
 */
function redRainAwardQualificationTesting( $userId , $version , $singleLimit = 3 )
{
    return true;
//    if (getCountWithCondition("addons_redrain_winning", array("user_id" => $userId)) >= $singleLimit) {
//        return false;
//    }
//
//    $invite_list = selectDataWithCondition("addons_redrain_invite_list", array("parent_user_id" => $userId));
//    if( !empty($invite_list) ){
//
//    }else{
//        return true;
//    }
//    return true;
}


/**
 * 获取邀请的人的头像
 * @param $userId
 * @return mixed
 */
function redRainGetMyInviteList($userId)
{
    $array = array();
    $inviteMan = findDataWithCondition("addons_redrain_invite_list", array("user_id" => $userId));
    if (!empty($inviteMan)) {
        $userInfo = findDataWithCondition("users", array("user_id" => $inviteMan["parent_user_id"]), "head_pic");
        if (!empty($userInfo["head_pic"])) {
            $array[] = $userInfo["head_pic"];
        }
    }
    $inviteLise = selectDataWithCondition("addons_redrain_invite_list", array("parent_user_id" => $userId));
    if (!empty($inviteLise)) {
        foreach ($inviteLise as $inviteItem) {
            $userInfo = findDataWithCondition("users", array("user_id" => $inviteItem["user_id"]), "head_pic");
            if (!empty($userInfo["head_pic"])) {
                $array[] = $userInfo["head_pic"];
            }
        }
    }
    return $array;
}


/**
 * 获取配置
 * @return array
 */
function redRainGetRedConfig()
{
    if ($_SERVER["HTTP_HOST"] == "www.longmiwang.com") {
        $data = array(
            "1" => array(
                "startTime" => "1485346680",
                "endTime"   => "1485350280",
                "number"    => "100",
                "version"   => "1",
                "title"     => "第1波",
                "lastTitle" => "第0波",
                "minMoney"  => "1",
                "maxMoney"  => "1.5",
                "maxNumber" => "21523",
            ),
            "2" => array(
                "startTime" => "1485519480",
                "endTime"   => "1485523080",
                "number"    => "500",
                "version"   => "2",
                "title"     => "第2波",
                "lastTitle" => "第1波",
                "minMoney"  => "1",
                "maxMoney"  => "1.5",
                "maxNumber" => "20326",
            )
        );
    } else {
        $data = array(
            "1" => array(
                "startTime" => "1485334540",//2017/1/21 20:0:0
                "endTime"   => "1485348600",//2017/1/21 20:05:0
                "number"    => "5",
                "version"   => "1",
                "title"     => "第1波",
                "lastTitle" => "第0波",
                "minMoney"  => "1",
                "maxMoney"  => "1.5",
                "maxNumber" => "20152",
            ),
            "2" => array(
                "startTime" => "1485399600",
                "endTime"   => "1485523080",
                "number"    => "6",
                "version"   => "2",
                "title"     => "第2波",
                "lastTitle" => "第1波",
                "minMoney"  => "1",
                "maxMoney"  => "1.5",
                "maxNumber" => "20326",
            )
        );
    }
    return $data;
}


/**
 * 获取数量
 * @param $config
 * @return float|int
 */
function redRainGetManNumber($config){
    $stop = findDataWithCondition("addons_redrain_stop");
    if( $stop['stop'] == 1){
        return $config["maxNumber"];
    }
    $winningNumber = getCountWithCondition("addons_redrain_winning",array('version'=>$config["version"]));
    if( $winningNumber >= $config["number"] ){
        $number = $config["maxNumber"];
    }else{
        $number = $winningNumber /  $config["number"] *  $config["maxNumber"];
        $number = intval($number);
    }

    return $number;
}