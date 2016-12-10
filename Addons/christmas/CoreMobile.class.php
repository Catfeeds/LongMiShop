<?php
@include 'Addons/christmas/Function/base.php';
class christmasMobileController
{

    const TB_ORDER = "addons_christmas_order";
    const TB_ORDER_GOODS = "addons_christmas_order_goods";
    const TB_ACTIVITY = "addons_christmas_activity";
    const TB_ACTIVITY_GOODS = "addons_christmas_activity_goods";

    public $userInfo = null;
    public $edition = null;
    public $assignData = array();
    public $activityInfo = array();


    //初始化
    public function __construct($userInfo)
    {
        $this->assignData["userInfo"] =$this->userInfo = $userInfo;
        $this->assignData["sharePath"] = "./Addons/christmas/Template/Mobile/default/Addons_share.html";
        $this->assignData["headerPath"] = "./Addons/christmas/Template/Mobile/default/Addons_header.html";
        $this->assignData["footerPath"] = "./Addons/christmas/Template/Mobile/default/Addons_footer.html";
        $this->assignData["activity"] = $this -> activityInfo = addonsGetActivityInfo();
        $this->assignData["share"] = addonsGetShareArray($this->assignData["activity"], I("order_id", 0));
        $this->edition = $this->assignData["activity"]["id"];
        $weChatLogic = new \Common\Logic\WeChatLogic();
        $this->assignData["signPackage"] = $weChatLogic->getSignPackage();
    }

    //圣诞故事
    public function index()
    {
        return $this->assignData;
    }

    //礼包内容
    public function rule()
    {
        return $this->assignData;
    }


    //支付页面
    public function pay()
    {
        if ( IS_POST ) {
            $start_time = $this->activityInfo["start_time"];
            $end_time = $this->activityInfo["end_time"];
            if (time() < $start_time) {
                exit(json_encode(callback(false,"活动还未开始")));
            }
            if ($end_time > time()) {
                exit(json_encode(callback(false,"活动已经结束")));
            }
            $number = getCountWithCondition(self::TB_ORDER, array("activity_id" => $this->activityInfo['id'],"status"=>array("neq","0")));
            if ($number >= $this->activityInfo['number']) {
                exit(json_encode(callback(false,"礼包名额已满")));
            }
            $notPayOrderInfo = findDataWithCondition( self::TB_ORDER, array("user_id"=> $this->userInfo["user_id"],"status"=>"0"),"id");
            if( !empty($notPayOrderInfo)){
                exit(json_encode(callback(true,"",$notPayOrderInfo["id"])));
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
                    "activity_id"   => $this->activityInfo['id'],
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
            exit(json_encode(callback(true,"",$orderId)));
        }
        exit(json_encode(callback(false,"ERROR")));
    }
    //支付页面
    public function weChatPay()
    {
        $id = I("order_id");
        if ($_SESSION['openid'] && strstr($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger')) {
            $order = findDataWithCondition(self::TB_ORDER, array("id" => $id,"user_id"=>$this->userInfo["user_id"]));
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
        if( !$this->assignData["orderInfo"] = addonsGetOrderInfo( $id  ) ){
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
        $where['status'] = array("eq",$type);
        $where['user_id'] = $this->userInfo ['user_id'];
        $count = getCountWithCondition(self::TB_ORDER, $where);
        $limit = 10;
        $Page = new \Think\Page($count, $limit);
        $show = $Page->show();
        $order_str = "pay_time DESC";
        $orderList = M( self::TB_ORDER )->order($order_str)->where($where)->limit($Page->firstRow . ',' . $Page->listRows)->select();
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
        if( !$this->assignData["orderInfo"] = addonsGetOrderInfo( $id ) ){
            return addonsError("未找到该订单");
        }
        return $this->assignData;
    }

    //获取礼包
    public function shareInfo()
    {
        $id = I("order_id");
        if( !$this->assignData["orderInfo"] = addonsGetOrderInfo( $id ) ){
            return addonsError("未找到该订单");
        }
        return $this->assignData;
    }

    //领取礼包
    public function get()
    {
        $id = I("order_id");
        if( !$this->assignData["orderInfo"] = addonsGetOrderInfo( $id  ) ){
            return addonsError("未找到该订单");
        }
        $address = getCurrentAddress( $this->userInfo["user_id"] , I('address_id',null) );
        $this -> assignData["address"] = $address ;
        $this -> assignData["region_list"] = get_region_list() ;
        addressTheJump( "addons_christmas");
        if( empty($address) ){
            header("Location: ".U('Mobile/User/edit_address',array('source'=>'addons_christmas')));
        }
        return $this->assignData;
    }

    //领取成功页面
    public function getResults()
    {
        $id = I("order_id");
        if( !$this->assignData["orderInfo"] = addonsGetOrderInfo( $id  ) ){
            return addonsError("未找到该订单");
        }
        return $this->assignData;
    }


    //创建订单
    public function createOrder()
    {
        $buyLogic = new \Common\Logic\BuyLogic();
        exit(json_encode($buyLogic -> createAddonsOrder("christmas")));
    }

}