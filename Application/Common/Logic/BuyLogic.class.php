<?php
namespace Common\Logic;

use Common\Logic\Base\BaseLogic;
use Think\Model;

class BuyLogic extends BaseLogic
{

    private         $cartLogic               = null;

    public function __construct()
    {
        parent::__construct("config");
        $this -> cartLogic = new \Common\Logic\CartLogic();
        $this -> userId = session(__UserID__);
        $this -> model = new Model();
    }



    /**
     * 插件的兑换订单生成
     * @param $addonsName
     * @return array
     */
    public function createAddonsOrder($addonsName)
    {
        try {
            $this -> status = "inCreateAddonsOrder";

            $this -> model  -> startTrans();

            //第1步 验证数据初始化
            $this -> _createAddonsOrderStep1();

            //第2步  得到购买商品信息
            $this -> _createAddonsOrderStep2();

            //第3步 生成订单
            $this -> _createAddonsOrderStep3();

            //第4步 订单后续处理
            $this -> _createAddonsOrderStep4();

//            throw new \Exception('我是断点！');
            $this -> model  -> commit();

            return callback(true,'领取成功',$this -> _post_data['order_id']);

        } catch (\Exception $e){
            $this -> model  -> rollback();

            return callback(false, $e->getMessage());
        }
    }

    /**
     * 兑换订单生成
     * @return array
     */
    public function createExchangeOrder()
    {
        try {
            $this -> status = "inCreateExchangeOrder";

            $this -> model  -> startTrans();

            //第1步 验证数据初始化
            $this -> _createExchangeOrderStep1();

            //第2步  得到购买商品信息
            $this -> _createExchangeOrderStep2();

            //第3步 生成订单
            $this -> _createExchangeOrderStep3();

            //第4步 订单后续处理
            $this -> _createExchangeOrderStep4();

//            throw new \Exception('我是断点！');
            $this -> model  -> commit();

            return callback(true,'兑换成功');

        } catch (\Exception $e){
            $this -> model  -> rollback();

            return callback(false, $e->getMessage());
        }
    }

    /**
     * 订单生成
     * @return array
     */
    public function createOrder()
    {

        try {
            $this -> status = "inCreateOrder";

            $this -> model -> startTrans();

            //第1步 验证数据初始化
            $this -> _createOrderStep1();

            //第2步 得到购买商品信息
            $this -> _createOrderStep2();

            //第3步 得到购买相关金额计算等信息
            $this -> _createOrderStep3();

            //第4步 生成订单
            $this -> _createOrderStep4();

            //第5步 订单后续处理
            $this -> _createOrderStep5();

//            throw new \Exception('我是断点！');
            $this -> model -> commit();

            return callback(true,'',$this -> _post_data['orderData']['order_id']);

        } catch (\Exception $e){
            $this -> model -> rollback();

            return callback(false, $e->getMessage());
        }

    }


    /**
     * 退货退款单生成
     * @return array
     */
    public function createServiceOrder()
    {

        try {
            $this -> model -> startTrans();

            //第1步 验证数据初始化
            $this -> _createServiceOrderStep1();

            //第2步 生成退货退款单单
            $this -> _createServiceOrderStep2();

            $this -> model -> commit();

            return callback(true,'申请成功,客服第一时间会帮你处理!');

        } catch (\Exception $e){
            $this -> model -> rollback();

            return callback(false, $e->getMessage());
        }
    }







    /***
     * 以下是步骤
     */

    //插件兑换订单生成第1步 验证数据初始化
    private function _createAddonsOrderStep1(){

        $this -> _post_data['address_id']   = $this -> _post_data['addressId'];
        if( is_null($this -> userId) ){
            throw new \Exception('登录超时请重新登录');
        }
        $this -> user = findDataWithCondition('users',array("user_id" => $this->userId));
        if( empty($this -> user) ){
            throw new \Exception('用户信息出错！');
        }
        $this -> _post_data['order_id'] = $this -> _post_data['orderId'];
        $result = findDataWithCondition("addons_christmas_order", array("id" => $this -> _post_data['order_id']));
        if( empty($result)){
            throw new \Exception( "未找到该礼包" );
        }
        if( $result["user_id"] == $this->userId ){
            throw new \Exception( "不能领取自己的礼包" );
        }
        if( $result["status"] == 0 ){
            throw new \Exception( "礼包还未付款" );
        }
        if( $result["status"] == 2 ){
            if( $result["get_user_id"] == $this->userId){
                throw new \Exception( "礼物已被您领取过" );
            }else{
                throw new \Exception( "礼物已被他人领取" );
            }
        }
    }

