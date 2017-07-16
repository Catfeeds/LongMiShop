<?php


/**
 * 订单进度条
 * @param $orderInfo
 * @return array
 */
function getOrderProgressBar($orderInfo){
    $orderStatus    = $orderInfo['order_status'];
    $shippingStatus = $orderInfo['shipping_status'];
    $payStatus      = $orderInfo['pay_status'];
    $parameter = array(
        "speed" => 0 ,
        "first" => array(
            "show" => 1,
            "on"   => 0,
            "done" => 0,
            "date" => "",
            "time" => "",
            "name" => "下单",
        ),
        "second" => array(
            "show" => 1,
            "on"   => 0,
            "done" => 0,
            "date" => "",
            "time" => "",
            "name" => "付款",
        ),
        "third" => array(
            "show" => 1,
            "on"   => 0,
            "done" => 0,
            "date" => "",
            "time" => "",
            "name" => "已出库",
        ),
        "fourth" => array(
            "show" => 1,
            "on"   => 0,
            "done" => 0,
            "date" => "",
            "time" => "",
            "name" => "交易完成",
        ),
    );

    if( $orderStatus == 0 && $payStatus == 0){  //订单查询状态 待支付
        $parameter['speed'] = 0;
        $parameter['first']['on'] = 1;
        $parameter['first']['done'] = 1;
        $parameter['first']['date'] = date('Y-m-d' , $orderInfo['add_time']);
        $parameter['first']['time'] = date('H:i:s' , $orderInfo['add_time']);
    }elseif( $orderInfo['pay_code'] == 'cod' && ($orderStatus == 1 || $orderStatus ==0) && $shippingStatus == 0 ){ //订单查询状态 待发货
        $parameter['speed'] = 33;
        $parameter['first']['on'] = 0;
        $parameter['first']['done'] = 1;
        $parameter['first']['date'] = date('Y-m-d' , $orderInfo['add_time']);
        $parameter['first']['time'] = date('H:i:s' , $orderInfo['add_time']);
        $parameter['second']['on'] = 1;
        $parameter['second']['done'] = 1;
        $parameter['second']['date'] = date('Y-m-d' , $orderInfo['pay_time']);
        $parameter['second']['time'] = date('H:i:s' , $orderInfo['pay_time']);
    }elseif(($orderStatus == 0 ||$orderStatus == 1) && $payStatus == 1 && $shippingStatus != 1){ //订单查询状态 待发货
        $parameter['speed'] = 33;
        $parameter['first']['on'] = 0;
        $parameter['first']['done'] = 1;
        $parameter['first']['date'] = date('Y-m-d' , $orderInfo['add_time']);
        $parameter['first']['time'] = date('H:i:s' , $orderInfo['add_time']);
        $parameter['second']['on'] = 1;
        $parameter['second']['done'] = 1;
        $parameter['second']['date'] = date('Y-m-d' , $orderInfo['pay_time']);
        $parameter['second']['time'] = date('H:i:s' , $orderInfo['pay_time']);
    }elseif( $orderStatus == 1 && $shippingStatus == 1){  //订单查询状态 待收货
        $parameter['speed'] = 67;
        $parameter['first']['on'] = 0;
        $parameter['first']['done'] = 1;
        $parameter['first']['date'] = date('Y-m-d' , $orderInfo['add_time']);
        $parameter['first']['time'] = date('H:i:s' , $orderInfo['add_time']);
        $parameter['second']['on'] = 0;
        $parameter['second']['done'] = 1;
        $parameter['second']['date'] = date('Y-m-d' , $orderInfo['pay_time']);
        $parameter['second']['time'] = date('H:i:s' , $orderInfo['pay_time']);
        $parameter['third']['on'] = 1;
        $parameter['third']['done'] = 1;
        $parameter['third']['date'] = date('Y-m-d' , $orderInfo['shipping_time']);
        $parameter['third']['time'] = date('H:i:s' , $orderInfo['shipping_time']);
    }elseif( $orderStatus == 2 || $orderStatus == 4 ){  // 待评价 确认收货
        $parameter['speed'] = 100;
        $parameter['first']['on'] = 0;
        $parameter['first']['done'] = 1;
        $parameter['first']['date'] = date('Y-m-d' , $orderInfo['add_time']);
        $parameter['first']['time'] = date('H:i:s' , $orderInfo['add_time']);
        $parameter['second']['on'] = 0;
        $parameter['second']['done'] = 1;
        $parameter['second']['date'] = date('Y-m-d' , $orderInfo['pay_time']);
        $parameter['second']['time'] = date('H:i:s' , $orderInfo['pay_time']);
        $parameter['third']['on'] = 0;
        $parameter['third']['done'] = 1;
        $parameter['third']['date'] = date('Y-m-d' , $orderInfo['shipping_time']);
        $parameter['third']['time'] = date('H:i:s' , $orderInfo['shipping_time']);
        $parameter['fourth']['on'] = 1;
        $parameter['fourth']['done'] = 1;
        $parameter['fourth']['date'] = date('Y-m-d' , $orderInfo['confirm_time']);
        $parameter['fourth']['time'] = date('H:i:s' , $orderInfo['confirm_time']);
    }elseif( $orderStatus == 3 ){  // 已取消
        $parameter['speed'] = 100;
        $parameter['first']['on'] = 0;
        $parameter['first']['done'] = 1;
        $parameter['first']['date'] = date('Y-m-d' , $orderInfo['add_time']);
        $parameter['first']['time'] = date('H:i:s' , $orderInfo['add_time']);
        $parameter['second']['show'] = 0;
        $parameter['third']['show'] = 0;
        $parameter['fourth']['on'] = 1;
        $parameter['fourth']['done'] = 1;
        $parameter['fourth']['name'] = "订单关闭";
        $parameter['fourth']['date'] = date('Y-m-d' , $orderInfo['confirm_time']);
        $parameter['fourth']['time'] = date('H:i:s' , $orderInfo['confirm_time']);
    }
    return $parameter;
}


