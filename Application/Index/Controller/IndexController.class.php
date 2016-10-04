<?php
namespace Index\Controller;


use Think\Model;

class IndexController extends IndexBaseController {

    function exceptAuthActions()
    {
        return array(
            'index',
            'test',
            'test2',
            'test3',
            "test4"
        );
    }

    public function _initialize() {
        parent::_initialize();
    }

    public function index(){
    	$this->display();
    }

    public function test4(){
        exit;
        $weChatConfig = M('wx_user')->find();
        if( empty( $weChatConfig ) ){
            return false;
        }
        $jsSdkLogic = new \Common\Logic\JsSdkLogic($weChatConfig['appid'], $weChatConfig['appsecret']);
        $jsSdkLogic -> push_msg( "owjy5v4020Mh7yNAT0aVapESwqNM" , "<a href='http://www.baidu.com'>23333</a>" );
    }
    public function test(){
        exit;
        set_time_limit(0);
        $model = new Model();
        $region_list = get_region_list();
        $regionList = array();
        foreach ($region_list as $region_item){
            $regionList[$region_item['name']] = $region_item['id'];
        }

        $goods = M('goods')->where("goods_id = '1' ")->find();
        try{
            $model -> startTrans();
            for ($i = 0;; ){
                $userList = M()->table("ims_mc_mapping_fans") -> limit($i,100) -> select();
                if(empty($userList)){
                    $model -> commit();
                    dd("ok");
                }

                foreach ($userList as $item){
                    $fan = $item;
                    if (!empty($fan['tag']) && is_string($fan['tag'])) {
                        $fan['tag'] = @base64_decode($fan['tag']);
                        $fan['tag'] = @unserialize($fan['tag']);
                    }
                    $item = $fan;
                    $fanId = $item['fanid'];
                    $openid = $item['openid'];
                    $memberInfo = M()->table("ims_mc_members") ->where("uid ='{$item['uid']}'") -> find();
                    $map= array();
                    $map['user_money'] = empty($memberInfo['credit2'])?0:$memberInfo['credit2'];
                    $map['openid'] = $item['openid'];
                    $map['nickname'] = $item['nickname'];
                    $map['reg_time'] = $item['createtime'];
                    $map['oauth'] = "WeChat";
                    $map['head_pic'] = $item['tag']['headimgurl'];
                    $map['sex'] = 1;
                    $userId = M('users')->add($map);
                    if(empty($userId)){
                        throw new \Exception('添加用户失败');
                    }


                    print_r($userId);
                    echo "<br>";
                    /**
                     * 地址部分
                     */
                    $addressInfo = M()->table("ims_dist_address") ->where("openid ='{$openid}' and deleted = 0") -> select();
                    if(!empty($addressInfo)){
                        foreach ($addressInfo as $addressItem){
                            if( !empty($regionList[$addressItem["province"]]) &&
                                !empty($regionList[$addressItem["city"]]) &&
                                !empty($regionList[$addressItem["area"]] )
                            ){
                                $address = array(
                                    "user_id"=>$userId,
                                    "consignee"     => $addressItem["realname"],
                                    "mobile"        => $addressItem["mobile"],
                                    "province"      => $regionList[$addressItem["province"]],
                                    "city"          => $regionList[$addressItem["city"]],
                                    "district"      => $regionList[$addressItem["area"]],
                                    "address"       => $addressItem["address"],
                                    "is_default"       => $addressItem["isdefault"],
                                );
                                $addressId = M('user_address')->add($address);
                                if(empty($addressId)){
                                    throw new \Exception('添加用户地址失败');
                                }
                            }
                        }
                    }

                    $orderInfo = M()->table("ims_dist_order") ->where("fanid ='{$fanId}' and status in (0,1,2,3,4) ") -> select();
                    if(!empty($orderInfo)){
                        foreach ($orderInfo as $orderItem){
                            $addressId = 0;
                            $orderId = $orderItem['id'];
                            $addressId = $orderItem['addressid'];
                            $yudanInfo = M()->table("ims_dist_order_dispatch") ->where("orderid ='{$orderId}' ") -> find();
                            $orderGoodsInfo = M()->table("ims_dist_order_goods") ->where("orderid ='{$orderId}' ") -> find();
                            $addressInfo = M()->table("ims_dist_address") ->where("id ='{$addressId}' ") -> find();
                            if( !empty($addressId) &&
                                !empty($orderGoodsInfo) &&
                                !empty($regionList[$addressInfo["province"]]) &&
                                !empty($regionList[$addressInfo["city"]]) &&
                                !empty($regionList[$addressInfo["area"]] )
                            ){
                                if(!empty($yudanInfo)){
                                    $orderItem['expresscom'] = !empty($yudanInfo['expresscom'])?$yudanInfo['expresscom']: $orderItem['expresscom'] ;

                                    $orderItem['expresssn'] = !empty($yudanInfo['expresssn'])?$yudanInfo['expresssn']: $orderItem['expresssn'] ;

                                    $orderItem['express'] = !empty($yudanInfo['express'])?$yudanInfo['express']: $orderItem['express'] ;
                                }
                                $data = array(
                                    'order_sn'         =>$orderItem['ordersn'], // 订单编号
                                    'user_id'          =>$userId, // 用户id
                                    'consignee'        =>$addressInfo['realname'], // 收货人
                                    'province'         =>$regionList[$addressInfo['province']],//'省份id',
                                    'city'             =>$regionList[$addressInfo['city']],//'城市id',
                                    'district'         =>$regionList[$addressInfo['area']],//'县',
                                    'twon'             =>"",// '街道',
                                    'address'          =>$addressInfo['address'],//'详细地址',
                                    'mobile'           =>$addressInfo['mobile'],//'手机',
                                    'zipcode'          =>"",//'邮编',
                                    'email'            =>"",//'邮箱',
                                    'shipping_code'    =>"",//'物流编号',
                                    'shipping_name'    =>$orderItem['expresscom'], //'物流名称',
                                    'invoice_title'    =>"", //'发票抬头',
                                    'goods_price'      =>$orderItem['goodsprice'],//'商品价格',
                                    'shipping_price'   =>$orderItem['dispatchprice'],//'物流价格',
                                    'user_money'       =>0,//'使用余额',
                                    'coupon_price'     =>0,//'使用优惠券',
                                    'integral'         =>0, //'使用积分',
                                    'integral_money'   =>0,//'使用积分抵多少钱',
                                    'total_amount'     =>$orderItem['price'],// 订单总额
                                    'order_amount'     =>$orderItem['price'],//'应付款金额',
                                    'add_time'         =>$orderItem['createtime'], // 下单时间
                                    'order_prom_id'    =>0,//'订单优惠活动id',
                                    'order_prom_amount'=>0,//'订单优惠活动优惠了多少钱',
                                );

                                if($orderItem['status'] == 0){
                                    $data['order_status']  = 0;
                                    $data['shipping_status']  = 0;
                                    $data['pay_status']  = 0;
                                }elseif($orderItem['status'] == 1){
                                    $data['order_status']  = 1;
                                    $data['shipping_status']  = 0;
                                    $data['pay_status']  = 1;
                                }elseif($orderItem['status'] == 2){
                                    $data['order_status']  = 1;
                                    $data['shipping_status']  = 1;
                                    $data['shipping_time'] = $orderItem['deliverytime'];
                                    $data['confirm_time'] = $orderItem['settletime'];

                                    $data['pay_status']  = 1;
                                }elseif($orderItem['status'] == 3){
                                    $data['order_status']  = 4;
                                    $data['shipping_status']  = 1;
                                    $data['shipping_time'] = $orderItem['deliverytime'];
                                    $data['confirm_time'] = $orderItem['settletime'];
                                    $data['pay_status']  = 1;
                                }elseif($orderItem['status'] == 4){
                                    $data['order_status']  = 4;
                                    $data['shipping_status']  = 1;
                                    $data['shipping_time'] = $orderItem['deliverytime'];
                                    $data['confirm_time'] = $orderItem['settletime'];
                                    $data['pay_status']  = 1;
                                }

                                if($orderItem['paytype'] == 1){
                                    $data['pay_code']  = "balance";
                                    $data['pay_name']  = "余额支付";
                                }

                                if($orderItem['paytype'] == 2){
                                    $data['pay_code']  = "weixin";
                                    $data['pay_name']  = "微信支付";
                                }
                                $order_id = M("Order")->data($data)->add();
                                if(!$order_id){
                                    throw new \Exception('添加订单失败！');
                                }
                                $did = 0;
                                if( $data['shipping_status'] == 1){
                                    $data3 = array();
                                    $data3['order_id']           = $order_id; // 订单id
                                    $data3['order_sn'] = $orderItem['ordersn'];
                                    $data3['invoice_no'] = $orderItem['expresssn'];
                                    $data3['zipcode'] = "";
                                    $data3['user_id'] = $userId;
                                    $data3['admin_id'] = 1;
                                    $data3['consignee'] = $addressInfo['realname'];
                                    $data3['mobile'] = $addressInfo['mobile'];
                                    $data3['country'] = "中国";
                                    $data3['province'] = $regionList[$addressInfo['province']];
                                    $data3['city'] = $regionList[$addressInfo['city']];
                                    $data3['district'] = $regionList[$addressInfo['area']];
                                    $data3['address'] = $addressInfo['address'];
                                    $data3['shipping_code'] = $orderItem['express'];
                                    $data3['shipping_name'] = $orderItem['expresscom'];
                                    $data3['shipping_price'] = $orderItem['dispatchprice'];
                                    $data3['create_time'] = time();
                                    $did = M('delivery_doc')->add($data3);
                                    if(!$did){
                                        throw new \Exception('添加物流单失败！');
                                    }

                                }

//                            dd($orderGoodsInfo);
                                $data2 = array();
                                $data2['order_id']           = $order_id; // 订单id
                                $data2['admin_id']           = $goods['admin_id']; // 供应商id
                                $data2['goods_id']           = $goods['goods_id']; // 商品id
                                $data2['goods_name']         = $goods['goods_name']; // 商品名称
                                $data2['goods_sn']           = $goods['goods_sn']; // 商品货号
                                $data2['goods_num']          = $orderGoodsInfo['total']; // 购买数量
                                $data2['market_price']       = $orderGoodsInfo['price']; // 市场价
                                $data2['goods_price']        = $orderGoodsInfo['price']; // 商品价
                                $data2['spec_key']           = ""; // 商品规格
                                $data2['spec_key_name']      = ""; // 商品规格名称
                                $data2['sku']                = ""; // 商品sku
                                if( $data['shipping_status'] == 1 ) {
                                    $data2['is_send'] = 1; // 商品sku
                                    $data2['delivery_id'] = $did;
                                }
                                $data2['member_goods_price'] = $orderGoodsInfo['price']; // 会员折扣价
                                $data2['cost_price']         = $orderGoodsInfo['cost']; // 成本价
                                $data2['give_integral']      = 0; // 购买商品赠送积分
                                $data2['prom_type']          = 0; // 0 普通订单,1 限时抢购, 2 团购 , 3 促销优惠
                                $data2['prom_id']            = 0; // 活动id
                                if( !isSuccessToAddData("order_goods" , $data2) ){
                                    throw new \Exception('添加商品失败！');
                                }
                            }



                        }
                    }


                }
                $i+=100;
            }

        } catch (\Exception $e){
            $model -> rollback();
            echo  $e->getMessage();
        }

    }



