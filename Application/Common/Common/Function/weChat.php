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
//
///**
// * 获取微信绑定方式
// * @return int|mixed
// */
//function getOpenidBindingWay()
//{
//    $bindingWay = empty(C('OPENID_BINDING_WAY')) ? 1 : C('OPENID_BINDING_WAY');
//    return $bindingWay;
//}
//
///**
// * 是否为首次需要登录注册的微信绑定方式
// * @return bool
// */
//function openidBindingWayIsLoginForTheFirstTime()
//{
//    if( getOpenidBindingWay() == C("OPENID_BINDING_WAY_DESC.LoginForTheFirstTime")){
//        return true;
//    }
//    return false;
//}
//
///**
// * 是否为自动注册的微信绑定方式
// * @return bool
// */
//function openidBindingWayIsAutoRegister()
//{
//    if( getOpenidBindingWay() == C("OPENID_BINDING_WAY_DESC.AutoRegister")){
//        return true;
//    }
//    return false;
//}
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
    if( getOpenidBindingWay() == $configArray['OPENID_BINDING_WAY_DESC']['LoginForTheFirstTime'] ){
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
    if( getOpenidBindingWay() == $configArray['OPENID_BINDING_WAY_DESC']['AutoRegister'] ){
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
 * @return bool
 */
function bindingOpenidAngUserId( $openid = null )
{
    $userId = session(__UserID__);
    $data = array(
        "user_id"       => $userId,
        "openid"        => $openid,
        "create_time"   => time(),
        "update_time"   => time(),
    );
    if( !is_null($openid) && !empty($userId) && isSuccessToAddData("binding",$data) ){
        return true;
    }
    return false;
}