/**
 * 获取物流信息
 * @param $orderId
 * @return array
 */
function getExpress($orderId){
    if( is_null($orderId) ){
        return callback(false,'没有获取到订单信息');
    }
    $delivery = M('delivery_doc') -> where("order_id='$orderId'")->limit(1)->find();
    if($delivery['shipping_name'] && $delivery['invoice_no']){
        return queryExpress($delivery['shipping_name'],$delivery['invoice_no']);
    }
    return callback(false,'没获取到物流信息');
}

/**
 * 给订单数组添加属性  包括按钮显示属性 和 订单状态显示属性
 * @param $order
 * @param null $engName
 * @return array
 */
function setBtnOrderStatus($order,$engName = null)
{
    if ( is_null($engName) ){
        $order_status_arr = C('ORDER_STATUS_DESC');
    }else{
        $order_status_arr = C('ORDER_STATUS_DESC_'.$engName);
    }
    $order['order_status_code'] = $order_status_code = orderStatusDesc(0, $order , $engName); // 订单状态显示给用户看的
    $order['order_status_desc'] = $order_status_arr[$order_status_code];
    $orderBtnArr = orderBtn(0, $order);
    return array_merge($order,$orderBtnArr); // 订单该显示的按钮
}
/**
 * 给订单数组添加属性  包括按钮显示属性 和 订单状态显示属性
 * @param $order
 * @return array
 */
function set_btn_order_status($order)
{
    $order_status_arr = C('ORDER_STATUS_DESC');
    $order['order_status_code'] = $order_status_code = orderStatusDesc(0, $order); // 订单状态显示给用户看的
    $order['order_status_desc'] = $order_status_arr[$order_status_code];
    $orderBtnArr = orderBtn(0, $order);
    return array_merge($order,$orderBtnArr); // 订单该显示的按钮
}


/**
 * 获取订单状态的 中文描述名称
 * @param int $order_id
 * @param array $order 订单数组
 * @param null $engName
 * @return string
 */
function orderStatusDesc($order_id = 0, $order = array() ,$engName = null)
{
    if(empty($order)){
        $order = M('Order') -> where("order_id = '$order_id'")->find();
    }
    // 货到付款
    if($order['pay_code'] == 'cod')
    {
        if(in_array($order['order_status'],array(0,1)) && $order['shipping_status'] == 0)
            return 'WAITSEND'; //'待发货',
    }
    else // 非货到付款
    {
        if($order['pay_status'] == 0 && $order['order_status'] == 0)
            return 'WAITPAY'; //'待支付',
        if($order['pay_status'] == 1 &&  in_array($order['order_status'],array(0,1)) ){
            if( $order['shipping_status'] == 0 )
                return 'WAITSEND'; //'待发货',
            if(($order['shipping_status'] == 2)){
                if($engName == "MOBILE"){
                    return 'PARTIALSEND'; //'部分发货',
                }else{
                    return 'PARTIALSEND'; //'部分发货',
                }
            }

        }
    }
    if(($order['shipping_status'] == 1) && ($order['order_status'] == 1))
        return 'WAITRECEIVE'; //'待收货',
    if($order['order_status'] == 2)
        return 'FINISH'; //'已完成',
//        return 'WAITCCOMMENT'; //'待评价',
    if($order['order_status'] == 3)
        return 'CANCEL'; //'已取消',
    if($order['order_status'] == 4)
        return 'FINISH'; //'已完成',
    return 'OTHER';
}