    public function test3(){
        exit;
        set_time_limit(0);
        $model = new Model();

        try{
            $model -> startTrans();
            for ($i = 0;; ){

                $userList = M()->table("ims_activity_coupon_recode") -> limit($i,100) -> select();
                if(empty($userList)){
                    $model -> commit();
                    dd("ok");
                }
                foreach ( $userList as $userItem){
                    $data = array();
                    $data['gift_coupon_id'] = 1;
                    $data['create_time'] = $userItem['createtime'];
                    $data['code'] = $userItem['code'];
                    $data['state'] = 0;
                    if( $userItem["status"] == 1 && !empty($userItem['recid']) ) {
                        $fansInfo = M()->table("ims_mc_mapping_fans")->where(array('fanid' => $userItem['fanid']))->field("openid")->find();

                        $userInfo = findDataWithCondition('users', array('openid' => $fansInfo['openid']), "user_id");
                        $data['user_id'] = $userInfo["user_id"];
                        if( empty($data['user_id'])){
                            unset($data['user_id']);
                        }
                        $recid = $userItem['recid'];
                        $data['receive_time'] = $userItem['gettime'];
                        $recordInfo = M()->table("ims_activity_coupon_record")->where(array('recid' => $recid))->find();
                        if ($recordInfo['status'] == 1) {
                            $data['state'] = 1;
                        }
                        if ($recordInfo['status'] == 2) {
                            $data['state'] = 2;
                            $data['use_time'] = $recordInfo['usetime'];
                        }
                    }
//                    dd(M('coupon_code')->getField());
                    $id = M('coupon_code')->add($data);
                    if( !$id ){
                        throw new \Exception('添加失败！');
                    }
                }

                $i+=100;
            }

        } catch (\Exception $e){
            $model -> rollback();
            echo  $e->getMessage();
        }

    }

    public function test2(){
        exit;
        set_time_limit(0);
        $model = new Model();

        try{
            $model -> startTrans();
            for ($i = 0;; ){

                $userList = M("users") -> limit($i,100) -> select();
                if(empty($userList)){
                    $model -> commit();
                    dd("ok");
                }
                foreach ( $userList as $userItem){


                    $add['cid'] = 2;
                    $add['type'] = 4;
                    $add['uid'] = $userItem['user_id'];
                    $add['send_time'] = time();
                    do{
                        $code = get_rand_str(8,0,1);//获取随机8位字符串
                        $check_exist = findDataWithCondition('coupon_list',array('code'=>$code),"code");
                    }while($check_exist);
                    $add['code'] = $code;
                    $id = M('coupon_list')->add($add);
                    if( !$id ){
                        throw new \Exception('添加失败！');
                    }
                }

                $i+=100;
            }

        } catch (\Exception $e){
            $model -> rollback();
            echo  $e->getMessage();
        }

    }



}