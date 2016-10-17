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
    if( $type == "成功邀请" || $type == "邀请奖励" || $type == "拒绝提现" || $type == "成功提现" ){
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
        "拒绝提现",
        "成功提现",
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
        "拒绝提现"         =>  "【系统消息】:您有一笔{$data['money']}元的提现申请被拒绝！原因：{$data['reason']}",
        "成功提现"         =>  "【系统消息】:您有一笔{$data['money']}元的提现申请成功！",
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

/**
 * 企业向个人付款
 * @param $openids
 * @param $amounts
 * @param $nickname
 * @param string $title
 * @return array
 */
function userWechatWithdrawDeposit($openids,$amounts,$nickname,$title = "用户提现" ){
	if( empty($openids) ){
		return callback( false , 'openid不能为空' );
	}
	if($amounts*100 < 100){
        return callback( false , '提现金额不能少于100' );
	}
	$weChatConfig = M('wx_user')->find();
    if( empty( $weChatConfig ) ){
        return callback( false , '微信未配置' );
    }

	$appid = $weChatConfig['appid']; 
    $pluginRes  = M('plugin')->where(array('code'=>'weixin','name'=>'微信支付'))->find();
    $key = unserialize($pluginRes['config_value']);
    $merchantConf = M('merchant_conf')->where(array('wx_uid'=>$weChatConfig['id']))->find();
    
    $keyRes = $key['key'];
	$mch_appid = $appid;
	$openid = $openids; //用户唯一标识
	$mchid = $merchantConf['merchant']; //商户号
	$nonce_str = 'qyzf'.rand(100000, 999999); //随机数
	$partner_trade_no = 'HW'.time().rand(10000, 99999); //商户订单号
	$check_name = 'NO_CHECK';//校验用户姓名选项，NO_CHECK：不校验真实姓名 FORCE_CHECK：强校验真实姓名（未实名认证的用户会校验失败，无法转账）OPTION_CHECK：针对已实名认证的用户才校验真实姓名（未实名认证用户不校验，可以转账成功）
	$re_user_name = 'test';//收款用户姓名
	$amount = $amounts*100;//金额（以分为单位，必须大于100）
	$desc = $title;//描述
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

	$sign = getSign($dataArr,$keyRes);

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
	curl_setopt ( $ch, CURLOPT_CUSTOMREQUEST, "POST" );
	curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,FALSE);
	curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,FALSE);
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
    curl_setopt($ch, CURLOPT_TIMEOUT,60); 
	// $zs1="http://" . $_SERVER['HTTP_HOST'] . "/Application/Common/Common/Function/apiclient_cert.pem";
	// $zs2="http://" . $_SERVER['HTTP_HOST'] . "/Application/Common/Common/Function/apiclient_key.pem";
	$zs1= $_SERVER['DOCUMENT_ROOT'].$merchantConf['apiclient_cert'];
	$zs2= $_SERVER['DOCUMENT_ROOT'].$merchantConf['apiclient_key'];
    dd($zs1);
	curl_setopt($ch,CURLOPT_SSLCERT,$zs1);
	curl_setopt($ch,CURLOPT_SSLKEY,$zs2);
	// curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01;
	// Windows NT 5.0)');
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION,1);
	curl_setopt($ch, CURLOPT_AUTOREFERER,1);
	curl_setopt($ch, CURLOPT_POSTFIELDS,$data );
	$info = curl_exec($ch);
	libxml_disable_entity_loader(true);
    // $postObj = simplexml_load_string($info, 'SimpleXMLElement', LIBXML_NOCDATA);
    // dd($postData);
    // dd($postObj->return_code);
    $postData = xmlToArray($info);
	if (empty($postData) ) {
        return callback( false , curl_error($ch));
	}
	// $curl_info= curl_getinfo($ch);
	// $error = curl_error($ch);
	curl_close($ch);
    return callback( true , "" , array('postData'=>$postData,'data'=>$data) );
//	return array('postData'=>$postData,'data'=>$data);
	



}

/**
 * 	作用：格式化参数，签名过程需要使用
 */
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

/**
 * 	作用：生成签名
 */
function getSign($Obj,$key)
{
	// print_r($Obj);die;
	foreach ($Obj as $k => $v)
	{
		$Parameters[$k] = $v;
	}
	ksort($Parameters);
	$String = formatBizQueryParaMap($Parameters, false);
	$String = $String."&key=".$key."";
	$String = md5($String);
	$result_ = strtoupper($String);
	return $result_;
}

