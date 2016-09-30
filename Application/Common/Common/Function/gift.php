<?php

/**
 * 获取被邀请奖励
 * @param $userId
 * @return bool
 */
function giveBeInviteGift( $userId ){
    $shopConfig = getShopConfig();
    giveGift( $userId , $config['invited_to_value'] , $config['invited_to'] , 0);
    return true;
}


/**
 * 获取邀请奖励
 * @param $userId
 * @return bool
 */
function giveInviteGift( $userId ){
    $condition = array(
        "user_id" => $userId,
        "pay_status" => 1,
    );
    $orderCount = M('order') -> where($condition) -> count();
    if( $orderCount == 1){
        $shopConfig = getShopConfig();
        giveGift( $userId , $config['invite_value'] , $config['invite'] , 1);
        return true;
    }
    return false;
}

/**
 * 获取奖励
 * @param $userID
 * @param null $value
 * @param int $type  1 为卡券  2 为余额 3 为积分
 * @param $isInvite
 * @return bool
 */
function giveGift( $userID , $value = null , $type = 1 , $isInvite = 0 ){
    $log = $isInvite ? "邀请奖励" : "系统奖励";
    if( !is_null( $value ) ){
        if( $type == 3 ){
            accountLog( $userID , 0 , $value , $log);
            return true;
        }
        if( $type == 2 ){
            accountLog( $userID , $value , 0 , $log);
            return true;
        }

        return true;
    }
   return false;
}