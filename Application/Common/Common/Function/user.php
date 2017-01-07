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
    $usr_time = M('push_message')->field('end_time') -> where("user_id = '".$user_id."'")->find();
    $art_time = M('article')->field('publish_time') -> where('is_open = 1 AND  device_type != 1 ')->order('publish_time DESC')->limit(1)->find();
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
 * @param bool $needJs
 */
function registerFromOpenid( $openid , $info = array() , $fromTo = "WeChat" , $needJs = true ){
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
    if( !empty( $info['subscribe'] ) ){
        $data['is_follow'] = 1;
        $data['follow_time'] = time();
    }
    $usersLogic = new \Common\Logic\UsersLogic();
    $result = $usersLogic -> thirdLogin($data);

    if( $needJs ){
//    if($result['status'] == 1){
        $openid = session('openid');
        $redirectedUrl = session("redirectedUrl");
        session(null);
        session('openid',$openid);

        if( !empty( $redirectedUrl ) ){
            header("Location: ".$redirectedUrl);
            exit;
        }
        echo "<script language=JavaScript> location.replace(location.href);</script>";
        exit;
//    }
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
        M('cart') -> where(" user_id = 0  and session_id = '".session_id()."'")->save(array('user_id'=>$userInfo["user_id"]));
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
    M('cart') -> where(" user_id = 0  and session_id = '".session_id()."'")->save(array('user_id'=>$loginUserId));

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
    $list = M('invite_list') -> where(array("parent_user_id" => $userId)) -> order('create_time desc') -> select();
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


/**
 * 创建二维码
 * @param $url
 * @param $userId
 * @return string
 */
function createQrCode( $url , $userId){
    vendor("Poster.phpqrcode");
    // 纠错级别：L、M、Q、H
    $level = 'L';
    // 点的大小：1到10,用于手机端4就可以了
    $size = 8;
    // 下面注释了把二维码图片保存到本地的代码,如果要保存图片,用$fileName替换第二个参数false
    //$path = "images/";
    // 生成的文件名
    $returnName='/Public/qrCode/invitation_' . $userId . '.png';
    $fileName = dirname(dirname(dirname(dirname(dirname(__FILE__))))).$returnName;
    QRcode::png($url, $fileName, $level, $size);
    return $returnName ;
}


/**
 * 创建海报
 * @param $userInfo
 */
function createMyPoster( $userInfo , $url )
{
    delFile('./Public/avatar');
    delFile('./Public/middleAvatar');
    delFile('./Public/qrCode');
    $logPath = './Public/avatar';
    if (! file_exists ( $logPath )) {
        mkdir ( $logPath, 0777, true );
    }
    $logPath = './Public/middleAvatar';
    if (! file_exists ( $logPath )) {
        mkdir ( $logPath, 0777, true );
    }
    $logPath = './Public/qrCode';
    if (! file_exists ( $logPath )) {
        mkdir ( $logPath, 0777, true );
    }
    $logPath = './Public/poster';
    if (! file_exists ( $logPath )) {
        mkdir ( $logPath, 0777, true );
    }

    createQrCode( $url, $userInfo["user_id"] );

    vendor("Poster.poster");
    $posterData = array(
        'user_id'     => $userInfo['user_id'],
        'user_name'   => $userInfo['nickname'],
        'headimg'     => $userInfo['head_pic'],
        'url'         => U('invitation', array('invitation_id' =>$userInfo['user_id'])),
        'base_url'    => dirname(dirname(dirname(dirname(dirname(__FILE__))))),
        'file_url'    => '/Public/poster/',
        'file_name'   => 'poster_' . $userInfo['user_id'] . '.png',
        'qrcode_url'  => '/Public/qrCode/',
        'qrcode_name' => 'invitation_' . $userInfo['user_id'] . '.png',
        'img_url'     => '/Public/images/',
        'avatar_url'  => '/Public/avatar/' . $userInfo['user_id'] . '.png',
        'uid'         => $userInfo['user_id']
    );
    Poster::run( $posterData );
}


/**
 * 生成推荐关系
 * @param $userID
 * @param $inviteUserId
 * @param $nickname
 * @param null $shopConfig
 * @return bool
 *
 */
function createInviteRelationship( $userID , $inviteUserId , $nickname , $shopConfig = null ){
    if(
        !empty( $userID ) &&
        !empty( $inviteUserId ) &&
        $userID != $inviteUserId &&
        isExistenceDataWithCondition("users",array("user_id"=>$inviteUserId)) &&
        !isExistenceDataWithCondition("invite_list",array( "user_id" =>$userID)) &&
        !isExistenceDataWithCondition('order',array("user_id" => $userID,"pay_status" => 1))
    ){
        if( is_null( $shopConfig ) ){
            $shopConfig = getShopConfig();
        }
        $addData = array(
            "user_id"           => $userID,
            "parent_user_id"    => $inviteUserId,
            "create_time"       => time(),
            "update_time"       => time(),
        );
        if(isSuccessToAddData( "invite_list" , $addData )){
            giveBeInviteGift( $userID );
        }
        if(  $shopConfig['prize_invited_to'] == 1 ){
            sendWeChatMessageUseUserId( $userID , "送券" , array("couponId" => $shopConfig['prize_invited_to_value']) );
        }
        if(  $shopConfig['prize_invite'] == 2 ){
            sendWeChatMessageUseUserId( $inviteUserId , "成功邀请" , array( "userName" => $nickname ,"money" => $shopConfig['prize_invite_value'] ) );
        }
        return true;
    }
    return false;
}


/**
 * 创建提现申请
 * @param $money
 * @param $userInfo
 * @return array
 */
function createWithdrawDepositApply( $money , $userInfo )
{
    $model = new \Think\Model();

    try {
        $model->startTrans();
        $shopConfig = getShopConfig();
        if (
            (!empty($shopConfig['basic_withdraw_storage']) && $money < $shopConfig['basic_withdraw_storage']) ||
            ($money <= 1 || $money > $userInfo['user_money'])
        ) {
            throw new \Exception('提现金额有误！');
        }

        $applyData = array(
            "user_id"          => $userInfo['user_id'],
            "openid"           => $userInfo['openid'],
            "nickname"         => $userInfo['nickname'],
            "money"            => $money,
            "status"           => 1,
            "application_time" => time(),
        );
        $id = M('withdraw_deposit')->add($applyData);
        if (empty($id)) {
            throw new \Exception('创建提现申请单失败！');
        }

        if (!accountLog($userInfo['user_id'], -$money, 0, "提现扣除")) {
            throw new \Exception('余额扣除失败！');
        }

        $model->commit();

        return callback(true);

    } catch (\Exception $e) {

        $model->rollback();

        return callback(false, $e->getMessage());
    }

}


function checkWithdrawDeposit( $id , $status , $reason ){

    $model = new \Think\Model();

    try {
        $model->startTrans();

        if( empty($id) || empty($status) || !in_array( $status , array( 2 , 3)) ){
            throw new \Exception('参数不正确！');
        }

        $condition = array(
            'id' => $id
        );

        $withdrawDepositInfo = findDataWithCondition( "withdraw_deposit" , $condition , "user_id,money" );
        $userId = $withdrawDepositInfo["user_id"];
        $userInfo = findDataWithCondition( "users" , array('user_id' => $userId ) );

        $data = array(
            "status" =>$status,
        );

        if( $status == 2 ){
            if( empty($reason) ){
                throw new \Exception('请填写不通过理由！');
            }
            $data["remark"] = $reason;
            if (!accountLog( $userId ,$withdrawDepositInfo['money'] , 0, "提现拒绝退回")) {
                throw new \Exception('余额退回失败！');
            }
        }
        $res = M('withdraw_deposit')-> where($condition) -> save($data);

        if( $res == false ){
            throw new \Exception('修改状态失败！');
        }



        if( $status == 3 ){
            $result = userWechatWithdrawDeposit( $userInfo['openid'] , $withdrawDepositInfo['money'] , $userInfo['nickname']);
            if(  !callbackIsTrue( $result ) ){
                throw new \Exception( getCallbackMessage( $result ) );
            }
            $weChatData = getCallbackData( $result );
            if( $weChatData["postData"]['result_code'] == "FAIL" ){
                throw new \Exception( "微信:" . $weChatData["postData"]['err_code_des'] );
            }
        }
        if( $status== 2 ){
            sendWeChatMessageUseUserId( $userId , "拒绝提现" ,array("money" => $withdrawDepositInfo['money'] ,"reason" => $reason ) );
        }
        if( $status== 3 ){
            sendWeChatMessageUseUserId( $userId , "成功提现" ,array("money" => $withdrawDepositInfo['money'] ) );
        }

        $model->commit();

        return callback(true);

    } catch (\Exception $e) {

        $model->rollback();

        return callback(false, $e->getMessage());
    }

}

function findUserId($nickName){
    $user = findDataWithCondition("users",array("nickname"=>$nickName),"user_id");
    return $user['user_id'];
}

function findUserNickName($userId){
    $user = findDataWithCondition("users",array("user_id"=>$userId),"nickname");
    return $user['nickname'];
}


/**
 * 获取等级名称
 * @param null $level
 * @return array|mixed
 */
function getLevelName( $level = null )
{
    $list =  array(
        "1" => "铜米",
        "2" => "银米",
        "3" => "金米",
        "4" => "钻石米",
    );
    if( is_null( $level )){
        return $list;
    }
    return $list[$level];
}

/**
 * 获取等级权限
 * @return array
 */
function getRankPrivilege()
{
    return array(
        1 => array(
            "id"                   => 1,
            "condition"            => 15,
            "growthRate"           => 1,
            "isDeliveryPriority"   => false,
            "cashWithdrawalAmount" => "500",
            "discount"             => 1,
        ),
        2 => array(
            "id"                   => 2,
            "condition"            => 30,
            "growthRate"           => 1.2,
            "isDeliveryPriority"   => false,
            "cashWithdrawalAmount" => "400",
            "discount"             => 0.95,
        ),
        3 => array(
            "id"                   => 3,
            "condition"            => 50,
            "growthRate"           => 1.7,
            "isDeliveryPriority"   => true,
            "cashWithdrawalAmount" => "300",
            "discount"             => 0.9,
        ),
        4 => array(
            "id"                   => 4,
            "condition"            => 100,
            "growthRate"           => 2.2,
            "isDeliveryPriority"   => true,
            "cashWithdrawalAmount" => "1",
            "discount"             => 0.8,
        ),
    );
}

/**
 * 积分日志
 * @param $before_points
 * @param $after_points
 * @param $value
 * @param $userId
 * @param $text
 * @return mixed
 */
function setUserPointsLog( $before_points , $after_points , $value , $userId , $text )
{

    $data = array(
        "user_id"       => $userId,
        "create_time"   => time(),
        "value"         => $value,
        "text"          => $text,
        "before_points" => $before_points,
        "after_points"  => $after_points
    );

    return addData("points_log", $data);
}




/**
 * 修改积分数值
 * @param $type
 * @param $userId
 */
function increasePoints( $type , $userId  )
{
    $condition = array("user_id" => $userId);
    $userInfo = findDataWithCondition("users", $condition, "user_points,level");
    if (empty($userInfo)) {
        return;
    }
    $value = 0;
    $text = "";

    $level = $userInfo['level'];
    $userPoints =$userInfo['user_points'];

    switch ($type) {
        case "login":
            $value = 1;
            $text = "登录奖励";
            break;
        case "register":
            $value = 2;
            $text = "注册赠送";
            break;
        case "sign":
            $value = 1;
            $text = "签到奖励";
            break;
        case "buy":
            $value = 10;
            $text = "购买奖励";
            break;
        case "upgrade":
            $value = 0;
            $text = "升级";
            break;
        case "downgrade":
            $value = -$userPoints;
            $text = "积分清空,降级";
            break;
        case "downgrade2":
            $value = 0;
            $text = "降级";
            break;
        default:
            break;
    }

    if ($value == 0 && $text == "" ) {
        return;
    }


    //根据等级加速积分成长
    if( $value > 0 ){
        $levelArray = getRankPrivilege();
        $value = $levelArray[$level]["growthRate"] * $value;
    }


    $points = $userPoints + $value;
    $data = array(
        "user_points" => $points
    );
    saveData("users", $condition, $data);

    //日志
    setUserPointsLog($userInfo['user_points'], $points, $value, $userId, $text);

    //升级检测
    userUpgradeDetection($userId, $points, $level);
}

/**
 * 用户升级检测
 * @param $userId
 * @param $points
 * @param $level
 * @return bool
 */
function userUpgradeDetection( $userId ,$points,$level)
{
    $levelArray = getRankPrivilege();
    $condition = array(
        "user_id" => $userId
    );
    if ($level <= 3 && $points > $levelArray[4]["condition"]) {
        $condition["total_amount"] = array("egt","20000");
        if ( isExistenceDataWithCondition("order", $condition)) {
            userUpgrade($userId, 4);
        }
    } elseif ($level <= 2 && $points > $levelArray[3]["condition"]) {
        $time = strtotime(date("Y-m-d", strtotime("-1 month")));
        $condition["last_buy_time"] = array("gt" , $time);
        if (isExistenceDataWithCondition("users", $condition)) {
            userUpgrade($userId, 3);
            saveData( "users",array("user_id"=> $userId ) , array("points_clear_time" => strtotime(date("Y-m-d", strtotime("+1 month")) )) );
        }
    } elseif ($level <= 1 && $points > $levelArray[2]["condition"]) {
        $condition["last_buy_time"] = array("gt", "0");
        if (isExistenceDataWithCondition("users", $condition)) {
            userUpgrade($userId, 2);
            saveData( "users",array("user_id"=> $userId ) , array("points_clear_time" => strtotime(date("Y-m-d", strtotime("+1 month")) )) );
        }
    }
    return false;
}

/**
 * 用户升级操作
 * @param $userId
 * @param $level
 * @return bool
 */
function userUpgrade( $userId , $level )
{
    $condition = array(
        "user_id" => $userId
    );
    $discount = 1;
    if ($level == 1) {
        $discount = 1;
    }elseif ($level == 2) {
        $discount = 0.95;
    }elseif ($level == 3) {
        $discount = 0.9;
    }elseif ($level == 4) {
        $discount = 0.8;
    }
    $data = array(
        "level"        => $level,
        "upgrade_time" => time(),
        "discount"     => $discount,
    );
    $res = saveData("users", $condition, $data);
    increasePoints("upgrade", $userId);
    return $res;
}

/**
 * 用户降级操作
 * @param $userId
 * @return bool
 */
function userDowngrade( $userId  ){
//    $condition = array(
//        "user_id" => $userId
//    );
//    $data = array(
//        "level"        => 1,
//        "upgrade_time" => time()
//    );
//    $res = saveData("users", $condition, $data);
    increasePoints("downgrade", $userId);
//    return $res;
}


/**
 * 获取等级权限列表
 * @param null $level
 * @return array
 */
function getLevelPrivilege( $level = null ){
    $items = array(
        "1" => array(
            "name" => "积分成长加速",
            "value" => array(
                "1" => false,
                "2" => "× 1.2",
                "3" => "× 1.7",
                "4" => "× 2.2",
            )
        ),
        "2" => array(
            "name" => "购买包邮",
            "value" => array(
                "1" => true,
                "2" => true,
                "3" => true,
                "4" => true,
            )
        ),
        "3" => array(
            "name" => "每月福利礼包",
            "value" => array(
                "1" => false,
                "2" => true,
                "3" => true,
                "4" => true,
            )
        ),
        "4" => array(
            "name" => "生日礼包",
            "value" => array(
                "1" => "",
                "2" => "普通生日礼包",
                "3" => "Vip生日礼包",
                "4" => "首席生日礼包",
            )
        ),
        "5" => array(
            "name" => "优先发货",
            "value" => array(
                "1" => false,
                "2" => false,
                "3" => true,
                "4" => true,
            )
        ),
        "6" => array(
            "name" => "龙米定制服务",
            "value" => array(
                "1" => false,
                "2" => false,
                "3" => false,
                "4" => true,
            )
        ),
        "7" => array(
            "name" => "用户提现",
            "value" => array(
                "1" => "满500元提现",
                "2" => "满400元提现",
                "3" => "满300元提现",
                "4" => "满1元提现",
            )
        ),
        "8" => array(
            "name" => "优先福利活动",
            "value" => array(
                "1" => false,
                "2" => true,
                "3" => true,
                "4" => true,
            )
        ),
        "9" => array(
            "name" => "生日双倍积分",
            "value" => array(
                "1" => false,
                "2" => true,
                "3" => true,
                "4" => true,
            )
        ),
        "10" => array(
            "name" => "分享赚米",
            "value" => array(
                "1" => true,
                "2" => true,
                "3" => true,
                "4" => true,
            )
        ),
        "11" => array(
            "name" => "专属客服",
            "value" => array(
                "1" => false,
                "2" => false,
                "3" => false,
                "4" => "12小时专项服务",
            )
        ),
        "12" => array(
            "name" => "购买折扣",
            "value" => array(
                "1" => false,
                "2" => "9.5折",
                "3" => "9折",
                "4" => "8折",
            )
        )
    );
    $array = array();
    if( is_null($level)){
        $array = $items;
    }else{
        foreach ( $items as $item){
            if( $item["value"][$level] != false){
                $array[] = $item;
            }
        }
    }
    $data = array(
        "level" => $level,
        "item" => $array
    );
    return $data;
}