//将XML转为array
function xmlToArray($xml)
{    
    //禁止引用外部xml实体
    libxml_disable_entity_loader(true);
    $values = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);        
    return $values;
}
    





/**
 *  array转xml
 */
// function arrayToXml($arr)
// {
//     $xml = "<xml>";
//     foreach ($arr as $key=>$val)
//     {
//         if (is_numeric($val))
//         {
//                $xml.="<".$key.">".$val."</".$key.">"; 

//         }
//         else
//         $xml.="<".$key."><![CDATA[".$val."]]></".$key.">";  
//     }
//     $xml.="</xml>";
//     return $xml; 
// }

/**
*   作用：使用证书，以post方式提交xml到对应的接口url
*/
// function postXmlSSLCurl($xml, $url, $second, $cert, $key)
// {
//     $ch = curl_init();
//     //超时时间
//     curl_setopt($ch,CURLOPT_TIMEOUT,$second ? $second : 60);
    
//     //这里设置代理，如果有的话
//     //curl_setopt($ch,CURLOPT_PROXY, '8.8.8.8');
//     //curl_setopt($ch,CURLOPT_PROXYPORT, 8080);
//     curl_setopt($ch,CURLOPT_URL, $url);
//     curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,FALSE);
//     curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,FALSE);
//     //设置header
//     curl_setopt($ch,CURLOPT_HEADER,FALSE);
//     //要求结果为字符串且输出到屏幕上
//     curl_setopt($ch,CURLOPT_RETURNTRANSFER,TRUE);
//     //设置证书
//     //使用证书：cert 与 key 分别属于两个.pem文件
//     //默认格式为PEM，可以注释
//     curl_setopt($ch,CURLOPT_SSLCERTTYPE,'PEM');
//     curl_setopt($ch,CURLOPT_SSLCERT,$cert);
//     //默认格式为PEM，可以注释
//     curl_setopt($ch,CURLOPT_SSLKEYTYPE,'PEM');
//     curl_setopt($ch,CURLOPT_SSLKEY, $key);
//     //post提交方式
//     curl_setopt($ch,CURLOPT_POST, true);
//     curl_setopt($ch,CURLOPT_POSTFIELDS,$xml);
//     $data = curl_exec($ch);
    
//     //返回结果
//     if($data){
//         curl_close($ch);
//         return $this->xmlToArray($data);
//     }
//     else {
//         $error = curl_errno($ch);
//         echo "curl出错，错误码:$error"."<br>"; 
//         curl_close($ch);
//         return false;
//     }
// }

//企业向个人付款
// function payToUser($params, $key, $apicent_cert, $apiclient_key , $appid , $mchid) {
//     $url = 'https://api.mch.weixin.qq.com/mmpaymkttransfers/promotion/transfers';
    
//     //检测必填参数
//     if($params["partner_trade_no"] == null) {   //
//         exit("退款申请接口中，缺少必填参数partner_trade_no！"."<br>");
//     }elseif($params["openid"] == null){
//         exit("退款申请接口中，缺少必填参数openid！"."<br>");
//     }elseif($params["check_name"] == null){             //NO_CHECK：不校验真实姓名 FORCE_CHECK：强校验真实姓名（未实名认证的用户会校验失败，无法转账）OPTION_CHECK：针对已实名认证的用户才校验真实姓名（未实名认证用户不校验，可以转账成功）
//         exit("退款申请接口中，缺少必填参数check_name！"."<br>");
//     }elseif(($params["check_name"] == 'FORCE_CHECK' or $params["check_name"] == 'OPTION_CHECK') && ($params["re_user_name"] == null)){  //收款用户真实姓名。
//         exit("退款申请接口中，缺少必填参数re_user_name！"."<br>");
//     }elseif($params["amount"] == null){
//         exit("退款申请接口中，缺少必填参数amount！"."<br>");
//     }elseif($params["desc"] == null){
//         exit("退款申请接口中，缺少必填参数desc！"."<br>");
//     }
    
//     $params["mch_appid"] = $appid;//公众账号ID
//     $params["mchid"] = $mchid;//商户号
//     $params["nonce_str"] = 'lm'.rand(100000, 999999);//随机字符串
//     $params['spbill_create_ip'] = $_SERVER['REMOTE_ADDR'] == '::1' ? '192.127.1.1' : $_SERVER['REMOTE_ADDR'];//获取IP
//     $params["sign"] = getSign($params, $key);//签名
//     $xml = arrayToXml($params);
    
//     return postXmlSSLCurl($xml, $url, false, $apicent_cert, $apiclient_key);
// }