/**
 * 获取订单状态的 显示按钮
 * @param int $order_id
 * @param array $order
 * @return array
 */
function orderBtn($order_id = 0, $order = array())
{
    if(empty($order))
        $order = M('Order') -> where("order_id = $order_id")->find();
    /**
     *  订单用户端显示按钮
    去支付     AND pay_status=0 AND order_status=0 AND pay_code ! ="cod"
    取消按钮  AND pay_status=0 AND shipping_status=0 AND order_status=0
    确认收货  AND shipping_status=1 AND order_status=0
    评价      AND order_status=1
    查看物流  if(!empty(物流单号))
     */
    $btn_arr = array(
        'pay_btn' => 0, // 去支付按钮
        'cancel_btn' => 0, // 取消按钮
        'receive_btn' => 0, // 确认收货
        'comment_btn' => 0, // 评价按钮
        'shipping_btn' => 0, // 查看物流
        'return_btn' => 0, // 退货按钮 (联系客服)
    );

    // 货到付款
    if($order['pay_code'] == 'cod')
    {
        if(($order['order_status']==0 || $order['order_status']==1) && $order['shipping_status'] == 0) // 待发货
        {
            $btn_arr['cancel_btn'] = 1; // 取消按钮 (联系客服)
        }
        if($order['shipping_status'] == 1 && $order['order_status'] == 1) //待收货
        {
            $btn_arr['receive_btn'] = 1;  // 确认收货
        }
    }
    // 非货到付款
    else
    {
        if($order['pay_status'] == 0 && $order['order_status'] == 0) // 待支付
        {
            $btn_arr['pay_btn'] = 1; // 去支付按钮
            $btn_arr['cancel_btn'] = 1; // 取消按钮
        }
        if($order['pay_status'] == 1 && in_array($order['order_status'],array(0,1)) && $order['shipping_status'] == 0) // 待发货
        {
            $btn_arr['return_btn'] = 1; // 退货按钮 (联系客服)
        }
        if($order['pay_status'] == 1 && $order['order_status'] == 1  && $order['shipping_status'] == 1) //待收货
        {
            $btn_arr['receive_btn'] = 1;  // 确认收货
            $btn_arr['return_btn'] = 1; // 退货按钮 (联系客服)
        }
    }
    if($order['order_status'] == 2)
    {
        $btn_arr['comment_btn'] = 1;  // 评价按钮
        $btn_arr['return_btn'] = 1; // 退货按钮 (联系客服)
    }
    if($order['shipping_status'] == 1)
    {
        $btn_arr['shipping_btn'] = 1; // 查看物流
    }
    if($order['shipping_status'] == 2)
    {
        $btn_arr['shipping_btn'] = 2; // 部分发货
    }
    if($order['shipping_status'] == 2 && $order['order_status'] == 1) // 部分发货
    {
        $btn_arr['return_btn'] = 1; // 退货按钮 (联系客服)
    }

    if( ( $order['order_status'] == 2 || $order['order_status'] == 4 ) && $order['confirm_time'] + (60*60*24*7) < time() ){
        $btn_arr['return_btn'] = 0; // 退货按钮 (联系客服)
    }
    return $btn_arr;
}


/**
 * 获取过期的订单
 * @return array
 */
function getOverdueOrder(){
    $orderList = M('Order') -> where(' pay_status=0 AND order_status=0 AND pay_code !="cod" ')->select();
    return $orderList;
}



/**
 * 支付完成修改订单
 * @param $order_sn 订单号
 * @param int $pay_status  默认1 为已支付
 * @return bool
 */