    //插件兑换订单生成第2步 得到购买商品信息
    private function _createAddonsOrderStep2(){
        $goodsList = selectDataWithCondition("addons_christmas_order_goods", array("order_id" => $this -> _post_data['order_id']));
        if( !empty($goodsList) ){
            foreach ( $goodsList as $key => $goodsItem ){
                $goodsInfo = findDataWithCondition( "goods" , array( 'goods_id' => $goodsItem['goods_id'] ) ) ;
                if( empty($goodsInfo) ){
                    unset( $goodsList[$key] );
                }
                $goodsList[$key]['market_price'] = $goodsInfo['market_price'];
                $goodsList[$key]['goods_price'] = $goodsInfo['shop_price'];
                $goodsList[$key]['member_goods_price'] = $goodsInfo['shop_price'];
            }
        }

        $this -> _post_data['orderGoods'] = $goodsList;
        if( empty($this -> _post_data['orderGoods']) ){
            throw new \Exception('商品获取失败');
        }
    }

    //插件兑换订单生成第3步 生成订单
    private function _createAddonsOrderStep3(){
        //第1步 验证数据初始化
        $this -> _createOrderStep1();

        //第2步 得到购买商品信息
        $this -> _createOrderStep2();

        //第3步 得到购买相关金额计算等信息
        $this -> _createOrderStep3();

        //第4步 生成订单
        $this -> _createOrderStep4();

        //第5步 订单后续处理
        $this -> _createOrderStep5();
    }

    //插件兑换订单生成第4步 订单后续处理
    private function _createAddonsOrderStep4(){
        $orderSn = $this -> _post_data['orderData']['order_sn'];
        if(! update_pay_status( $orderSn ) ){
            throw new \Exception('修改订单支付状态失败');
        }
        $result = setOrderPayCode( $this -> _post_data['orderData']['order_id'] , "addons_christmas" , "圣诞礼包" );
        if( !callbackIsTrue($result)){
            throw new \Exception( getCallbackMessage($result) );
        }
        $save = array(
            "order_id"=>$this -> _post_data['orderData']['order_id']
        );

        if( saveData( "addons_christmas_order_get_list", array("c_order_id" => $this -> _post_data['order_id'],"get_user_id"=>$this ->userId),$save) == false){
            throw new \Exception( "礼包状态修改失败" );
        }
    }


    //商品兑换订单生成第1步 验证数据初始化
    private function _createExchangeOrderStep1(){

        $this -> _post_data['address_id']   = $this -> _post_data['addressId'];
        $result = checkCode( $this -> _post_data['code'] );
        if( !callbackIsTrue($result)){
            throw new \Exception( getCallbackMessage($result) );
        }
        if( is_null($this -> userId) ){
            throw new \Exception('登录超时请重新登录');
        }
        $this -> user = findDataWithCondition('users',array("user_id" => $this->userId));
        if( empty($this -> user) ){
            throw new \Exception('用户信息出错！');
        }
    }

    //商品兑换订单生成第2步 得到购买商品信息
    private function _createExchangeOrderStep2(){
        $this -> _post_data['orderGoods'] = getExchangeGoodsList( $this -> _post_data['code'] );
        if( empty($this -> _post_data['orderGoods']) ){
            throw new \Exception('商品获取失败');
        }
    }

    //商品兑换订单生成第3步 生成订单
    private function _createExchangeOrderStep3(){
        //第1步 验证数据初始化
        $this -> _createOrderStep1();

        //第2步 得到购买商品信息
        $this -> _createOrderStep2();

        //第3步 得到购买相关金额计算等信息
        $this -> _createOrderStep3();

        //第4步 生成订单
        $this -> _createOrderStep4();

        //第5步 订单后续处理
        $this -> _createOrderStep5();
    }

