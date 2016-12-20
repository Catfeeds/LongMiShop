<?php
@include 'Addons/christmas/Function/base.php';

class christmasMobileController
{

    const TB_ORDER = "addons_christmas_order";
    const TB_ACTIVITY = "addons_christmas_activity";
    const TB_ORDER_GOODS = "addons_christmas_order_goods";
    const TB_ORDER_GET_LIST = "addons_christmas_order_get_list";
    const TB_ACTIVITY_GOODS = "addons_christmas_activity_goods";

    public $edition = null;
    public $userInfo = null;
    public $assignData = array();
    public $activityInfo = array();


    public $odds = array(
//        array(//龙米商城优惠券
//            "chance" => "100",
//            "number" => "0",
//            "in_odds" => array(
////                array(//商城千元优惠券
////                    "chance" => "3",
////                    "number" => "0",
////                    "type" =>"coupon",
////                    "id" => "1",
////                ),
//                array(//20元现金券
//                    "chance" => "59",
//                    "number" => "0",
//                    "type" =>"coupon",
//                    "id" => "13",
//                ),
//                array(//25元抵扣券
//                    "chance" => "40",
//                    "number" => "0",
//                    "type" =>"coupon",
//                    "id" => "7",
//                ),
////                array(//188元季度满减券
////                    "chance" => "10",
////                    "number" => "0",
////                    "type" =>"coupon",
////                    "id" => "1",
////                ),
////                array(//688元年卡优惠券
////                    "chance" => "6",
////                    "number" => "0",
////                    "type" =>"coupon",
////                    "id" => "1",
////                ),
//                array(//龙米买一送一券
//                    "chance" => "1",
//                    "number" => "50",
//                    "type" =>"coupon",
//                    "id" => "8",
//                )
//            )
//        ),
        array(//20元现金券
            "chance" => "100",
            "number" => "0",
            "type" =>"coupon",
            "id" => "13",
        ),
        array(//25元抵扣券
            "chance" => "100",
            "number" => "0",
            "type" =>"coupon",
            "id" => "7",
        ),
        array(//188元季度满减券
            "chance" => "100",
            "number" => "0",
            "type" =>"coupon",
            "id" => "14",
        ),
        array(//688元年卡优惠券
            "chance" => "100",
            "number" => "0",
            "type" =>"coupon",
            "id" => "15",
        ),
        array(//龙米买一送一券
            "chance" => "1",
            "number" => "50",
            "type" =>"coupon",
            "id" => "8",
        ),
        array(//宴午体验券
            "chance" => "100",
            "number" => "0",
            "id" => "12",
            "type" =>"coupon"
        ),
        array(//太二酸菜鱼
            "chance" => "10",
            "number" => "0",
            "in_odds" => array(
                array(//太二贵宾券（免排队）
                    "chance" => "50",
                    "number" => "100",
                    "type" =>"coupon",
                    "id" => "10",
                ),
                array(//太二现金券20元（吃饭抵扣）
                    "chance" => "50",
                    "number" => "50",
                    "type" =>"coupon",
                    "id" => "11",
                )
            )
        ),
        array(//恒大买房优惠券
            "chance" => "5",
            "number" => "0",
            "type" =>"coupon",
            "id" => "9",
        ),
        array(//新年礼遇直通券
            "chance" => "1",
            "number" => "5",
            "type" =>"goods",
            "id" => "1",
        )
    );

    //初始化
    public function __construct($userInfo)
    {
        $this->assignData["userInfo"] = $this->userInfo = $userInfo;
        $this->assignData["sharePath"] = "./Addons/christmas/Template/Mobile/default/Addons_share.html";
        $this->assignData["headerPath"] = "./Addons/christmas/Template/Mobile/default/Addons_header.html";
        $this->assignData["footerPath"] = "./Addons/christmas/Template/Mobile/default/Addons_footer.html";
        $this->assignData["activity"] = $this->activityInfo = addonsGetActivityInfo();
        $this->assignData["share"] = addonsGetShareArray($this->assignData["activity"], I("order_id", 0));
        $this->assignData["isFollow"] = $this->userInfo["is_follow"];
        $this->assignData["v"] = 1;
        $this->edition = $this->assignData["activity"]["id"];
        if (isWeChatBrowser()) {
            $weChatLogic = new \Common\Logic\WeChatLogic();
            $this->assignData["signPackage"] = $weChatLogic->getSignPackage();
        }
    }

