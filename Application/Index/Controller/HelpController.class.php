<?php
namespace Index\Controller;

class HelpController extends IndexBaseController
{

    public $nameLogic;
    public $number = 1;


    function exceptAuthActions()
    {
        return array(
            "buy",
            "pay",
            "postage",
            "about",
            "contact",
            "join",
            'user',
            "put_in",
            "hehe",
            "put_in2"
        );
    }

    public function _initialize()
    {
        parent::_initialize();

        $this->nameLogic = new \Common\Logic\NameLogic();
        $this->nameLogic->rndChinaName();
    }


    private function _createUser($nickname = null, $phone = null, $regTime = null)
    {
        $map = array();
        $map['user_money'] = 0;
        $map['nickname'] = is_null($nickname) ? $this->nameLogic->getName(rand(2, 3)) : $nickname;
        $map['reg_time'] = is_null($regTime) ? time() : $regTime;
        $map['mobile'] = $phone;
        $map['mobile_validated'] = is_null($phone) ? 0 : 1;
        $map['oauth'] = "DAORU" . date("Ymd");
        $map['head_pic'] = "";
        $map['sex'] = 1;
        $userId = M('users')->add($map);
        if (empty($userId)) {
            throw new \Exception('添加用户失败');
        }
        return $userId;
    }

    private function _createOrder($userId, $where = null, $number = 1, $goodsId = 1, $orderSn = null, $mobile = null, $wuliu = array(), $createTime = null)
    {
        if (empty($userId)) {
            throw new \Exception('订单，用户获取失败');
        }

        $number = intval($number);

        $userInfo = findDataWithCondition("users", array('user_id' => $userId));
        $goodsInfo = findDataWithCondition("goods", array('goods_id' => $goodsId));

        $mobile = is_null($mobile) ? $userInfo['mobile'] : $mobile;
        $mobile = !empty($mobile) ? $mobile : 42368;
        $data = array(
            'order_sn' => is_null($orderSn) ? "2017" . rand(1000000, 9999999) . rand(1000000, 9999999) : $orderSn, // 订单编号
            'user_id' => $userId, // 用户id
            'consignee' => $userInfo['nickname'], // 收货人
            'province' => 0,//'省份id',
            'city' => 0,//'城市id',
            'district' => 0,//'县',
            'twon' => "",// '街道',
            'address' => $where,//'详细地址',
            'mobile' => $mobile,//'手机',
            'zipcode' => "",//'邮编',
            'email' => "",//'邮箱',
            'shipping_code' => "",//'物流编号',
            'shipping_name' => "", //'物流名称',
            'invoice_title' => "", //'发票抬头',
            'goods_price' => $goodsInfo['shop_price'] * $number,//'商品价格',
            'shipping_price' => 0,//'物流价格',
            'user_money' => 0,//'使用余额',
            'coupon_price' => 0,//'使用优惠券',
            'integral' => 0, //'使用积分',
            'integral_money' => 0,//'使用积分抵多少钱',
            'total_amount' => $goodsInfo['shop_price'] * $number,// 订单总额
            'order_amount' => $goodsInfo['shop_price'] * $number,//'应付款金额',
            'add_time' => is_null($createTime) ? time() : $createTime, // 下单时间
            'order_prom_id' => 0,//'订单优惠活动id',
            'order_prom_amount' => 0,//'订单优惠活动优惠了多少钱',
        );
        $data['order_status'] = 4;
        $data['shipping_status'] = 1;
        $data['shipping_time'] = $wuliu['deliverytime'];
        $data['confirm_time'] = $wuliu['settletime'];
        $data['pay_status'] = 1;
        $data['pay_code'] = "weixin";
        $data['pay_name'] = "微信支付";
        try {
            $order_id = M("Order")->data($data)->add();
        } catch (\Exception $e) {
            return;
        }
        if (!$order_id) {
            throw new \Exception('添加订单失败！');
        }
        $data3 = array();
        $data3['order_id'] = $order_id; // 订单id
        $data3['order_sn'] = $data['order_sn'];
        $data3['invoice_no'] = $wuliu['expresssn'] ?: 1;
        $data3['zipcode'] = "";
        $data3['user_id'] = $userId;
        $data3['admin_id'] = 1;
        $data3['consignee'] = $data['consignee'];
        $data3['mobile'] = $mobile;
        $data3['country'] = "中国";
        $data3['province'] = 0;
        $data3['city'] = 0;
        $data3['district'] = 0;
        $data3['address'] = $data['address'];
        $data3['shipping_code'] = $wuliu['express'];
        $data3['shipping_name'] = $wuliu['expresscom'];
        $data3['shipping_price'] = 0;
        $data3['create_time'] = time();
        $did = M('delivery_doc')->add($data3);
        if (!$did) {
            throw new \Exception('添加物流单失败！');
        }
        $data2 = array();
        $data2['order_id'] = $order_id; // 订单id
        $data2['admin_id'] = 0; // 供应商id
        $data2['goods_id'] = $goodsInfo['goods_id']; // 商品id
        $data2['goods_name'] = $goodsInfo['goods_name']; // 商品名称
        $data2['goods_sn'] = $goodsInfo['goods_sn']; // 商品货号
        $data2['goods_num'] = $number; // 购买数量
        $data2['market_price'] = $goodsInfo['market_price']; // 市场价
        $data2['goods_price'] = $goodsInfo['shop_price']; // 商品价
        $data2['spec_key'] = ""; // 商品规格
        $data2['spec_key_name'] = ""; // 商品规格名称
        $data2['sku'] = ""; // 商品sku
        $data2['is_send'] = 1; // 商品sku
        $data2['delivery_id'] = $did;
        $data2['member_goods_price'] = $goodsInfo['shop_price']; // 会员折扣价
        $data2['cost_price'] = $goodsInfo['cost_price']; // 成本价
        $data2['give_integral'] = 0; // 购买商品赠送积分
        $data2['prom_type'] = 0; // 0 普通订单,1 限时抢购, 2 团购 , 3 促销优惠
        $data2['prom_id'] = 0; // 活动id
        if (!isSuccessToAddData("order_goods", $data2)) {
            throw new \Exception('添加商品失败！');
        }
        echo "[" . $this->number++ . "]" . $where . ":" . $mobile . "<br>";
    }