    //商品兑换订单生成第4步 订单后续处理
    private function _createExchangeOrderStep4(){
        $orderSn = $this -> _post_data['orderData']['order_sn'];
        if(! update_pay_status( $orderSn ) ){
            throw new \Exception('修改订单支付状态失败');
        }
        $result = setOrderPayCode( $this -> _post_data['orderData']['order_id'] , "GCode" , "G码兑换" );
        if( !callbackIsTrue($result)){
            throw new \Exception( getCallbackMessage($result) );
        }
        $result = changeCodeState( $this -> _post_data['code'] , $this->userId , $this -> _post_data['orderData']['order_id'] );
        if( !callbackIsTrue($result)){
            throw new \Exception( getCallbackMessage($result) );
        }
    }



    //退货退款单生成第1步 验证数据初始化
    private function _createServiceOrderStep1(){
        if( is_null($this -> userId) ){
            throw new \Exception('登录超时请重新登录');
        }
        $this -> user =  M('users') -> where("user_id = '{$this->userId}'")->find();
        if( empty($this -> user) ){
            throw new \Exception('用户信息出错！');
        }
        $this -> _post_data["orderId"]      = $order_id = I('order_id',0);
        $this -> _post_data["orderSn"]      = $order_sn = I('order_sn',0);
        $this -> _post_data["goodsId"]      = $goods_id = I('goods_id',0);
        $this -> _post_data["speckey"]      = $spec_key = I('spec_key');
        $this -> _post_data["type"]         = I('type') == "REFUND0" ? 0 : 1;
        $this -> _post_data["refundWay"]    = I('returnWay') != "YUAN_LU" ? 1 : 0;
        $this -> _post_data["refundMoney"]  = I('refundMoney')  ? I('refundMoney') : 0;
        $goodsInfo = findDataWithCondition("goods",array("goods_id"=>$goods_id),"admin_id");
        if( empty($goodsInfo) ){
            throw new \Exception("商品不存在！");
        }
        $this -> _post_data["adminId"]     = $goodsInfo["admin_id"];
        if( $this -> _post_data["type"] != 1  && $this -> _post_data["refundMoney"] == 0 ){
            throw new \Exception("退款金额有误！");
        }

        if( empty($this -> _post_data["refundName"]) ){
            $msg =  !$this -> _post_data["type"]  ? "快递公司必填" : "联系人称呼必填";
            throw new \Exception( $msg );
        }
        if( empty($this -> _post_data["refundCode"]) ){
            $msg =  !$this -> _post_data["type"]  ? "快递单号必填" : "联系人电话必填";
            throw new \Exception( $msg );
        }
        $condition = array(
            "order_id"  => $order_id,
            "order_sn"  => $order_sn,
        );
        if( !isExistenceDataWithCondition( 'order' , $condition ) ){
            throw new \Exception('订单不存在！');
        }
        $condition = array(
            "order_id"  => $order_id,
            "order_sn"  => $order_sn,
            "goods_id"  => $goods_id,
            "status"    => array("in" ,"0 , 1"),
            "spec_key"  => $spec_key,
        );
        if( isExistenceDataWithCondition( 'return_goods' , $condition ) ){
            throw new \Exception('已经提交过退货退款申请！');
        }
    }

    //退货退款单生成第2步 生成订单
    private function _createServiceOrderStep2(){
        $data = array(
            "order_id"      => $this -> _post_data["orderId"],
            "order_sn"      => $this -> _post_data["orderSn"],
            "goods_id"      => $this -> _post_data["goodsId"],
            "addtime"       => time(),
            "user_id"       => $this->userId,
            "type"          => $this -> _post_data["type"], // 服务类型  退货 或者 换货
            "reason"        => I('reason'),// 问题描述
            "imgs"          => I('imgs'), // 用户拍照的相片
            "spec_key"      => $this -> _post_data["speckey"],
            "refund_name"   => $this -> _post_data["refundName"],
            "refund_code"   => $this -> _post_data["refundCode"],
            "refund_way"    => $this -> _post_data["refundWay"],
            "refund_money"  => $this -> _post_data["refundMoney"],
            "admin_id"      => $this -> _post_data["adminId"],
        );
        if( !isSuccessToAddData( "return_goods",$data) ){
            throw new \Exception('提交失败！');
        }
    }



