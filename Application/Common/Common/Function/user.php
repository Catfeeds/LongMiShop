<?php

/**
 * 通过用户id获取头像
 * @param null $userId
 * @return string
 */
function getUserHeadImg( $userId = null ){
    if( is_null($userId) ){
        return "";
    }
    $condition = array(
        "user_id" => $userId,
    );
    $userInfo = M('users') -> where($condition) -> field("head_pic") -> find();
    return $userInfo["head_pic"];
}


/**
 * 计算推送消息上次访问时间
 * @param $user_id
 * @return bool
 */
function push_message_time( $user_id ){
    $usr_time = M('push_message')->field('end_time')->where("user_id = '".$user_id."'")->find();
    $art_time = M('article')->field('publish_time')->where('is_open = 1 AND  device_type != 1 ')->order('publish_time DESC')->limit(1)->find();
    if($art_time['publish_time'] > $usr_time['end_time']){
    	return true;
    }else{
    	return false;
    }
}


/**
 * 根据 id 登录
 * @param $userId
 * @return array
 */
function loginFromUserId( $userId ){
    $condition = array(
        "user_id" => $userId,
        "is_lock" => 0
    );
    if( isExistenceDataWithCondition( 'users' ,$condition)){
        session('auth',true);
        session(__UserID__,$userId);
        return callback(true,'登录成功');
    }
    return callback(false,'账号不存在或者异常被锁定');
}


/**
 * 根据 openid 登录
 * @param $openid
 * @return array
 */
function loginFromOpenid( $openid ){
    $condition = array(
        "openid" => $openid,
        "is_lock" => 0
    );
    if( $userInfo = findDataWithCondition( 'users' ,$condition , 'user_id')){
        session('auth',true);
        session(__UserID__,$userInfo["user_id"]);
        M('cart')->where("session_id = '{$this->session_id}'")->save(array('user_id'=>$userInfo["user_id"]));
        return callback(true,'登录成功');
    }
    return callback(false,'账号不存在或者异常被锁定');
}


/**
 * 通过第三方openid 注册
 * @param $openid
 */
function registerFromOpenid( $openid ){
    $data = array(
        'openid'        => $openid ,
        'oauth'         =>'WeChat',
        'nickname'      => $openid,
        'sex'           => 1,
    );
    $usersLogic = new \Common\Logic\UsersLogic();
    $result = $usersLogic -> thirdLogin($data);

    if($result['status'] == 1){
        session('auth',true);
        session(__UserID__,$data['result']['user_id']);

        $condition = array(
            "session_id" => session_id(),
        );
        $save = array(
            'user_id' => $result['result']['user_id']
        );
        M('cart')->where( $condition )->save( $save );
    }
}


/**
 * 判断该账号是否为第三方账号
 * @param $userInfo
 * @return bool
 */
function isThirdAccount( $userInfo ){
    if( empty( $userInfo ) || empty($userInfo['oauth'])){
        return false;
    }
    return true;
}