    //圣诞故事
    public function index()
    {
        return $this->assignData;
    }

    //礼包内容
    public function rule()
    {
        $this->assignData["message"] = I("message", "");
        return $this->assignData;
    }


    //支付页面
    public function pay()
    {
        if (IS_POST) {
            $start_time = $this->activityInfo["start_time"];
            $end_time = $this->activityInfo["end_time"];
            if (time() < $start_time) {
                exit(json_encode(callback(false, "活动还未开始")));
            }
            if ($end_time < time()) {
                exit(json_encode(callback(false, "活动已经结束")));
            }
            $number = getCountWithCondition(self::TB_ORDER, array("activity_id" => $this->edition, "status" => array("neq", "0")));
            if ($number >= $this->activityInfo['number']) {
                exit(json_encode(callback(false, "礼包名额已满")));
            }
            $notPayOrderInfo = findDataWithCondition(self::TB_ORDER, array("user_id" => $this->userInfo["user_id"], "status" => "0"), "id");
            if (!empty($notPayOrderInfo)) {
                $save = array(
                    "id" => $notPayOrderInfo["id"]
                );
                $data = array(
                    "message" => I("message", "")
                );
                saveData(self::TB_ORDER, $save, $data);
                exit(json_encode(callback(true, "", $notPayOrderInfo["id"])));
            }
            $data = array(
                "user_id"     => $this->userInfo["user_id"],
                "activity_id" => $this->edition,
                "order_sn"    => date('YmdHis') . rand(1000, 9999),
                "status"      => 0,
                "money"       => $this->assignData["activity"]["money"],
                "message"     => I("message", ""),
                "create_time" => time(),
            );
            $orderId = addData(self::TB_ORDER, $data);
            foreach ($this->activityInfo["goods"] as $goodsItem) {
                $goods = array(
                    "activity_id"   => $this->edition,
                    "order_id"      => $orderId,
                    "admin_id"      => $goodsItem['admin_id'],
                    "goods_id"      => $goodsItem['goods_id'],
                    "goods_sn"      => $goodsItem['goods_sn'],
                    "goods_name"    => $goodsItem['goods_name'],
                    "spec_key"      => $goodsItem['spec_key'],
                    "spec_key_name" => $goodsItem['spec_key_name'],
                    "goods_num"     => $goodsItem['goods_num'],
                    "goods_money"   => $goodsItem['goods_money'],
                    "create_time"   => time()
                );

                addData(self::TB_ORDER_GOODS, $goods);
            }
            exit(json_encode(callback(true, "", $orderId)));
        }
        exit(json_encode(callback(false, "ERROR")));
    }