    //订单生成第1步 验证数据初始化
    private function _createOrderStep1(){

        $address_id = $this -> _post_data['address_id'];
        $userCouponId = $this -> _post_data['coupon'];
        if( is_null($this -> userId) ){
            throw new \Exception('登录超时请重新登录');
        }
        $this -> user =  M('users') -> where("user_id = '{$this->userId}'")->find();
        if( empty($this -> user) ){
            throw new \Exception('用户信息出错！');
        }
        $this -> _post_data['address'] = M('UserAddress') -> where("address_id = '$address_id'")->find();
        if( empty($this -> _post_data['address'] ) ){
            throw new \Exception('收货人信息有误！');
        }

        //检查优惠券情况
        if( !empty( $userCouponId ) ){
            $userCouponInfo = findDataWithCondition( 'coupon_list' , array( "id" => $userCouponId ) );
            if( empty($userCouponInfo) ){
                throw new \Exception('您的优惠券信息有误！');
            }
            if( !empty($userCouponInfo['order_id']) ){
                throw new \Exception('优惠券已被使用！');
            }
            $couponInfo = findDataWithCondition( 'coupon' , array("id" => $userCouponInfo['cid'] ) );
            if( empty($couponInfo) ){
                throw new \Exception('优惠券信息有误！');
            }
            if( !empty( $couponInfo['use_type'] ) && $couponInfo['use_type'] == 1 ){
                if( $userCouponInfo['receive_time'] + $couponInfo['limit_day'] * 24 * 60 *60  < $this -> nowTime ){
                    throw new \Exception('此优惠券已过期！');
                }
            }else{
                if( $couponInfo['use_start_time'] > $this -> nowTime ){
                    throw new \Exception('此优惠券还未到使用时间！');
                }
                if( $couponInfo['use_end_time'] < $this -> nowTime  ){
                    throw new \Exception('此优惠券已过期！');
                }
            }
            $this -> _post_data['userCouponId'] = $userCouponId;
            $this -> _post_data['couponInfo'] = $couponInfo;
            $this -> _post_data['useCoupon']  = true;
        }

    }

    //订单生成第2步 得到购买商品信息
    private function _createOrderStep2(){
        if( empty($this -> _post_data['orderGoods']) ){
            $cart_count = $this -> cartLogic -> cart_count($this->userId,1);
            if( $cart_count == 0 ){
                throw new \Exception('你的购物车没有选中商品！');
            }

            $order_goods = M('cart') -> where("user_id = '{$this->userId}' and selected = 1")->select();
            if(empty($order_goods)){
                throw new \Exception('商品列表不能为空！');
            }

            $this -> _post_data['orderGoods'] = $order_goods;
        }
    }