function update_pay_status($order_sn,$pay_status = 1)
{
    // 如果这笔订单已经处理过了
    $count = M('order') -> where("order_sn = '$order_sn' and pay_status = 0")->count();   // 看看有没已经处理过这笔订单  支付宝返回不重复处理操作
    if($count == 0) return false;
    // 找出对应的订单
    $order = M('order') -> where("order_sn = '$order_sn'")->find();
    // 修改支付状态  已支付
    M('order') -> where("order_sn = '$order_sn'")->save(array('pay_status'=>1,'pay_time'=>time()));
    // 减少对应商品的库存
//    minus_stock($order['order_id']);
    // 给他升级, 根据order表查看消费记录 给他会员等级升级 修改他的折扣 和 总金额
//    update_user_level($order['user_id']);
    // 记录订单操作日志
    logOrder($order['order_id'],'订单付款成功','付款成功',$order['user_id']);

    giveInviteGift( $order['user_id'] );

    $orderLogic = new \Admin\Logic\OrderLogic();

    $orderLogic -> orderProcessHandle( $order['order_id'] , "confirm" );

    commoditySalesVolume($order['order_id']);

    sendWeChatMessageUseUserId( $order['user_id'] , "支付" , array("orderId" => $order['order_id']) );
    return true;
    //分销设置
//    M('rebate_log') -> where("order_id = {$order['order_id']}")->save(array('status'=>1));
    // 成为分销商条件
//    $distribut_condition = tpCache('distribut.condition');
//    if($distribut_condition == 1)  // 购买商品付款才可以成为分销商
//        M('users') -> where("user_id = {$order['user_id']}")->save(array('is_distribut'=>1));
}


/**
 * 获取退货情况
 * @param $orderInfo
 * @param $userId
 * @return array
 */
function setOrderReturnState( $orderInfo , $userId ){
    $goodsList = $orderInfo['goods_list'];
    if( empty( $goodsList ) ){
        return $orderInfo;
    }
    $goodsCount  = count( $goodsList );
    $returnCount = 0;
    $isRetreats  = 0;
    foreach ( $goodsList as $key => $goodsItem ){
        $where = array();
        $where['order_id']  = $goodsItem['order_id'];
        $where['user_id']   = $userId;
        $where['goods_id']  = $goodsItem['goods_id'];
        $where['spec_key']  = $goodsItem['spec_key'];
        $where['result']  = "0";
        $goodsList[$key]['isReturn'] = $count = M('return_goods') -> where($where)->count();
        $where['result']  = "1";
        $goodsList[$key]['isReturnPass']  = M('return_goods') -> where($where)->count();

        if( $count > 0){
            $returnCount ++;
        }
        if($goodsList[$key]['isReturnPass'] !=0 || $goodsList[$key]['isReturn'] != 0 ){
            $isRetreats++;
        }
    }
    if( $returnCount != 0 ){
        $orderInfo['isReturn'] = true;
    }
    if( $isRetreats == $goodsCount ){
        $orderInfo['isRetreats'] = true;
    }
    $orderInfo['goods_list'] = $goodsList;
    return $orderInfo;
}


/**
 * 根据 order_goods 表扣除商品库存
 * @param type $order_id  订单id
 * @param 订单取消 $goods_num
 */
function minus_stock($order_id,$goods_num = null ){
    $orderGoodsArr = M('OrderGoods') -> where("order_id = $order_id")->select();
    foreach($orderGoodsArr as $key => $val)
    {
        // 有选择规格的商品
        if(!empty($val['spec_key']))
        {   // 先到规格表里面扣除数量 再重新刷新一个 这件商品的总数量
            if(!empty($goods_num)){
                M('SpecGoodsPrice') -> where("goods_id = {$val['goods_id']} and `key` = '{$val['spec_key']}'")->setInc('store_count',$val['goods_num']);
            }else{
                M('SpecGoodsPrice') -> where("goods_id = {$val['goods_id']} and `key` = '{$val['spec_key']}'")->setDec('store_count',$val['goods_num']);
            }

            refresh_stock($val['goods_id']);
            //更新活动商品购买量
            if($val['prom_type']==1 || $val['prom_type']==2){
                $prom = get_goods_promotion($val['goods_id']);
                if($prom['is_end']==0){
                    $tb = $val['prom_type']==1 ? 'flash_sale' : 'group_buy';
                    M($tb) -> where("id=".$val['prom_id'])->setInc('buy_num',$val['goods_num']);
                    M($tb) -> where("id=".$val['prom_id'])->setInc('order_num');
                }
            }
        }else{
            if(!empty($goods_num)){
                M('Goods') -> where("goods_id = {$val['goods_id']}")->setInc('store_count',$val['goods_num']); // 直接增加商品总数量
            }else{
                M('Goods') -> where("goods_id = {$val['goods_id']}")->setDec('store_count',$val['goods_num']); // 直接扣除商品总数量
            }

        }
    }
}


