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

//计算推送消息上次访问时间
function push_message_time($user_id){
    $usr_time = M('push_message')->field('end_time')->where("user_id = '".$user_id."'")->find();
    $art_time = M('article')->field('publish_time')->where('device_type = 2 OR device_type = 3')->order('publish_time DESC')->limit(1)->find();
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
function loginFromUserId($userId){
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