    //订单生成第3步 得到购买相关金额计算等信息
    private function _createOrderStep3(){

        $order_goods = $this -> _post_data['orderGoods'];
        $goods_price = 0;
        $cut_fee = 0;
        $num= 0;
        $shipping_price = 0;
        $coupon_price = 0;
        $couponInfo_list =  $this -> _post_data['couponInfo'];

        $goods_id_arr = get_arr_column($order_goods,'goods_id');
        $goods_arr = M('goods') -> where("goods_id in(".  implode(',',$goods_id_arr).")")->getField('goods_id,weight,market_price,is_free_shipping'); // 商品id 和重量对应的键值对

        foreach($order_goods as $key => $val)
        {
            // 如果传递过来的商品列表没有定义会员价
            if(!array_key_exists('member_goods_price',$val))
            {
                $this -> user['discount'] = $this -> user['discount'] ? $this -> user['discount'] : 1; // 会员折扣 不能为 0
                $order_goods[$key]['member_goods_price'] = $val['member_goods_price'] = $val['goods_price'] * $this -> user['discount'];
            }
//            $goods_weight = 0;
            //如果商品不是包邮的

//            if($goods_arr[$val['goods_id']]['is_free_shipping'] == 0) {
//                $goods_weight += $goods_arr[$val['goods_id']]['weight'] * $val['goods_num']; //累积商品重量 每种商品的重量 * 数量
//            }
            $order_goods[$key]['goods_fee'] = $val['goods_num'] * $val['member_goods_price'];    // 小计
            $order_goods[$key]['store_count']  = getGoodNum($val['goods_id'],$val['spec_key']); // 最多可购买的库存数量
            $goods_price += $order_goods[$key]['goods_fee']; // 商品总价
            $cut_fee     += $val['goods_num'] * $val['market_price'] - $val['goods_num'] * $val['member_goods_price']; // 共节约
            $num        += $val['goods_num']; // 购买数量
        }


        $address = $this -> _post_data['address'];

        //计算邮费
        $region_list = get_region_list();
        $goods_data = array();
        foreach($order_goods as $key => $item){
                $goods_res = M('goods')->field('weight,delivery_way') -> where("goods_id = '".$item['goods_id']."'")->find();
                $goods_data[$key]['spec_key'] = $item['spec_key']; //商品规格
                $goods_data[$key]['goods_id'] = $item['goods_id']; //商品id
                $goods_data[$key]['goods_num'] = $item['goods_num']; //件数  重量
                $goods_data[$key]['goods_name'] = $item['goods_name']; //商品名称
                $goods_data[$key]['goods_price'] = $item['goods_price']; //商品价格
                $goods_data[$key]['weight'] = $goods_res['weight'];  //商品重量
                $goods_data[$key]['shipping_code'] = $goods_res['delivery_way']; //配送方式
                $goods_data[$key]['site'] = $region_list[$address['province']]['name']; //收获地址
        }
        $count_postage = count_postage($goods_data);
        $this -> _post_data['count_postage']  = $count_postage['data']['result'];
        $shipping_price = $count_postage['data']['count'];//运费


        $total_amount = $goods_price + $shipping_price;
        //优惠金额计算
        $result = cardDiscountAmountCalculation( $this -> _post_data['coupon'] , $this->userId , $total_amount , $goods_data);
        if( callbackIsTrue($result) ){
            $coupon_price = $result['data']['privilege'];
        }else{
            $coupon_price = 0;
        }


        $order_amount = $goods_price + $shipping_price - $coupon_price; // 应付金额 = 商品价格 + 物流费 - 优惠券
        $pay_points = 0;
        $user_money =0 ;
        //订单总价  应付金额  物流费  商品总价 节约金额 共多少件商品 积分  余额  优惠券
        $result = array(
            'total_amount'      => $total_amount, // 商品总价
            'order_amount'      => $order_amount, // 应付金额
            'shipping_price'    => $shipping_price, // 物流费
            'goods_price'       => $goods_price, // 商品总价
            'cut_fee'           => $cut_fee, // 共节约多少钱
            'anum'              => $num, // 商品总共数量
            'integral_money'    => $pay_points,  // 积分抵消金额
            'user_money'        => $user_money, // 使用余额
            'coupon_price'      => $coupon_price,// 优惠券抵消金额
            'order_goods'       => $order_goods, // 商品列表 多加几个字段原样返回
        );
        $order_prom = $this -> getOrderPromotion($result['order_amount']);

        $result['order_amount'] = $order_prom['order_amount'] ;
        $result['order_prom_id'] = $order_prom['order_prom_id'] ;
        $result['order_prom_amount'] = $order_prom['order_prom_amount'] ;

        $car_price = array(
            'postFee'      => $result['shipping_price'], // 物流费
            'couponFee'    => $result['coupon_price'], // 优惠券
            'balance'      => $result['user_money'], // 使用用户余额
            'pointsFee'    => $result['integral_money'], // 积分支付
            'payables'     => $result['order_amount'], // 应付金额
            'goodsFee'     => $result['goods_price'],// 商品价格
            'order_prom_id' => $result['order_prom_id'], // 订单优惠活动id
            'order_prom_amount' => $result['order_prom_amount'], // 订单优惠活动优惠了多少钱
        );
        $this -> _post_data['carPrice'] = $car_price;
    }

