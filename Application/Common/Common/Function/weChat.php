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
 * 生成推送数据
 * @param $data
 * @param $type
 * @return array|string
 */
function getWeChatMessageData( $data , $type ){
    $condition = array();
    $returnArray = array();


    if( !empty( $data['orderId'] ) ){
        $url = U('Mobile/Order/order_detail',array('order_id' => $data['orderId']));
        $condition['order_id'] = $data['orderId'];
        $orderInfo = findDataWithCondition("order" , $condition , "order_sn" );
        $orderGoodsInfo = findDataWithCondition("order_goods" , $condition , "goods_name" );
        $orderGoodsNumber = 0;
        $orderGoodsNumberList = M("order_goods") -> where($condition) -> field("goods_num") -> select();
        if( !empty($orderGoodsNumberList) ){
            foreach ($orderGoodsNumberList as $orderGoodsNumberItem){
                $orderGoodsNumber += $orderGoodsNumberItem['goods_num'];
            }
        }
//        if( $type == "下单" ){
//            $url = U('Mobile/Order/order_list',array('type'=>'WAITPAY'));
//        }
//        if( $type == "支付" ){
//            $url = U('Mobile/Order/order_list',array('type'=>'WAITSEND'));
//        }
//        if( $type == "完成" ){
//            $url = U('Mobile/Order/order_list',array('type'=>'WAITCCOMMENT'));
//        }
        if( $type == "发货" ){
            $url = U('Mobile/User/express',array('order_id'=> $data['orderId']));
//            $deliveryDocInfo = findDataWithCondition("delivery_doc" , $condition , "invoice_no" );
//            $returnArray["invoiceNo"]   = $deliveryDocInfo["invoice_no"];
        }
        $returnArray["url"]             = 'http://'.$_SERVER["SERVER_NAME"].$url;
        $returnArray["orderSn"]         = $orderInfo["order_sn"];
        $returnArray["goodsName"]       = $orderGoodsInfo["goods_name"];
        $returnArray["goodsNumber"]     = $orderGoodsNumber;
        return $returnArray;
    }
    if( !empty( $data['couponId'] ) ){
        $condition['id'] = $data['couponId'];
        $url = U('Mobile/User/coupon');
        $couponInfo = findDataWithCondition("coupon" , $condition , "name" );
        $returnArray["url"]            = 'http://'.$_SERVER["SERVER_NAME"].$url;
        $returnArray["couponName"]     = $couponInfo["name"];
        return $returnArray;
    }
    if( $type == "成功邀请" || $type == "邀请奖励" ){
        $returnArray = $data;
    }

    return $returnArray;
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
        "送券",
        "成功邀请",
        "邀请奖励",
    );
    if( ! in_array( $type , $typeArray )){
        return false;
    }
    $data = getWeChatMessageData( $data , $type );
    if( empty($data) ){
        return false;
    }

    $messageArray = array(
        "下单"            =>  "为你生成了订单：{$data['goodsName']} 等{$data['goodsNumber']}件，24小时内请完成支付【<a href = '{$data['url']}'>点击支付</a>】，客服热线：4000787725。",
        "支付"            =>  "您的订单：{$data['goodsName']} 等{$data['goodsNumber']}件，已支付成功，我们将在48小时内为您发货【<a href = '{$data['url']}'>查看订单</a>】，客服热线：4000787725。",
        "发货"            =>  "您的订单：{$data['goodsName']} 等{$data['goodsNumber']}件，已发货【<a href = '{$data['url']}'>查看物流信息</a>】。请注意查收，客服热线：4000787725。",
        "完成"            =>  "您的订单：{$data['goodsName']} 等{$data['goodsNumber']}件，交易成功。感谢您的购买！【<a href = '{$data['url']}'>查看详情</a>】，客服热线：4000787725。",
        "送券"            =>  "【系统消息】：我们向您送出了一张【{$data['couponName']}】，【<a href = '{$data['url']}'>点此查看</a>】，客服热线：4000787725。",
        "成功邀请"         =>  "【系统消息】：成功邀请的好友{$data['userName']}，他首次成功购买后，您将获得奖励【{$data['money']}元】",
        "邀请奖励"         =>  "【系统消息】：您邀请的{$data['userName']}完成了首购，您获得奖励【{$data['money']}元】，请在个人中心-钱包里查收",

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
    if( isBinding( $userInfo['user_id'] ) ){
        $bindingUserInfo = getBindingAccountData( $userInfo );
        if( isWeChatUser( $bindingUserInfo['oauth'] )) {
            return sendWeChatMessage( $bindingUserInfo['openid'] , $type , $data  );
        }
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

/*
 * 企业向个人付款
 * @param  access_token
 * @param  openids 
 * 
 */

function userWechatWithdrawDeposit($openids,$amounts,$nickname){
	error_reporting(E_ALL);
	if( empty($openids) ){
		return 'openid不能为空';
	}
	if($amounts < 100){
		return '提现金额不能少于100';
	}
	$weChatConfig = M('wx_user')->find();
    if( empty( $weChatConfig ) ){
        return false;
    }

	$appid = $weChatConfig['openid']; 
	$mch_appid = $appid;
	$openid = $openids; //用户唯一标识
	$mchid = '1394154902'; //商户号
	$nonce_str = 'qyzf'.rand(100000, 999999); //随机数
	$partner_trade_no = 'HW'.time().rand(10000, 99999); //商户订单号
	$check_name = 'NO_CHECK';//校验用户姓名选项，NO_CHECK：不校验真实姓名 FORCE_CHECK：强校验真实姓名（未实名认证的用户会校验失败，无法转账）OPTION_CHECK：针对已实名认证的用户才校验真实姓名（未实名认证用户不校验，可以转账成功）
	$re_user_name = 'test';//收款用户姓名
	$amount = $amounts;//金额（以分为单位，必须大于100）
	$desc = 'test_desc';//描述
	$spbill_create_ip = $_SERVER["REMOTE_ADDR"];//请求ip

	//封装成数据 
	$dataArr=array();
	$dataArr['amount']=$amount;
	$dataArr['check_name']=$check_name;
	$dataArr['desc']=$desc;
	$dataArr['mch_appid']=$mch_appid;
	$dataArr['mchid']=$mchid;
	$dataArr['nonce_str']=$nonce_str;
	$dataArr['openid']=$openid;
	$dataArr['partner_trade_no']=$partner_trade_no;
	$dataArr['re_user_name']=$re_user_name;
	$dataArr['spbill_create_ip']=$spbill_create_ip;

	$sign = getSign($dataArr);

	$data="<xml>
	<mch_appid>".$mch_appid."</mch_appid>
	<mchid>".$mchid."</mchid>
	<nonce_str>".$nonce_str."</nonce_str>
	<partner_trade_no>".$partner_trade_no."</partner_trade_no>
	<openid>".$openid."</openid>
	<check_name>".$check_name."</check_name>
	<re_user_name>".$re_user_name."</re_user_name>
	<amount>".$amount."</amount>
	<desc>".$desc."</desc>
	<spbill_create_ip>".$spbill_create_ip."</spbill_create_ip>
	<sign>".$sign."</sign>
	</xml>";
	
	
	$ch = curl_init ();
	
	$MENU_URL="https://api.mch.weixin.qq.com/mmpaymkttransfers/promotion/transfers";
	curl_setopt ( $ch, CURLOPT_URL, $MENU_URL );
	// curl_setopt ( $ch, CURLOPT_CUSTOMREQUEST, "POST" );
	curl_setopt($ch,CURLOPT_POST, true);
	curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, FALSE );
	curl_setopt ( $ch, CURLOPT_SSL_VERIFYHOST, FALSE );
	//设置header
    curl_setopt($ch,CURLOPT_HEADER,FALSE);
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);

	$zs1="http://" . $_SERVER['HTTP_HOST'] . "/Application/Common/Common/Function/apiclient_cert.pem";
	$zs2="http://" . $_SERVER['HTTP_HOST'] . "/Application/Common/Common/Function/apiclient_key.pem";
	
	curl_setopt($ch,CURLOPT_SSLCERT,$zs1);
	curl_setopt($ch,CURLOPT_SSLKEY,$zs2);
	// curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01;
	// Windows NT 5.0)');
	curl_setopt ( $ch, CURLOPT_FOLLOWLOCATION, 1 );
	curl_setopt ( $ch, CURLOPT_AUTOREFERER, 1 );
	curl_setopt ( $ch, CURLOPT_POSTFIELDS, $data );
	
	$info = curl_exec ( $ch );

	
	if (curl_errno ( $ch )) {
		return curl_error ( $ch );
	}
	
	curl_close ( $ch );
	return $info;
	



}