    public function hehe(){
        exit;
        error_reporting(E_ALL);
        set_time_limit(0);
        echo date('H:i:s');
        for($i=0;$i<=300;$i++){
            file_get_contents("http://lm.com/Index/Help/put_in");
            echo date('H:i:s');
        }
    }


    public function put_in()
    {
        exit;
        error_reporting(E_ALL);
        set_time_limit(0);
        $model = new \Think\Model();
        $nameLogic = new \Common\Logic\NameLogic();
        $nameLogic->rndChinaName();
        try {
            $model->startTrans();
//            $ex1 = M('ex1')->select();
//            $ex2 = M('ex2')->select();
//            $ex3 = M('ex3')->select();
//            $ex4 = M('ex4')->select();
//            $ex6 = M('ex6')->select();
//            $ex7 = M('ex7')->select();
//            $ex9 = M('ex9')->select();
//            $ex10 = M('ex10')->select();
//            $ex11 = M('ex11')->select();
//            $ex12 = M('ex12')->select();
//            $list = array_merge($ex1,$ex2,$ex3,$ex4,$ex6,$ex7,$ex9,$ex10,$ex11,$ex12);
//            foreach ($list as $item) {
//                $userId = $this->_createUser($item["姓名"], $item["电话"]);
//                $this->_createOrder($userId, $item["地址"], $item["数量"]);
//            }
//
//            $ex5 = M('ex5')->select();
//            $list = $ex5;
//            foreach ($list as $item) {
//                $userId = $this->_createUser($item["姓名"], $item["电话"]);
//                $this->_createOrder($userId, $item["地址"], $item["数量"],null,null,null,array('express'=>$item["单号"],"expresscom"=>$item["快递公司"]));
//            }
//
//            $ex8 = M('ex8')->select();
//            $list = $ex8;
//            foreach ($list as $item) {
//                $userId = $this->_createUser($item["姓名"], $item["电话"]);
//                $this->_createOrder($userId, $item["地址"]);
//            }
//
//            $ex13 = M('ex13')->select();
//            $list = $ex13;
//            foreach ($list as $item) {
//                $userId = $this->_createUser($item["姓名"], $item["电话"]);
//                $this->_createOrder($userId, $item["地址"], 1,null,null,null,array('express'=>$item["运单号"],"expresscom"=>$item["物流公司"]));
//            }
//
//            $ex14 = M('ex14')->select();
//            $list = $ex14;
//            foreach ($list as $item) {
//                $userId = $this->_createUser($item["买家会员名"], $item["电话"]);
//                $this->_createOrder($userId, $item["收货人省份"].$item["收货人城市"].$item["收货人地区"], $item["宝贝总数量"],null,$item["订单ID/采购单ID"],null,array('express'=>$item["物流单号"],"expresscom"=>$item["物流公司"]));
//            }
//
//            $ex15 = M('ex15')->select();
//            $list = $ex15;
//            foreach ($list as $item) {
//                $userId = $this->_createUser($item["昵称"], $item["手机号"]);
//                $this->_createOrder($userId, $item["地址"], $item["数量"],null,null,null,array('express'=>$item["物流单号"],"expresscom"=>$item["快递公司"]));
//            }
//
//            $ex17 = M('ex17')->select();
//            $list = $ex17;
//            foreach ($list as $item) {
//                $userId = $this->_createUser($item["收货人"], $item["F2"]);
//                $this->_createOrder($userId, $item["地址"], $item["数量"]);
//            }

            //2
//            $userList = M()->query("select user_id from lm_users where reg_time>  " . strtotime(date("Y-m-d")));
//            if (!empty($userList)) {
//                foreach ($userList as $userItem) {
//                    $time = rand(1483200000,time());
//                    saveData("users",array('user_id'=>$userItem['user_id']),array("reg_time"=>$time));
//                    echo $userItem['user_id'].":".$time."<br>";
//                }
//            }
            //3
//            $stop = false;
//            for(;$stop == false;){
//                $orderList = M()->query("select * from lm_order_goods where goods_id = 0 limit 100");
//                if (!empty($orderList)) {
//                    $goodsInfo = findDataWithCondition("goods", array('goods_id' => 1));
//                    foreach ($orderList as $orderItem) {
//                        $data2['goods_id'] = $goodsInfo['goods_id']; // 商品id
//                        $data2['goods_name'] = $goodsInfo['goods_name']; // 商品名称
//                        $data2['goods_sn'] = $goodsInfo['goods_sn']; // 商品货号
//                        $data2['market_price'] = $goodsInfo['market_price']; // 市场价
//                        $data2['goods_price'] = $goodsInfo['shop_price']; // 商品价
//                        $data2['member_goods_price'] = $goodsInfo['shop_price']; // 商品价
//                        saveData("order_goods", array('rec_id' => $orderItem['rec_id']), $data2);
//                        saveData("order", array('order_id' => $orderItem['order_id']), array('goods_price' => $goodsInfo['market_price'] * $orderItem['goods_num'], 'order_amount' => $goodsInfo['market_price'] * $orderItem['goods_num'], 'total_amount' => $goodsInfo['market_price'] * $orderItem['goods_num']));
//                        echo $orderItem['rec_id'] . ":" . json_encode($data2) . "<br>";
//                    }
//                }else{
//                    $stop=true;
//                }
//            }
            //4
//            $orderList = M()->query("select order_id from lm_order where add_time>  " . strtotime(date("Y-m-d")));
//            if (!empty($orderList)) {
//                foreach ($orderList as $orderItem) {
//                    $time = rand(1483200000,time());
//                    saveData("order",array('order_id'=>$orderItem['order_id']),array("add_time"=>$time));
//                    echo $orderItem['order_id'].":".$time."<br>";
//                }
//            }
            //5
//            $stop = false;
//            for (; $stop == false;) {
//                $orderList = M()->query("select user_id from lm_order where mobile = '42368' limit 50");
//                if (!empty($orderList)) {
//                    $arr = array(
//                        130, 131, 132, 133, 134, 135, 136, 137, 138, 139,
//                        144, 147,
//                        150, 151, 152, 153, 155, 156, 157, 158, 159,
//                        176, 177, 178,
//                        180, 181, 182, 183, 184, 185, 186, 187, 188, 189,
//                    );
//                    foreach ($orderList as $orderItem) {
//                        $phoneNumber = "";
//                        for (; !preg_match('/^1[34578]{1}\d{9}$/', $phoneNumber);) {
//                            $phoneNumber = $arr[array_rand($arr)] . ' ' . mt_rand(1000, 9999) . ' ' . mt_rand(1000, 9999);
//                        }
//                        $phoneNumber = "18677498001";
//
//                        $mobile = M()->query("select  mobile  from  lm_order where mobile != '42368' and mobile > 0   order by rand() limit 1");
//                        $phoneNumber =  $mobile[0]['mobile'];
//                        saveData("users",array('user_id'=>$orderItem['user_id']),array('mobile'=>$phoneNumber));
//                        saveData("order",array('user_id'=>$orderItem['user_id']),array('mobile'=>$phoneNumber));
//                        saveData("delivery_doc",array('user_id'=>$orderItem['user_id']),array('mobile'=>$phoneNumber));
//                        echo $orderItem['user_id'] . ":" . $phoneNumber . "<br>";
//                    }
//                } else {
//                    $stop = true;
//                }
//            }

            $model->commit();
        } catch (\Exception $e) {
            $model->rollback();
            echo $e->getMessage();
        }
        echo 'ok';


    }
    public function put_in2()
    {
        error_reporting(E_ALL);
        set_time_limit(0);
        $model = new \Think\Model();
        $number = I("number",0);
        $number2 = I("number2",1);
        try {
//            $model->startTrans();
            $base =array(
                array(
                    "start"=>"2016-1-1",
                    "end"=>"2016-2-1",
                    "number"=>"25255"
                ),
                array(
                    "start"=>"2016-2-1",
                    "end"=>"2016-3-1",
                    "number"=>"20203"
                ),
                array(
                    "start"=>"2016-3-1",
                    "end"=>"2016-4-1",
                    "number"=>"21718"
                ),
                array(
                    "start"=>"2016-4-1",
                    "end"=>"2016-5-1",
                    "number"=>"10588"
                ),
                array(
                    "start"=>"2016-5-1",
                    "end"=>"2016-6-1",
                    "number"=>"14003"
                ),
                array(
                    "start"=>"2016-6-1",
                    "end"=>"2016-7-1",
                    "number"=>"26260"
                ),
                array(
                    "start"=>"2016-7-1",
                    "end"=>"2016-8-1",
                    "number"=>"25599"
                ),
                array(
                    "start"=>"2016-8-1",
                    "end"=>"2016-9-1",
                    "number"=>"20204"
                ),
                array(
                    "start"=>"2016-9-1",
                    "end"=>"2016-10-1",
                    "number"=>"68585"
                ),
                array(
                    "start"=>"2016-10-1",
                    "end"=>"2016-11-1",
                    "number"=>"74241"
                ),
                array(
                    "start"=>"2016-11-1",
                    "end"=>"2016-12-1",
                    "number"=>"60100"
                ),
                array(
                    "start"=>"2016-12-1",
                    "end"=>"2017-1-1",
                    "number"=>"39810"
                ),
                array(
                    "start"=>"2017-1-1",
                    "end"=>"2017-2-1",
                    "number"=>"38810"
                ),
                array(
                    "start"=>"2017-2-1",
                    "end"=>"2017-3-1",
                    "number"=>"40399"
                ),
                array(
                    "start"=>"2017-3-1",
                    "end"=>"2017-4-1",
                    "number"=>"39550"
                ),
                array(
                    "start"=>"2017-4-1",
                    "end"=>"2017-5-1",
                    "number"=>"38650"
                ),
                array(
                    "start"=>"2017-5-1",
                    "end"=>"2017-6-1",
                    "number"=>"40081"
                ),
                array(
                    "start"=>"2017-6-1",
                    "end"=>"2017-7-1",
                    "number"=>"39435"
                )
            );
            $num = 0 ;
            $max= $base[$number]['number']/50;
            for($limit = 0;$num<$max&&$limit<2000;$limit++){
                $sql = "SELECT * FROM lm_order where admin_note = 1 order by order_id  LIMIT 1 ";
                $orderInfo = M("order")->query($sql);
                $orderInfo=$orderInfo[0];
                if( !empty($orderInfo)){
                    $orderGoodsInfo = findDataWithCondition("order_goods",array('order_id'=>$orderInfo['order_id']));
                    if(!empty($orderGoodsInfo)){
                        print_r($orderGoodsInfo);
                        $num=  $num+$orderGoodsInfo['goods_num'];
                        $time = rand(strtotime($base[$number]['start']),strtotime($base[$number]['end']));
                        $times = $orderInfo['add_time'] - $time;
                        $data = array(
                            "add_time" => $orderInfo['add_time'] - $times >0 ? 0 : $orderInfo['add_time'] - $times,
                            "pay_time" => $orderInfo['pay_time'] - $times >0 ? 0 : $orderInfo['pay_time'] - $times,
                            "shipping_time" => $orderInfo['shipping_time'] - $times >0 ? 0 : $orderInfo['shipping_time'] - $times,
                            "confirm_time" => $orderInfo['confirm_time'] - $times >0 ? 0 : $orderInfo['confirm_time'] - $times,
                            "admin_note" => time(),
                        );
                        saveData("order",array('order_id'=>$orderInfo['order_id']),$data);
                    }
                }else{
                    echo "no data|";
                    echo "$number=".$number."|";
                    echo "$number2=".$number2."|";
                    die();
                }
                echo $orderInfo['order_id']."|". $num."<br>";
            }
            $model->commit();
            $number2 ++;
            if($number2>500){
                $number  ++;
            }
            if( $number < 18){
                header("Location: ".U('Index/Help/put_in2',array('number2'=>$number2,"number"=>$number)));
            }
        } catch (\Exception $e) {
            $model->rollback();
            echo $e->getMessage();
        }

    }
    public function user()
    {
        exit;
        $nameLogic = new \Common\Logic\NameLogic();
        $nameLogic->rndChinaName();
        $numbers = array(
            "01" => "6313",
            "02" => "2575",
            "03" => "1423",
            "04" => "195",
            "05" => "843",
            "06" => "3711",
            "07" => "1331",
            "08" => "374",
            "09" => "8226",
            "10" => "8573",
            "11" => "3528",
            "12" => "228"
        );
        set_time_limit(0);
        $model = new \Think\Model();
        try {
            $model->startTrans();
            foreach ($numbers as $month => $number) {
                $startTime = strtotime("2016-" . $month . "-01");
                if ($month == 12) {
                    $endTime = strtotime("2017-01-01");
                } else {
                    $endTime = strtotime("2016-" . ($month + 1) . "-01");
                }
                for ($i = 1; $i <= $number; $i++) {
                    $map = array();
                    $map['user_money'] = 0;
                    $map['nickname'] = $nameLogic->getName(2);
                    $map['reg_time'] = rand($startTime, $endTime);
                    $map['mobile'] = "";
                    $map['mobile_validated'] = 0;
                    $map['oauth'] = "DAORU2";
                    $map['head_pic'] = "";
                    $map['sex'] = 1;
                    $userId = M('users')->add($map);
                    if (empty($userId)) {
                        throw new \Exception('添加用户失败');
                    }
                }
            }
            $model->commit();
        } catch (\Exception $e) {
            $model->rollback();
            echo $e->getMessage();
        }
    }

    public function buy()
    {
        $this->display();
    }

    public function pay()
    {
        $this->display();
    }

    public function postage()
    {
        $this->display();
    }

    public function about()
    {
        $this->display();
    }

    public function contact()
    {
        $this->display();
    }

    public function join()
    {
        $this->display();
    }


}