    //订单生成第4步 生成订单
    private function _createOrderStep4(){


        // 仿制灌水 1天只能下 50 单  // select * from `tp_order` where user_id = 1  and order_sn like '20151217%'
        $order_count = M('Order') -> where("user_id= '{$this->userId}' and order_sn like '" . date('Ymd') . "%'")->count();
        if ($order_count >= 50){
            throw new \Exception('一天只能下50个订单！');
        }
        // 0插入订单 order
        $user_id = $this-> userId;
        $address = $this -> _post_data['address'];
        $car_price = $this -> _post_data['carPrice'];

        //查询配送id
        $delivery_way = M('goods')->field('delivery_way') -> where("goods_id = '".$this -> _post_data['orderGoods'][0]['goods_id']."'")->find();
        //查询配送名字
        $logistics = M('logistics')->field('log_delivery') -> where("log_id = '".$delivery_way['delivery_way']."'")->find();
        !empty($logistics) ? $logistics   : $logistics = '';
        //判断发票类型
        $invoice_title = $this -> _post_data['invoice_title'] == 1 ?  '个人' : $this -> _post_data['invoice'];

        $data = array(
            'order_sn'         =>date('YmdHis').rand(1000,9999), // 订单编号
            'user_id'          =>$user_id, // 用户id
            'consignee'        =>$address['consignee'], // 收货人
            'province'         =>$address['province'],//'省份id',
            'city'             =>$address['city'],//'城市id',
            'district'         =>$address['district'],//'县',
            'twon'             =>$address['twon'],// '街道',
            'address'          =>$address['address'],//'详细地址',
            'mobile'           =>$address['mobile'],//'手机',
            'zipcode'          =>$address['zipcode'],//'邮编',
            'email'            =>$address['email'],//'邮箱',
//            'shipping_code'    =>$shipping_code,//'物流编号',
            'shipping_name'    =>$logistics['log_delivery'], //'物流名称',
            'invoice_title'    =>$invoice_title, //'发票抬头',
            'goods_price'      =>$car_price['goodsFee'],//'商品价格',
            'shipping_price'   =>$car_price['postFee'],//'物流价格',
            'user_money'       =>$car_price['balance'],//'使用余额',
            'coupon_price'     =>$car_price['couponFee'],//'使用优惠券',
//            'integral'         =>($car_price['pointsFee'] * tpCache('shopping.point_rate')), //'使用积分',
//            'integral_money'   =>$car_price['pointsFee'],//'使用积分抵多少钱',
            'total_amount'     =>($car_price['goodsFee'] + $car_price['postFee']),// 订单总额
            'order_amount'     =>$car_price['payables'],//'应付款金额',
            'add_time'         =>$this -> nowTime, // 下单时间
            'order_prom_id'    =>$car_price['order_prom_id'],//'订单优惠活动id',
            'order_prom_amount'=>$car_price['order_prom_amount'],//'订单优惠活动优惠了多少钱',
            'user_note'        =>$this -> _post_data['userMessage']." ", // 备注信息
        );

        $order_id = M("Order")->data($data)->add();
        if(!$order_id){
            throw new \Exception('添加订单失败！');
        }

        // 记录订单操作日志
        logOrder($order_id,'您提交了订单，请等待系统确认','提交订单',$user_id);

        $order = M('Order') -> where("order_id = $order_id")->find();

        $cartList = $this -> _post_data['orderGoods'];

        $adminListArray = array();
        foreach($cartList as $key => $val)
        {
            $adminListArray[$val['admin_id']] = $val['admin_id'];
            $goods = M('goods') -> where("goods_id = {$val['goods_id']} ")->find();
            $data2['order_id']           = $order_id; // 订单id
            $data2['admin_id']           = $val['admin_id']; // 供应商id
            $data2['goods_id']           = $val['goods_id']; // 商品id
            $data2['goods_name']         = $val['goods_name']; // 商品名称
            $data2['goods_sn']           = $val['goods_sn']; // 商品货号
            $data2['goods_num']          = $val['goods_num']; // 购买数量
            $data2['goods_postage']      = $this -> _post_data['count_postage'][$val['goods_id'].'_LM_'.$val['spec_key']]; // 商品邮费
            $data2['market_price']       = $val['market_price']; // 市场价
            $data2['goods_price']        = $val['goods_price']; // 商品价
            $data2['spec_key']           = $val['spec_key']; // 商品规格
            $data2['spec_key_name']      = $val['spec_key_name']; // 商品规格名称
            $data2['sku']                = $val['sku']; // 商品sku
            $data2['member_goods_price'] = $val['member_goods_price']; // 会员折扣价
            $data2['cost_price']         = $goods['cost_price']; // 成本价
            $data2['give_integral']      = $goods['give_integral']; // 购买商品赠送积分
            $data2['prom_type']          = $val['prom_type']; // 0 普通订单,1 限时抢购, 2 团购 , 3 促销优惠
            $data2['prom_id']            = $val['prom_id']; // 活动id
            if( !isSuccessToAddData("order_goods" , $data2) ){
                throw new \Exception('添加商品失败！');
            }
        }
        $adminListString = implode("][" ,$adminListArray );
        $adminListString = "[".$adminListString."]";
        //订单供应商
        M('order') -> where(array("order_id" =>$order_id )) -> save(array("admin_list" => $adminListString));
        M('Cart') -> where("user_id = '$user_id' and selected = 1")->delete();

        $data4['user_id'] = $user_id;
        $data4['user_money'] = -$car_price['balance'];
        $data4['pay_points'] = -($car_price['pointsFee'] * tpCache('shopping.point_rate'));
        $data4['change_time'] = $this -> nowTime;
        $data4['desc'] = '下单消费';
        $data4['order_sn'] = $order['order_sn'];
        $data4['order_id'] = $order_id;
//        // 如果使用了积分或者余额才记录
        ($data4['user_money'] || $data4['pay_points']) && M("AccountLog")->add($data4);

        $this -> _post_data['orderData'] = $order;
    }