/**
 * 是否在创建订单状态
 * @param $key
 * @return bool
 */
function isInCreateOrder( $key ){
    if( $key == "inCreateOrder"){
        return true;
    }
    return false;
}
/**
 * 是否在创建兑换订单状态
 * @param $key
 * @return bool
 */
function isInCreateExchangeOrder( $key ){
    if( $key == "inCreateExchangeOrder"){
        return true;
    }
    return false;
}

/**
 * 设置订单支付类型
 * @param $orderId
 * @param $code
 * @param $name
 * @return array
 */
function setOrderPayCode( $orderId , $code , $name ){
    $where = array();
    $saveData = array();
    $where['order_id'] = $orderId;
    $saveData['pay_code'] = $code;
    $saveData['pay_name'] = $name;
    $result = M('order') -> where($where)->save($saveData);
    if ($result > 0 || $result === 0) {
        return callback( true );
    }
    return callback( false , "修改失败" );
}

/**
 * 退款退货处理
 * @param $returnOrderInfo
 * @param $postData
 * @return array
 */
function returnOrderHandle( $returnOrderInfo , $postData )
{
    $id = $returnOrderInfo['id'];
    $data['refund_money'] = $postData['refund_money'];
    $data['remark'] = $postData['remark'];
    $data['result'] = 2;
    $condition = array("id" => $id);
//    $typeMsg = array('退换','换货');
    $statusMsg = array('未处理', '处理中', '已完成');

    $model = new \Think\Model();
    try {
        $model->startTrans();

        if (!empty($data['refund_money'])) { //退款
            $data['result'] = 1;
            $desc = '售后退款';
            $userId = $returnOrderInfo['user_id'];
            $moneyRes = accountLog($userId, $data['refund_money'], 0, $desc);
            if (!$moneyRes) {
                throw new \Exception('退款失败！');
            }
            $type = 3;
            $where = array(
                "order_id" => $returnOrderInfo['order_id'],
                "goods_id" => $returnOrderInfo['goods_id'],
                "spec_key" => $returnOrderInfo['spec_key'],
            );
            $orderGoods = M('order_goods') -> where($where)->save(array('is_send' => $type));
            if ($orderGoods == false) {
                throw new \Exception('订单商品状态修改失败！');
            }
            closeOrderCheck( $returnOrderInfo['order_id'] );
            deliveryOrderCheck( $returnOrderInfo['order_id'] );


        }

        $data['status'] = 2;
        if ( M('return_goods') -> where($condition) -> save($data) == false ) {
            throw new \Exception('操作失败！');
        }


        $orderLogic = new \Admin\Logic\OrderLogic();
        $note = "处理退换货, 状态:{$statusMsg[$data['status']]},处理备注：{$data['remark']}";
        if (!$orderLogic->orderActionLog($returnOrderInfo['order_id'], 'refund', $note)) {
            throw new \Exception('插入日志失败！');
        }
//            throw new \Exception('我是断点！');
        $model -> commit();

        return callback(true, '处理成功');

    } catch (\Exception $e) {
        $model -> rollback();

        return callback(false, $e->getMessage());
    }
}

/**
 * 拆分admin-list 字段
 * @param string $adminListString
 * @return array
 */
function explodeAdminList( $adminListString = "[0]" ){
    $orderAdminListArray  = explode( "][" , $adminListString );
    $orderAdminListString = implode( "%" , $orderAdminListArray );
    $orderAdminListString = preg_replace( '/\[|\]/' , '' , $orderAdminListString );
    return explode( "%" , $orderAdminListString );
}

/**
 * 是否拥有快速发货按钮
 * @param $adminList
 * @param $adminId
 * @return bool
 */
function getFastDeliveryBool( $adminList , $adminId ){
    $adminArray = explodeAdminList( $adminList );
    if(
//        count( $adminArray ) == 1 &&
//        ( is_supplier() && $adminArray[0] == $adminId ) ||
//        ( !is_supplier() &&  $adminArray[0] == 0  )
        ( is_supplier() && in_array( $adminId , $adminArray ) ) ||
        ( !is_supplier() && in_array( 0 , $adminArray )  )

    ){
        return true;
    }
    return false;

}



