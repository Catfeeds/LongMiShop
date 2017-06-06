<?php

/**
 * 获取被邀请奖励
 * @param $userId
 * @return bool
 */
function giveBeInviteGift( $userId ){
    $shopConfig = getShopConfig();
    giveGift( $userId , $shopConfig['prize_invited_to_value'] , $shopConfig['prize_invited_to'] , 0);
    return true;
}


/**
 * 获取邀请奖励
 * @param $userId
 * @param null $orderId
 * @return bool
 */
function giveInviteGift( $userId , $orderId = null ){
    $condition = array(
        "user_id" => $userId,
        "pay_status" => 1,
    );
    $invitedUserId = getInvitedUserId( $userId );
    $isSpecial = isSpecialInvitation($invitedUserId);
    $shopConfig = getShopConfig();
    if($isSpecial){
        if( !is_null($orderId)){
            $condition["order_id"] = $orderId;
        }
        $orderInfo = findDataWithCondition('order',$condition);
        if( !empty($orderInfo)){
            $money = $orderInfo["order_amount"]  * 0.2;
            giveGift( $invitedUserId , $money , $shopConfig['prize_invite'] , 1);
            $userInfo = findDataWithCondition( "users" , array( "user_id" => $userId ) ,"nickname" );
            if(  $shopConfig['prize_invite'] == 2 ){
                sendWeChatMessageUseUserId( $invitedUserId , "邀请奖励2" , array("userName" => $userInfo['nickname'],"money" => $shopConfig['prize_invite_value']) );
            }
            return true;
        }
    }else{
        $orderCount = M('order')->where($condition) ->count();
        if( $orderCount == 1 ){
            giveGift( $invitedUserId , $shopConfig['prize_invite_value'] , $shopConfig['prize_invite'] , 1);
            $userInfo = findDataWithCondition( "users" , array( "user_id" => $userId ) ,"nickname" );
            if(  $shopConfig['prize_invite'] == 2 ){
                sendWeChatMessageUseUserId( $invitedUserId , "邀请奖励" , array("userName" => $userInfo['nickname'],"money" => $shopConfig['prize_invite_value']) );
            }
            return true;
        }
    }

    return false;
}

/**
 * 获取奖励
 * @param null $userID
 * @param null $value
 * @param int $type  1 为卡券  2 为余额 3 为积分
 * @param $isInvite
 * @return bool
 */
function giveGift( $userID = null , $value = null , $type = 1 , $isInvite = 0 ){
    if( is_null( $userID ) ){
        return false;
    }
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
        $add['cid'] = $value;
        $add['type'] = 3;
        $add['uid'] = $userID;
        $add['send_time'] = time();
        do{
            $code = get_rand_str(8,0,1);//获取随机8位字符串
            $check_exist = findDataWithCondition('coupon_list',array('code'=>$code),"code");
            if( empty( $check_exist ) ){
                $check_exist = findDataWithCondition('coupon_code',array('code'=>$code),"code");
            }
        }while($check_exist);
        $add['code'] = $code;
        M('coupon_list')->add($add);
        return true;
    }
    return false;
}


/**
 * 获取礼品情况
 * @param null $value
 * @param int $type
 * @return array
 */
function getGiftInfo( $value = null , $type = 1  ){
    if( !is_null( $value ) ){
        if( $type == 3 ){
            return callback( true , "" ,array( 'point' => $value ,"type" => $type) );
        }
        if( $type == 2 ){
            return callback( true , "" ,array( 'balance' => $value ,"type" => $type) );
        }
        $couponInfo = getCouponInfo($value);
        return callback( true , "" ,array( 'coupon' => $couponInfo ,"type" => $type) );
    }
    return callback( false );
}