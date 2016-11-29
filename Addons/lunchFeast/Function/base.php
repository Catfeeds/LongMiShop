<?php

/**
 * 生成推荐关系
 * @param $userID
 * @param $inviteUserId
 * @param $nickname
 * @param null $shopConfig
 * @return bool
 *
 */
function lunchFeastCreateInviteRelationship( $userID , $inviteUserId , $nickname , $shopConfig = null ){
    if(
        !empty( $userID ) &&
        !empty( $inviteUserId ) &&
        $userID != $inviteUserId &&
        isExistenceDataWithCondition("users",array("user_id"=>$inviteUserId)) &&
        !isExistenceDataWithCondition("addons_lunchfeast_invite_list",array( "user_id" =>$userID)) &&
        !isExistenceDataWithCondition('addons_lunchfeast_order',array("user_id" => $userID,"status" => array("gt","0")))
    ){
        if( is_null( $shopConfig ) ){
            $shopConfig = getLunchFeastConfig();
        }
        $addData = array(
            "user_id"           => $userID,
            "parent_user_id"    => $inviteUserId,
            "create_time"       => time(),
            "update_time"       => time(),
        );
        if(isSuccessToAddData( "addons_lunchfeast_invite_list" , $addData )){
            lunchFeastGiveBeInviteGift( $userID );
        }
        if(  $shopConfig['invited_to'] == 1 ){
            sendWeChatMessageUseUserId( $userID , "送券" , array("couponId" => $shopConfig['invited_to_value']) );
        }
        if(  $shopConfig['invite'] == 2 ){
            sendWeChatMessageUseUserId( $inviteUserId , "成功邀请" , array( "userName" => $nickname ,"money" => $shopConfig['invited_value'] ) );
        }
        return true;
    }
    return false;
}


/**
 * 获取被邀请奖励
 * @param $userId
 * @return bool
 */
function lunchFeastGiveBeInviteGift( $userId ){
    $shopConfig = getLunchFeastConfig();
    lunchFeastGiveGift( $userId , $shopConfig['invited_to_value'] , $shopConfig['invited_to'] , 0);
    return true;
}


/**
 * 获取邀请奖励
 * @param $userId
 * @return bool
 */