function batchDelivery( $deliveryList = null ){

    if( !is_null( $deliveryList ) ) {

        $errorMsgList = array();
        $orderLogic = new \Admin\Logic\OrderLogic();
        $expressList = include_once 'Application/Common/Conf/express.php'; //快递名称


        foreach ($deliveryList as $deliveryItem) {
            $order_sn = $deliveryItem['A'];
            $shipping_name = $deliveryItem['B'];
            $invoice_no = $deliveryItem['C'];

            if ( ( $order_sn == "订单id" || $order_sn == "订单号" ) && $shipping_name == "物流公司" && $invoice_no == "物流单号") {
                continue;
            }
            if (!in_array($shipping_name, $expressList)) {
                $errorMsgList[] = array(
                    "orderSn" => $order_sn,
                    "msg"     => "后台不支持【" . $shipping_name . "】这个快递",
                );
                continue;
            }
            $condition = array();
            $condition["order_sn"] = $order_sn;
            if( is_supplier() ){
                $condition["admin_list"] = array("like","%[". session("admin_id") ."]%");
            }else{
                $condition["admin_list"] = array("like","%[0]%");
            }
            $orderInfo = findDataWithCondition("order", $condition, "order_id");
            if ( empty($orderInfo) ) {
                $errorMsgList[] = array(
                    "orderSn" => $order_sn,
                    "msg"     => "没有此订单信息",
                );
                continue;
            }
            $order_id = $orderInfo["order_id"];

            $condition = array();
            $condition["order_id"] = $order_id;
            $condition["is_send"] = "0";
            if( is_supplier() ){
                $condition["admin_id"] = session("admin_id");
            }else{
                $condition["admin_id"] = "0";
            }
            $goods = M("order_goods") -> where( $condition )->getField("rec_id", true);
            if( empty($goods) ){
                $errorMsgList[] = array(
                    "orderSn" => $order_sn,
                    "msg"     => "订单已经全部发货",
                );
                continue;
            }
            if( isExistenceDataWithCondition("return_goods",array("order_id"=>$order_id,"result"=>"0")) ){
                $errorMsgList[] = array(
                    "orderSn" => $order_sn,
                    "msg"     => "此订单有售后申请",
                );
                continue;
            }

            $data = array(
                "order_id"       => $order_id,
                "invoice_no"     => $invoice_no,
                "myShippingName" => $shipping_name,
                "goods"          => $goods,
                "note"           => " ",
            );
            $result = $orderLogic->deliveryHandle($data);
            if ( ! callbackIsTrue( $result ) ) {
                $errorMsgList[] = array(
                    "orderSn" => $order_sn,
                    "msg"     => getCallbackMessage( $result ),
                );
                continue;
            }
            $errorMsgList[] = array(
                "orderSn" => $order_sn,
                "msg"     => "发货成功!",
            );
            continue;

        }
        return callback( true , "批量发货成功"  , $errorMsgList );

    }
    return callback( false );
}





/**
 * 订单关闭检测
 * @param $orderId
 */
function closeOrderCheck( $orderId ){
    $condition1 =  array("order_id" => $orderId ) ;
    $condition2 =  array("order_id" => $orderId , "is_send" => array( "in" , "2,3" ) ) ;

    $orderGoodsNumber   = getCountWithCondition( 'order_goods' , $condition1 );
    $returnGoodsNumber  = getCountWithCondition( 'order_goods' , $condition2 );

    if( $returnGoodsNumber == $orderGoodsNumber ){
        M('order') -> where( $condition1 )->save( array( 'order_status' => 3 ) );
    }
}

/**
 * 订单待收货检测
 * @param $orderId
 */
function deliveryOrderCheck( $orderId ){
    $condition1 =  array( "order_id" => $orderId ) ;
    $condition2 =  array( "order_id" => $orderId , "is_send" => "0" ) ;
    $notSendGoodsNumber   = getCountWithCondition( 'order_goods' , $condition2 );
    if( $notSendGoodsNumber == 0 ){
        $update['shipping_time'] = time();
        $update['shipping_status'] = 1;
        M('order') -> where( $condition1 ) -> save( $update );
    }
}