<?php


/**
 * 是否在微信浏览器
 * @return bool
 */
function isWeChatBrowser()
{
    if(strstr($_SERVER['HTTP_USER_AGENT'],'MicroMessenger')){
        return true;
    }
    return false;
}

/**
 * 获取微信绑定方式
 * @return int|mixed
 */
function getOpenidBindingWay()
{
    $configArray = getConfigArray();
    $bindingWay = empty( $configArray['OPENID_BINDING_WAY'] ) ? 1 : $configArray['OPENID_BINDING_WAY'];
    return $bindingWay;
}

/**
 * 是否为首次需要登录注册的微信绑定方式
 * @return bool
 */
function openidBindingWayIsLoginForTheFirstTime()
{
    $configArray = getConfigArray();
    if( $configArray['OPENID_BINDING_WAY'] == $configArray['OPENID_BINDING_WAY_DESC']['LoginForTheFirstTime'] ){
        return true;
    }
    return false;
}

/**
 * 是否为自动注册的微信绑定方式
 * @return bool
 */
function openidBindingWayIsAutoRegister()
{
    $configArray = getConfigArray();
    if( $configArray['OPENID_BINDING_WAY'] == $configArray['OPENID_BINDING_WAY_DESC']['AutoRegister'] ){
        return true;
    }
    return false;
}

/**
 * 根据openid 获取用户ID
 * @param null $openid
 * @return null
 */
function getOpenidBindingUserId( $openid = null )
{
    if( is_null($openid) ){
        return null;
    }
    $condition = array(
        "openid" => $openid
    );
    $bindingInfo = M('binding') -> where($condition) ->find();
    return $bindingInfo['user_id'];
}


/**
 * 查看改openid 是否已经注册
 * @param null $openid
 * @return bool
 */
function isExistenceUserWithOpenid( $openid = null )
{
    if( is_null($openid) ){
        return false;
    }
    $condition = array(
        "openid" => $openid
    );
    if( isExistenceDataWithCondition( "users" , $condition ) ){
        return true;
    }
    return false;
}


/**
 * 查看此openid 和 用户 是否绑定过
 * @param null $openid
 * @return bool
 */
function isBindingOpenidAngUserId( $openid = null )
{
    $userId = session(__UserID__);
    $condition = array(
        "openid"     => $openid,
        "user_id"    => $userId,
    );
    if( is_null($openid) || empty($userId) || isExistenceDataWithCondition("binding",$condition)){
        return false;
    }
    return true;
}

/**
 * 绑定
 * @param null $openid
 * @param null $userId
 * @param null $thirdUserId
 * @return bool
 */
function bindingOpenidAngUserId( $openid = null , $userId = null , $thirdUserId = null )
{
    if( is_null($userId) ){
        $userId = session(__UserID__);
    }
    $data = array(
        "user_id"       => $userId,
        "openid"        => $openid,
        "create_time"   => time(),
        "update_time"   => time(),
    );
    if( !is_null( $thirdUserId ) ){
        $data["third_user_id"]  = $thirdUserId;
        $data["current_user_id"]  = $thirdUserId;
    }
    if( !is_null($openid) && !empty($userId) && isSuccessToAddData("binding",$data) ){
        return true;
    }
    return false;
}


/**
 * 发送微信推送
 * @param $openid
 * @param $type
 * @param $data
 * @return bool
 */
function sendWeChatMessage( $openid , $type , $data ){
    $typeArray = array(
        "下单",
        "支付",
        "发货",
        "完成",
    );
    if( ! in_array( $type , $typeArray )){
        return false;
    }
//        "下单" => __WeChatMessage_CreateOrder__,
//        "支付" => __WeChatMessage_Payment__,
//        "发货" => __WeChatMessage_Delivery__,
    $messageArray = array(
        "下单" =>  "您刚刚下了一笔订单【{$data['orderSn']}】尽快支付,过期失效!",
        "支付" =>  "您的订单【{$data['orderSn']}】已支付,我们会尽快发货!",
        "发货" =>  "您的订单【{$data['orderSn']}】已发货,物流单号【{$data['logisticsSn']}】!",
        "完成" =>  "您的订单【{$data['orderSn']}】已确认收货，感谢您的购买!",

    );
    $weChatConfig = M('wx_user')->find();
    if( empty( $weChatConfig ) ){
        return false;
    }
    $jsSdkLogic = new \Common\Logic\JsSdkLogic($weChatConfig['appid'], $weChatConfig['appsecret']);
    $jsSdkLogic -> push_msg( $openid , $messageArray[$type] );
    return true;
}


/**
 * 根据用户信息发微信推送
 * @param $userInfo
 * @param $type
 * @param $data
 * @return bool
 */
function sendWeChatMessageUseUserInfo( $userInfo , $type , $data ){
    // 如果有微信公众号 则推送一条消息到微信
    if( isWeChatUser( $userInfo['oauth'] )) {
        return sendWeChatMessage( $userInfo['openid'] , $type , $data  );
    }
    return false;
}

/**
 * 根据用户 ID 发微信推送
 * @param $userId
 * @param $type
 * @param $data
 * @return bool
 */
function sendWeChatMessageUseUserId( $userId , $type , $data ){
    $condition = array(
        "user_id" => $userId,
    );
    $userInfo = findDataWithCondition('users',$condition);
    if( empty($userInfo) ){
        return false;
    }
    return sendWeChatMessageUseUserInfo( $userInfo , $type , $data );
}