    //订单生成第5步 订单后续处理
    private function _createOrderStep5(){

        $order = $this -> _post_data['orderData'];

        if( $this -> status == "inCreateOrder" ){
            if(!minus_stock($order["order_id"])){
                throw new \Exception('库存不足！');
            }
        }

        //更改最后一次购买时间
        saveData("users",array("user_id"=>$this -> user['user_id']),array("last_buy_time"=>time()));

        //改变优惠券状态
        if( $this -> _post_data['useCoupon'] == true &&!empty($this -> _post_data['couponInfo']) ){
            $condition = array(
                "id" => $this -> _post_data["userCouponId"],
                "uid" => $this -> user['user_id'],
                "order_id" => 0,
            );
            $save = array(
                "order_id" => $order["order_id"],
                "use_time" => $this -> nowTime,
            );

            $result = M('coupon_list') -> where($condition) -> save($save);
            if(empty($result)){
                throw new \Exception('优惠券使用失败！');
            }
        }

        if( isInCreateOrder( $this -> status )){
            sendWeChatMessageUseUserInfo( $this -> user , "下单" , array("orderId" => $order['order_id']) );
        }
    }


















    /***
     * 其他
     */


    /**
     * 获取pc微信支付二维码
     * @param $orderId
     * @return mixed
     */
    public function getWeChatCode($orderId){
        $payCode = "weixin"; // 支付 code
        //获取通知的数据
        $xml = $GLOBALS['HTTP_RAW_POST_DATA'];
        // 导入具体的支付类文件
        include_once  "plugins/payment/{$payCode}/{$payCode}.class.php";
        $code = '\\'.$payCode; // \alipay
        $payment = new $code();
        C('TOKEN_ON',false);
        header("Content-type:text/html;charset=utf-8");
        // 修改订单的支付方式
        $payment_arr = M('Plugin') -> where("`type` = 'payment'")->getField("code,name");
        M('order') -> where("order_id = $orderId")->save(array('pay_code'=>$payCode,'pay_name'=>$payment_arr[$payCode]));
        $order = M('order') -> where("order_id = $orderId")->find();
        $pay_radio = "pay_code";
        $config_value = parse_url_param($pay_radio);
        $code_str = $payment->get_code($order,$config_value);
        return $code_str;
    }



    /**
     * 查看订单是否满足条件参加活动
     * @param $order_amount
     * @return array
     */
    public function getOrderPromotion($order_amount){
        $parse_type = array('0'=>'满额打折','1'=>'满额优惠金额','2'=>'满额送倍数积分','3'=>'满额送优惠券','4'=>'满额免运费');
        $now = $this -> nowTime;
        $prom = M('prom_order') -> where("type<2 and end_time>$now and start_time<$now and money<=$order_amount")->order('money desc')->find();
        $res = array('order_amount'=>$order_amount,'order_prom_id'=>0,'order_prom_amount'=>0);
        if($prom){
            if($prom['type'] == 0){
                $res['order_amount']  = round($order_amount*$prom['expression']/100,2);//满额打折
                $res['order_prom_amount'] = $order_amount - $res['order_amount'] ;
                $res['order_prom_id'] = $prom['id'];
            }elseif($prom['type'] == 1){
                $res['order_amount'] = $order_amount- $prom['expression'];//满额优惠金额
                $res['order_prom_amount'] = $prom['expression'];
                $res['order_prom_id'] = $prom['id'];
            }
        }
        return $res;
    }

}