    //支付页面
    public function weChatPay()
    {
        $id = I("order_id");
        if ($_SESSION['openid'] && strstr($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger')) {
            $order = findDataWithCondition(self::TB_ORDER, array("id" => $id, "user_id" => $this->userInfo["user_id"]));
            if (!empty($order)) {
                addonsWeChatPay($id, "christmas");
                exit;
            }
        } else {
            exit;
        }
        exit;
    }

    //结果页
    public function results()
    {
        $id = I("order_id");
        if (!$this->assignData["orderInfo"] = addonsGetOrderInfo($id)) {
            return addonsError("未找到该订单");
        }
        if ($this->assignData["orderInfo"]['user_id'] != $this->userInfo ['user_id'] || $this->assignData["orderInfo"]['status'] == 0) {
            return addonsError("未找到该订单");
        }
        return $this->assignData;
    }

    //支付失败回复
    public function payBack()
    {
        $url = U("Mobile/Addons/christmas", array("pluginName" => "rule"));
        header("Location: " . $url);
        exit;
    }

    //订单页面
    public function order()
    {
        return $this->assignData;
    }

    //ajax订单列表
    public function ajaxOrderList()
    {
        $where = array();
        $type = I('type');
        $type = intval($type);
        $where['status'] = array("eq", $type);
        $where['user_id'] = $this->userInfo ['user_id'];
        $count = getCountWithCondition(self::TB_ORDER, $where);
        $limit = 10;
        $Page = new \Think\Page($count, $limit);
        $show = $Page->show();
        $order_str = "pay_time DESC";
        $orderList = M(self::TB_ORDER)->order($order_str)->where($where)->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assignData['show'] = $show;
        $this->assignData['lists'] = $orderList;
        $this->assignData['p'] = I('p');
        $this->assignData['number'] = I('number');
        $this->assignData['count'] = $count;
        $this->assignData['type'] = $type;
        $this->assignData['limit'] = $limit * I('p');
        return $this->assignData;
    }

    //订单详情页面
    public function orderDetail()
    {
        $id = I("order_id");
        if (!$this->assignData["orderInfo"] = addonsGetOrderInfo($id)) {
            return addonsError("未找到该订单");
        }
        if ($this->assignData["orderInfo"]['user_id'] != $this->userInfo['user_id']) {
            return addonsError("未找到该订单");
        }
        return $this->assignData;
    }

    //获取礼包
    public function shareInfo()
    {
        $id = I("order_id");
        if (!$this->assignData["orderInfo"] = addonsGetOrderInfo($id)) {
            return addonsError("未找到该订单");
        }
        if ($this->assignData["orderInfo"]['user_id'] == $this->userInfo['user_id']) {
            header("Location: " . U("Mobile/Addons/christmas", array("pluginName" => "orderDetail", "order_id" => $id)));
            exit;
        }
        return $this->assignData;
    }


    //礼物检测ajax
    public function getGift(){
        $id = I("orderId");
        if (!$this->assignData["orderInfo"] = addonsGetOrderInfo($id)) {
            exit(json_encode(callback(false,"未找到该订单")));
        }
        if ($this->assignData["orderInfo"]['user_id'] == $this->userInfo['user_id']) {
            exit(json_encode(callback(true,"", U("Mobile/Addons/christmas", array("pluginName" => "orderDetail", "order_id" => $id)))));
        }
        if( !empty($this->assignData["orderInfo"]['get_user_id'])){
            if ($this->assignData["orderInfo"]['get_user_id'] == $this->userInfo['user_id']) {
                exit(json_encode(callback(true,"", U("Mobile/Addons/christmas", array("pluginName" => "getResults", "order_id" => $id)))));
            }
            if ($this->assignData["orderInfo"]['get_user_id'] != $this->userInfo['user_id']) {
                exit(json_encode(callback(false,"该礼包已经被别人领取了")));
            }
        }
        /**
         * 随机部分
         */
        if( $this->assignData["orderInfo"]["status"] == 1 && empty($this->assignData["orderInfo"]["get_user_id"]) ){
            $rewardList = addonsNewGetReward($this -> odds);
            $have_goods = false;
            if( !empty($rewardList)) {
                saveData( self::TB_ORDER , array("id"=>$this->assignData["orderInfo"]["id"]),array("get_time"=>time(),"status"=>2,"get_user_id" => $this->userInfo['user_id']));
                foreach ($rewardList as $rewardItem) {
                    if( $rewardItem["isGet"] == true){
                        $key_id1 = $this->odds[$rewardItem['key']]['id'];
                        $key_id2 = $this->odds[$rewardItem['key']]["in_odds"][$rewardItem['key2']]['id'];
                        $key_type1 = $this->odds[$rewardItem['key']]['type'];
                        $key_type2 = $this->odds[$rewardItem['key']]["in_odds"][$rewardItem['key2']]['type'];
                        $key_id = empty($key_id1) ? $key_id2 : $key_id1;
                        $key_type = empty($key_type1) ? $key_type2 : $key_type1;
                        $key_type = $key_type == "coupon" ? 1 : 2;
                        $newData = array(
                            "c_order_id"  => $this->assignData["orderInfo"]["id"],
                            "user_id"     => $this->assignData["orderInfo"]['user_id'],
                            "get_key"     => intval($rewardItem['key']),
                            "get_key2"    => intval($rewardItem['key2']),
                            "create_time" => time(),
                            "order_id"    => 0,
                            "type"        => $key_type,
                            "get_id"      => intval($key_id),
                            "get_user_id" => $this->userInfo['user_id']
                        );
                        addData(  self::TB_ORDER_GET_LIST , $newData);
                        if( $key_type == 1){
                            addNewCoupon($key_id,$this->userInfo['user_id']);
                        }else if( $key_type == 2){
                            $have_goods = true;
                        }else{

                        }
                    }
                }
            }
            if( $have_goods == true ){
                exit(json_encode(callback(true,"", U("Mobile/Addons/christmas", array("pluginName" => "get", "order_id" => $id)))));
            }
        }
        exit(json_encode(callback(true,"", U("Mobile/Addons/christmas", array("pluginName" => "getResults", "order_id" => $id)))));
    }

    //领取礼包
    public function get()
    {
        $id = I("order_id");
        if (!$this->assignData["orderInfo"] = addonsGetOrderInfo($id)) {
            return addonsError("未找到该订单");
        }
        if ($this->assignData["orderInfo"]['user_id'] == $this->userInfo['user_id']) {
            header("Location: " . U("Mobile/Addons/christmas", array("pluginName" => "orderDetail", "order_id" => $id)));
            exit;
        }
        if ($this->assignData["orderInfo"]["status"] != 2) {
            header("Location: " . U("Mobile/Addons/christmas", array("pluginName" => "shareInfo", "order_id" => $id)));
            exit;
        }
        if( !empty($this->assignData["orderInfo"]['get_user_id'])){
            if ($this->assignData["orderInfo"]['get_user_id'] == $this->userInfo['user_id']) {
                header("Location: " . U("Mobile/Addons/christmas", array("pluginName" => "getResults", "order_id" => $id)));
                exit;
            }
            if ($this->assignData["orderInfo"]['get_user_id'] != $this->userInfo['user_id']) {
                return addonsError("该礼包已经被别人领取了");
            }
        }

        $address = getCurrentAddress($this->userInfo["user_id"], I('address_id', null));
        $this->assignData["address"] = $address;
        $this->assignData["region_list"] = get_region_list();
        addressTheJump("addons_christmas");
        if (empty($address)) {
            header("Location: " . U('Mobile/User/edit_address', array('source' => 'addons_christmas')));
            exit;
        }
        return $this->assignData;
    }


    //领取成功页面
    public function getResults()
    {
        $id = I("order_id");
        if (!$this->assignData["orderInfo"] = addonsGetOrderInfo($id)) {
            return addonsError("未找到该订单");
        }
        if ($this->assignData["orderInfo"]['user_id'] == $this->userInfo['user_id']) {
            header("Location: " . U("Mobile/Addons/christmas", array("pluginName" => "orderDetail", "order_id" => $id)));
            exit;
        }
        if( $this->assignData["orderInfo"]["status"] != 2 ){
            header("Location: " . U("Mobile/Addons/christmas", array("pluginName" => "shareInfo", "order_id" => $id)));
            exit;
        }
        if(  isExistenceDataWithCondition( self::TB_ORDER_GET_LIST , array("c_order_id"=>$id ,"get_user_id"=>$this->userInfo['user_id'],"order_id" => "0","type"=>"2" )) ){
            header("Location: " . U("Mobile/Addons/christmas", array("pluginName" => "get", "order_id" => $id)));
            exit;
        }
        if( !empty($this->assignData["orderInfo"]['get_user_id'])) {
            if ($this->assignData["orderInfo"]['get_user_id'] != $this->userInfo['user_id']) {
                return addonsError("该礼包已经被别人领取了");
            }
        }
        return $this->assignData;
    }


    //创建订单
    public function createOrder()
    {
        $buyLogic = new \Common\Logic\BuyLogic();
        exit(json_encode($buyLogic->createAddonsOrder("christmas")));
    }

}