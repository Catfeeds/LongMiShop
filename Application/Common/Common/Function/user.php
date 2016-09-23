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
    $art_time = M('article')->field('publish_time')->order('publish_time DESC')->limit(1)->find();
    if($art_time['publish_time'] > $usr_time['end_time']){
    	return true;
    }else{
    	return false;
    }

}