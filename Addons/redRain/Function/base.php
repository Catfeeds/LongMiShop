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


function redRainSendRed( $userInfo , $money){
    $jsSdkLogic = new \Common\Logic\JsSdkLogic();
    $jsSdkLogic -> push_msg( $userInfo['openid'] , "此处会换成发红包" );

}


function redRainAwardQualificationTesting( $userId ){
    return true;
}


/**
 * 获取邀请的人的头像
 * @param $userId
 * @return mixed
 */
function redRainGetMyInviteList($userId){

    $array = array();

    $inviteMan = findDataWithCondition("addons_redrain_invite_list",array("user_id"=>$userId));
    if( !empty($inviteMan)){
        $userInfo = findDataWithCondition("users",array("user_id"=>$inviteMan["parent_user_id"]),"head_pic");
        if( !empty($userInfo["head_pic"]) ){
            $array[] = $userInfo["head_pic"];
        }
    }
    $inviteLise = selectDataWithCondition("addons_redrain_invite_list",array("parent_user_id"=>$userId));
    if( !empty($inviteLise)){
        foreach ( $inviteLise as $inviteItem){
            $userInfo = findDataWithCondition("users",array("user_id"=>$inviteItem["user_id"]),"head_pic");
            if( !empty($userInfo["head_pic"]) ){
                $array[] = $userInfo["head_pic"];
            }
        }
    }
    return $array;
}