// /**
//  * 	作用：格式化参数，签名过程需要使用
//  */
function formatBizQueryParaMap($paraMap, $urlencode)
{
	// var_dump($paraMap);//die;
	$buff = "";
	ksort($paraMap);
	foreach ($paraMap as $k => $v)
	{
		if($urlencode)
		{
			$v = urlencode($v);
		}
		//$buff .= strtolower($k) . "=" . $v . "&";
		$buff .= $k . "=" . $v . "&";
	}
	$reqPar;
	if (strlen($buff) > 0)
	{
		$reqPar = substr($buff, 0, strlen($buff)-1);
	}
	// var_dump($reqPar);//die;
	return $reqPar;
}

// /**
//  * 	作用：生成签名
//  */
function getSign($Obj)
{
	// print_r($Obj);die;
	foreach ($Obj as $k => $v)
	{
		$Parameters[$k] = $v;
	}
	//签名步骤一：按字典序排序参数
	ksort($Parameters);
	$String = formatBizQueryParaMap($Parameters, false);
	//echo '【string1】'.$String.'</br>';
	//签名步骤二：在string后加入KEY
	$String = $String."&key=LongMi20161011LongMi20161011Long";
	//echo "【string2】".$String."</br>";
	//签名步骤三：MD5加密
	$String = md5($String);
	//echo "【string3】 ".$String."</br>";
	//签名步骤四：所有字符转为大写
	$result_ = strtoupper($String);
	//echo "【result】 ".$result_."</br>";
	return $result_;
}


