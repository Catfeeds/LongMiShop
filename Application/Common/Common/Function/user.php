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
    $userInfo = findDataWithCondition('users',$condition,"head_pic");
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
    if( isExistenceDataWithCondition( 'users' ,$condition ) ){
        session('auth',true);
        session(__UserID__,$userId);
        return callback(true,'登录成功');
    }
    return callback(false,'账号不存在或者异常被锁定');
}



/**
 * 通过第三方openid 注册
 * @param $openid
 * @param array $info
 * @param string $fromTo
 */
function registerFromOpenid( $openid , $info = array() , $fromTo = "WeChat" ){
    $data = array(
        'openid'        => $openid,
        'oauth'         => $fromTo,
        'nickname'      => $openid,
        'sex'           => 1,
    );
    if( !empty( $info['nickname'] ) ){
        $data['nickname'] = $info['nickname'];
    }
    if( !empty( $info['sex'] ) ){
        $data['sex'] = intval( $info['sex'] );
    }
    if( !empty( $info['headimgurl'] ) ){
        $data['head_pic'] = $info['headimgurl'];
    }
    $usersLogic = new \Common\Logic\UsersLogic();
    $result = $usersLogic -> thirdLogin($data);

//    if($result['status'] == 1){
        $openid = session('openid');
        session(null);
        session('openid',$openid);

        echo "<script language=JavaScript> location.replace(location.href);</script>";
        exit;
//    }
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


/**
 * 获取绑定账号的数据
 * @param $userInfo
 * @param $field
 * @return mixed
 */
function getBindingAccountData( $userInfo ,$field = " * " ){
    $condition = array();
    $userId = $userInfo['user_id'];
    if( isThirdAccount( $userInfo ) ){
        $condition['third_user_id'] = $userId;
        $bindingInfo = findDataWithCondition( "binding" , $condition , "user_id" );
        $otherUserId = $bindingInfo['user_id'];
    }else{
        $condition['user_id'] = $userId;
        $bindingInfo = findDataWithCondition( "binding" , $condition , "third_user_id" );
        $otherUserId = $bindingInfo['third_user_id'];
    }
    $condition = array();
    $condition['user_id'] = $otherUserId;
    return findDataWithCondition( "users" , $condition , $field );
}


/**
 * 是否已经绑定
 * @param $userId
 * @return bool
 */
function isBinding( $userId ){
    $condition = array();
    $condition['user_id'] = $userId;
    if( isExistenceDataWithCondition( "binding" , $condition ) ){
        return true;
    }
    $condition = array();
    $condition['third_user_id'] = $userId;
    if( isExistenceDataWithCondition( "binding" , $condition ) ){
        return true;
    }
    return false;
}



/**
 * 通过手机号注册
 * @param array $info
 * @return bool
 */
function registerFromMobile(  $info = array()  ){
    $data = array(
        'mobile'                => $info['mobile'],
        'nickname'              => $info['mobile'],
        'mobile_validated'      => 1,
        'sex'                   => 1,
        'password'              => "",
        'reg_time'              => time()

    );
    if(isSuccessToAddData( 'users', $data )){
        $userId = M() -> getLastInsID();
        return $userId;
    }
    return false;
}


/**
 * 设置绑定的当前账号
 * @param $currentUserId
 * @param $switchUserId
 */
function setBindingCurrentAccount( $currentUserId , $switchUserId ){
    $condition  = array(
        'current_user_id' => $currentUserId,
    );
    $save = array(
        'update_time' => time(),
        'current_user_id' => $switchUserId,
    );
    M('binding') -> where( $condition ) -> save( $save );
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
        $userId = $userInfo["user_id"];
        if( isBinding($userId) ){
            loginBindingCurrentAccount( $userId );
            return callback(true,'登录成功');
        }
        session('auth',true);
        session(__UserID__,$userId);
        M('cart')->where(" user_id = 0  and session_id = '".session_id()."'")->save(array('user_id'=>$userInfo["user_id"]));
        return callback(true,'登录成功');
    }
    return callback(false,'账号不存在或者异常被锁定');
}


/**
 * 登录绑定的默认账号
 * @param $userId
 */
function loginBindingCurrentAccount( $userId ){
    $condition = array();
    $condition['user_id'] = $userId;
    $condition['third_user_id'] = $userId;
    $condition['_logic'] = 'or';
    $bindingInfo = findDataWithCondition( "binding" , $condition , "current_user_id" );
    $loginUserId = $bindingInfo['current_user_id'];
    session('auth',true);
    session(__UserID__,$loginUserId);
    M('cart')->where(" user_id = 0  and session_id = '".session_id()."'")->save(array('user_id'=>$loginUserId));

}

/**
 * 解除绑定
 * @param $userId
 */
function relieveBinding( $userId ){
    $condition = array();
    $condition['user_id'] = $userId;
    $condition['third_user_id'] = $userId;
    $condition['_logic'] = 'or';
    M('binding') -> where( $condition ) -> delete();
    $openid = session('openid');
    session(null);
    session('openid',$openid);
}




/**
 * 判断是否为微信用户
 * @param $key
 * @return bool
 */
function isWeChatUser( $key = null ){
    if( is_null($key) ){
        return false;
    }
    if( $key == "weixin" || $key == "weChat" || $key == "WeChat" ){
        return true;
    }
    return false;
}


/**
 * 获取邀请人列表
 * @param $userId
 * @return mixed
 */
function getInviteList( $userId ){
    $list = M('invite_list') -> where(array("parent_user_id" => $userId)) -> select();
    if( !empty( $list ) ){
        foreach ( $list as $key => $item){
            $list[$key]['userInfo'] = findDataWithCondition( "users" , array( "user_id" => $item['user_id'] ) );
        }
    }
    return $list;
}

/**
 * 获取邀请人数量
 * @param $userId
 * @return mixed
 */
function getInviteNumber( $userId ){
    return M('invite_list') -> where(array("parent_user_id" => $userId)) -> count();
}

/**
 * 获取邀请人id
 * @param $userId
 * @return mixed
 */
function getInvitedUserId( $userId ){
    $invitedUserInfo = findDataWithCondition( "invite_list" , array("user_id" => $userId) , "parent_user_id" );
    return $invitedUserInfo['parent_user_id'];
}