function lunchFeastGiveInviteGift( $userId ){
    $condition = array(
        "user_id" => $userId,
        "status" => array("gt","0")
    );
    $orderCount = getCountWithCondition( 'addons_lunchfeast_order' , $condition );
    if( $orderCount == 1){
        $shopConfig = getLunchFeastConfig();
        $invitedUserId = lunchFeastGetInvitedUserId( $userId );
        lunchFeastGiveGift( $invitedUserId , $shopConfig['invited_value'] , $shopConfig['invite'] , 1);
        $userInfo = findDataWithCondition( "users" , array( "user_id" => $userId ) ,"nickname" );
        $shopConfig = getLunchFeastConfig( );
        if(  $shopConfig['invite'] == 2 ){
            sendWeChatMessageUseUserId( $invitedUserId , "邀请奖励" , array("userName" => $userInfo['nickname'],"money" => $shopConfig['invited_value']) );
        }
        return true;
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
function lunchFeastGiveGift( $userID = null , $value = null , $type = 1 , $isInvite = 0 ){
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
        $add['receive_time'] = time();
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
 * 获取邀请人列表
 * @param $userId
 * @return mixed
 */
function lunchFeastGetInviteList( $userId ){
    $list = M('addons_lunchfeast_invite_list') -> where(array("parent_user_id" => $userId)) -> order('create_time desc') -> select();
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
function lunchFeastGetInviteNumber( $userId ){
    return M('addons_lunchfeast_invite_list') -> where(array("parent_user_id" => $userId)) -> count();
}

/**
 * 获取邀请人id
 * @param $userId
 * @return mixed
 */
function lunchFeastGetInvitedUserId( $userId ){
    $invitedUserInfo = findDataWithCondition( "addons_lunchfeast_invite_list" , array("user_id" => $userId) , "parent_user_id" );
    return $invitedUserInfo['parent_user_id'];
}
/**
 * 获取礼品情况
 * @param null $value
 * @param int $type
 * @return array
 */
function lunchFeastGetGiftInfo( $value = null , $type = 1  ){
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

/**
 * 支付返回
 * @param $orderSn
 * @param $data
 */
function addonsPayNotify( $orderSn , $data ){
    $orderInfo = findDataWithCondition( "addons_lunchfeast_order" , array( "order_sn" => $orderSn ) );
    if( !empty( $orderInfo ) ){
        if( $orderInfo["status"] != 0 ){
            return;
        }
        $add = array(
            "order_id" => $orderInfo["id"],
            "user_id" => $orderInfo["user_id"],
            "openid" => $data["openid"],
            "create_time" => time(),
            "pay_time" => time(),
            "money" => $data["total_fee"]/100,
            "tag" => serialize( $data ),
            "status" => 1,
        );
        addData( "addons_lunchfeast_order_pay_log" , $add );
        $payLogList =selectDataWithCondition( "addons_lunchfeast_order_pay_log" , array('order_id' =>$orderInfo["id"] , "status" => 1 )  , "money");
        $money = 0;
        foreach ($payLogList as $payLogItem){
            $money += $payLogItem["money"];
        }
        if( $orderInfo["pay_amount"] <= $money ){
            saveData( "addons_lunchfeast_order" ,  array( "order_sn" => $orderSn ) , array( 'status' => 1 ,"pay_time" => time()));
            //邀请人奖励
            lunchFeastGiveInviteGift( $orderInfo['user_id'] );
        }
    }
}

/**
 * 获取支付数据
 * @param $orderId
 * @return array
 */
function addonsPayData( $orderId ){
    $id = $orderId;
    $payData = array(
        "order" => "",
        "goUrl" => "",
        "backUrl" => "",
        "notifyUrl" => "",
    );
    if( $_SESSION['openid'] && strstr($_SERVER['HTTP_USER_AGENT'],'MicroMessenger')) {
        $order = findDataWithCondition( "addons_lunchfeast_order" , array("id" => $id));
        if (!empty($order)) {
            if($order['status'] != 0){
                header('Location: '.U('Mobile/Addons/lunchFeast',array('pluginName'=>'orderList')));
                exit;
            }
            $order["order_amount"] = $order["pay_amount"];
            $payData['order'] = $order;
            $payData['goUrl'] = U('Mobile/Addons/lunchFeast', array("pluginName" => "results" , "id" => $id ) );
            $payData['backUrl'] = U('Mobile/Addons/lunchFeast', array("pluginName" => "payBack" , "id" => $id ));
            $payData['notifyUrl'] =  SITE_URL.'/index.php/Api/Addons/lunchFeast/pluginName/notifyUrl';
        }else{
            die("<script>history.go(-1);</script>");
        }
    }
    return $payData;
}

/**
 * 获取配置
 * @return mixed
 */
function getLunchFeastConfig(){
    return findDataWithCondition( "addons_lunchfeast_config" );
}
/**
 * 用餐人置空
 */
function setPitchon($userId){
    M('addons_lunchfeast_diningper')->where(array('uid'=>$userId))->save(array('pitchon'=>0));
}

/**
 * 饭点查询
 */
function selectMealList(){
    return  M('addons_lunchfeast_meal_list')->getField("id ,name" ,true);
}

/**
 * 店铺名字查询
 */
function selectShopList(){
    return  M('addons_lunchfeast_shop')->getField("id ,shop_name" ,true);
}

/**
 * 获取 饭点列表
 * @return mixed
 */
function getMealList(){
    return selectDataWithCondition( "addons_lunchfeast_meal_list" , array( "is_show" => 1 , "is_delete" => "0" ) );
}

/**
 * 获取 店铺列表
 * @return mixed
 */
function getShopList( $userId ){
    $shopList = selectDataWithCondition( "addons_lunchfeast_shop"  );
    if( !empty( $shopList ) ){
        foreach ( $shopList as $shopKey => $shopItem ){
            $condition = array(
                "user_id" => $userId,
                "status" => 2,
                "shop_id" => $shopItem['id']
            );
            if( isExistenceDataWithCondition( "addons_lunchfeast_order" , $condition ) ){
                $shopList[$shopKey]["is_go"] = true;
            }
        }
    }
    return $shopList;
}
/**
 * 获取 店铺菜品列表
 * @param $shopId
 * @param null $mealId
 * @return mixed
 */
function getShopMealList( $shopId , $mealId = null ){
    $condition = array( "shop_id" => $shopId );
    if( !is_null( $mealId ) ){
        $condition["meal_id"] = $mealId;
    }
    return M("addons_lunchfeast_shop_goods") -> where( $condition ) -> order("date") -> select();
}


/**
 * 检查用户token
 * @param $token
 * @return bool
 */
function lunchFeastApiUserToken( $token ){
    $condition = array(
        "token" => $token,
    );
    if( isExistenceDataWithCondition("addons_lunchfeast_admin",$condition)){
        return true;
    }
    return false;
}

/**
 * 核销码验证
 * @param $code
 * @param $token
 * @return array
 */
function lunchFeastApiVerificationCode( $code , $token ){
    $userInfo = findDataWithCondition( "addons_lunchfeast_admin" , array('token' => $token ));
    if( empty($userInfo) ){
        exit(json_encode(callback(false, "用户不存在")));
    }
    $codeInfo = findDataWithCondition( "addons_lunchfeast_order_user" ,array( "code" => $code ) );
    if( empty($codeInfo) ){
        exit(json_encode(callback(false, "核销码不存在")));
    }
    if( $codeInfo["is_use"] == 1 ){
        exit(json_encode(callback(false, "核销码已使用")));
    }
    $orderInfo =  findDataWithCondition( "addons_lunchfeast_order" , array("id" => $codeInfo["order_id"])  );
    if( empty( $orderInfo ) ){
        exit(json_encode(callback(false, "订单不存在")));
    }
    if( $orderInfo["date"] <  strtotime(date("Y-m-d",time())) ){
        exit(json_encode(callback(false, "订单已过期")));
    }
    return array(
        "userInfo" => $userInfo,
        "codeInfo" => $codeInfo,
        "orderInfo" => $orderInfo,
    );
}