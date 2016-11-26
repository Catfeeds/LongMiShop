<?php

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
        if( $orderInfo["order_amount"] <= $money ){
            saveData( "addons_lunchfeast_order" ,  array( "order_sn" => $orderSn ) , array( 'status' => 1 ,"pay_time" => time()));
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
        $payData['order'] = $order = findDataWithCondition( "addons_lunchfeast_order" , array("id" => $id));
        if (!empty($order)) {
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
function getShopList(){
    return selectDataWithCondition( "addons_lunchfeast_shop" , array( "is_online" => 1 ) );
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