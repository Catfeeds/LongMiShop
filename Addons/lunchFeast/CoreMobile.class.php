<?php
@include 'Addons/lunchFeast/Function/base.php';
class lunchFeastMobileController
{

    public $assignData = array();
    public $userInfo = array();

    public function __construct( $userInfo )
    {
        $this -> userInfo = $userInfo;
        $this -> assignData["headerPath"] = "./Addons/lunchFeast/Template/Mobile/default/Addons_header.html";
        $this -> assignData["footerPath"] = "./Addons/lunchFeast/Template/Mobile/default/Addons_footer.html";
        define("TB_SHOP", "addons_lunchfeast_shop");
        define("TB_MEAL", "addons_lunchfeast_meal_list");
        define("TB_GOODS", "addons_lunchfeast_shop_goods");
        define("TB_CONFIG", "addons_lunchfeast_config");
        define("TB_ORDER", "addons_lunchfeast_order");
        define("TB_ORDER_USER", "addons_lunchfeast_order_user");
    }
    //主页
    public function index()
    {
        $this -> assignData["config"] = findDataWithCondition( TB_CONFIG );
        $shopList = getShopList();
        $mealList = getMealList();
        if( empty( $shopList ) ){
            return addonsError( "宴午还没有开始" );
        }
        if( empty( $mealList ) ){
            return addonsError( "还没设置时间" );
        }
        $today = strtotime(date('Y-m-d',strtotime("+1 day")));
        $lastDay = strtotime(date("Y-m-d",strtotime("+1 month +1 day")));
        $this -> assignData['today'] = $today;
        $this -> assignData['lastDay'] = $lastDay;
        $this -> assignData['regionList'] = get_region_list();
        $this -> assignData["shopList"] = $shopList;
        $this -> assignData["mealList"] = $mealList;
        return $this->assignData;
    }
    //ajax菜品列表
    public function ajaxShopMealList(){
        $id = I("id");
        $shopMealList = getShopMealList($id);
        if( !empty( $shopMealList )){
            exit(json_encode( callback( true, "",$shopMealList ) ));
        }
        exit(json_encode( callback( false, "未找到" ) ));
    }
    //ajax用餐店铺 时间 价格
    public function ajaxShopGoods(){
        $mealId = I('mealId');
        $shopGoodsList = M('addons_lunchfeast_shop_goods')->where(array('meal_id'=>$mealId))->select();
        $shop = M('addons_lunchfeast_shop')->field('seats')->where(array('id'=>$mealId))->find();
        foreach($shopGoodsList as $goodsKey=>$goodsItem){
            $dataArrat = array(
                'date'=>$goodsItem['date'],
                'shop_id'=>$mealId,
                'meal_id'=>$goodsItem['meal_id']
            );
            //剩余座位数
            $number = M('addons_lunchfeast_order')->where($dataArrat)->sum('number');
            $seats = $shop['seats'] - $number;
            $shopGoodsList[$goodsKey]['seats'] = $seats > 0 ? $seats : '0';
        }
        exit(json_encode(callback(true,'',array('timeList'=>$shopGoodsList))));

    }
    //店铺主页
    public function shopDetail()
    {
        $id = I( "id" );
        $shopInfo = findDataWithCondition( TB_SHOP , array( "id" => $id ) );
        if( empty( $shopInfo ) ){
            return addonsError( "没有此店" );
        }
        $this -> assignData["shopInfo"] = $shopInfo;
        return $this->assignData;
    }
    //我的宴午
    public function orderList()
    {
        return $this->assignData;
    }
    //获取二维码
    public function qrCode(){
        $url = I("url");
        if( empty( $url ) ){
            die( "Not Find The Url !");
        }
        vendor("Poster.phpqrcode");
        // 纠错级别：L、M、Q、H
        $level = 'L';
        // 点的大小：1到10,用于手机端4就可以了
        $size = 8;
        QRcode::png("$url", false, $level, $size);
        exit;
    }
    //ajax我的宴午
    public function ajaxOrderList()
    {
        $where = array();
        $type = I('type');
        $type = intval($type);
        $today = strtotime(date('Y-m-d',strtotime("+1 day")));
        if ($type == "0") {
            $where['status'] = "1";
//            $where['date'] = array("egt",$today);
        }
        if ($type == "1") {
            $where['status'] = array("in","2,3");
//            $where['date'] = array("lt",$today);
        }
        $where['user_id'] = $this->userInfo ['user_id'];
        $count = getCountWithCondition(TB_ORDER, $where);
        $limit = 10;
        $Page = new \Think\Page($count, $limit);
        $show = $Page->show();
        $order_str = "pay_time DESC";
        $orderList = M( TB_ORDER )->order($order_str)->where($where)->limit($Page->firstRow . ',' . $Page->listRows)->select();
        if( !empty( $orderList ) ){
            foreach ( $orderList as $orderKey => $orderItem ){
                $orderList[$orderKey]["shopData"] = findDataWithCondition( TB_SHOP , array( "id" => $orderItem["shop_id"] ) );
            }
        }
        $this->assignData['today'] = $today;
        $this->assignData['show'] = $show;
        $this->assignData['lists'] = $orderList;
        $this->assignData['p'] = I('p');
        $this->assignData['number'] = I('number');
        $this->assignData['count'] = $count;
        $this->assignData['type'] = $type;
        $this->assignData['limit'] = $limit * I('p');
        return $this->assignData;
    }

    //订单详情 我的二维码
    public function orderDetail()
    {
        $id = I('id');
        $orderInfo = findDataWithCondition( TB_ORDER , array("user_id" => $this -> userInfo['user_id'] , "id" => $id) );
        if( empty( $orderInfo ) ){
            return addonsError( "没有此店" );
        }
        $orderInfo["userList"] = selectDataWithCondition( TB_ORDER_USER , array( "order_id" => $id));
        foreach ( $orderInfo["userList"] as $userKey => $userItem){
            $orderInfo["userList"][$userKey]["userInfo"] = findDataWithCondition( "addons_lunchfeast_diningper" , array( "uid" => $this -> userInfo['user_id'] , "id" => $userItem["diningper_id"]));
        }
        $this->assignData['orderInfo'] = $orderInfo;
        return $this->assignData;
    }
    //菜品结果
    public function foods()
    {
        $date = I("date");
        $shopId = I("shopId");
        $mealId = I("mealId");
        $shopInfo = findDataWithCondition( TB_SHOP , array( "is_online" => 1 , "id" => $shopId ) );
        $mealInfo = findDataWithCondition( TB_MEAL , array( "is_show" => 1 , "is_delete" => "0" , "id" => $mealId ) );
        if( empty( $date ) || empty( $shopId ) || empty( $mealId ) || $date < time() || empty( $shopInfo ) || empty( $mealInfo ) ){
            return addonsError( "参数错误" );
        }
        $where = array(
            'shop_id'=>$shopId,
            'date'=>$date,
            'meal_id'=>$mealId
        );
        $shopGoods = M('addons_lunchfeast_shop_goods')->where($where)->find();
        session('ShopData',$shopGoods);
        $shopRes = M('addons_lunchfeast_shop')->field('seats')->where(array('id'=>$shopId))->find();
        $number = M('addons_lunchfeast_order')->where($where)->sum('number');
        $shopGoods['seats'] = $shopRes['seats'] - $number;
        $shopGoods['content'] = str_replace("\r\n","<br/>",$shopGoods['content']);
        $this->assignData['shopGoods'] = $shopGoods;


        return $this->assignData;
    }
    //提交页面
    public function pageSubmit()
    {
        $list = M('addons_lunchfeast_diningper')->where(array('uid'=>$this -> userInfo['user_id'],'pitchon'=>1))->select();
        $ShopData = session('ShopData');
        $this->assignData['money'] = $ShopData['money'] * count($list);
        $this->assignData['sum'] = count($list);
        $this->assignData['list'] = $list;
        return $this->assignData;
    }
    //移除用餐人
    public function removePer(){
        $id = I('delPerId');
        $res = M('addons_lunchfeast_diningper')->where(array('id'=>$id))->save(array('pitchon'=>0));
        $count = M('addons_lunchfeast_diningper')->where(array('uid'=>$this -> userInfo['user_id'],'pitchon'=>1))->count();
        if($res){
            exit(json_encode(callback(true,'',array('count'=>$count))));
        }
        exit(json_encode(callback(false,'移除失败')));
    }
    //添加用餐人
    public function aMeal()
    {
        if(IS_POST){
            $data = I('post.');
            unset($data['pluginName']);
            $where["id"] = array('in',$data['list']);
            setPitchon($this -> userInfo['user_id']);
            $res[] = M('addons_lunchfeast_diningper')->where($where)->save(array('pitchon'=>1));
            if($res >= 1){
                exit(json_encode(callback(true)));
            }
            exit(json_encode(callback(false)));
        }
        $list = M('addons_lunchfeast_diningper')->where(array('uid'=>$this -> userInfo['user_id'],'show'=>'1'))->order('add_time DESC')->select();
        $this->assignData['list'] = $list;
        return $this->assignData;
    }

    //删除用餐人
    public function ajaxDelMeal(){
        $id = I('ids');
        $res = M('addons_lunchfeast_diningper')->where(array('id'=>$id))->save(array('show'=>0));
        if($res){
            exit(json_encode(callback(true)));
        }
        exit(json_encode(callback(false,'删除失败')));
    }

    //新增用餐人
    public function addAMeal()
    {
        if(IS_POST){
            $data = I('post.');
            unset($data['pluginName']);
            $data['uid'] = $this -> userInfo['user_id'];
            $data['add_time'] =  time();
            $res = M('addons_lunchfeast_diningper')->add($data);
            if($res){
                exit(json_encode(callback(true,'添加成功')));
            }
            exit(json_encode(callback(false,'添加失败')));
        }
        return $this->assignData;
    }

    //结算页面
    public function payment()
    {
        $ShopData = session('ShopData');
        $OrderWhere = array(
            'shop_id'=>$ShopData['shop_id'],
            'date'=>$ShopData['date'],
            'meal_id'=>$ShopData['meal_id'],
        );
        $shopRes = M('addons_lunchfeast_shop')->field('seats')->where(array('id'=>$ShopData['shop_id']))->find();
        $number = M('addons_lunchfeast_order')->where($OrderWhere)->sum('number');
        $countPer = M('addons_lunchfeast_diningper')->where(array('uid'=>$this -> userInfo['user_id'],'pitchon'=>1))->count();
        $seats = $shopRes['seats'] - $number; //剩余座位
        if($seats >= $countPer){
            $userId = $this -> userInfo['user_id'];
            $order_sn = date('YmdHis').rand(1000,9999);
            $order_amount = $ShopData['money'] * $countPer; //总价
            $pay_amount = $ShopData['money'] * $countPer; //实际支付金额
            $OrderData = array(
                'order_sn'=> $order_sn,
                'order_amount'=>$order_amount,
                'pay_amount' =>$pay_amount,
                'status'=>'0', //状态 未支付
                'create_time'=>time(),
                'date'=>$ShopData['date'], //就餐时间
                'meal_id'=>$ShopData['meal_id'], //菜品id
                'shop_id'=>$ShopData['shop_id'], //店铺id
                'meal_content'=>$ShopData['content'], //菜品
                'number'=>$countPer,
                'user_id'=>$userId, //用户id
            );
            $OrderRes = M('addons_lunchfeast_order')->add($OrderData);
            $perList = M('addons_lunchfeast_diningper')->where(array('uid'=>$userId,'pitchon'=>1))->select();
            foreach($perList as $perItem){
                do{
                    $code = get_rand_str(8,0,1);//获取随机8位字符串
                    $check_exist = findDataWithCondition('addons_lunchfeast_order_user',array('code'=>$code),"code");
                }while($check_exist);
                $dataData = array(
                    'order_id'=>$OrderRes,
                    'diningper_id'=>$perItem['id'],
                    'code'=>$code
                );
                M('addons_lunchfeast_order_user')->add($dataData);
            }


            header("Location: " . U("Mobile/Addons/lunchFeast",array('pluginName' => "weChatPay" ,"id" => $OrderRes)));
            exit;
        }else{
            return addonsError( "该店铺的座位数不够" , U("Mobile/Addons/lunchFeast",array('pluginName' => "pageSubmit")));
        }
    }
    //支付页面
    public function weChatPay()
    {
        $id = I("id");
        if( $_SESSION['openid'] && strstr($_SERVER['HTTP_USER_AGENT'],'MicroMessenger')){
            $order = findDataWithCondition( TB_ORDER , array("id"=>$id));
            if( !empty( $order ) ){
                addonsWeChatPay( $id , "lunchFeast" );
                exit;
            }
        }else{
            exit;
        }
        exit;
    }
    //结果页
    public function results()
    {
        $id = I("id");
        $results = M('addons_lunchfeast_order')->where(array('id'=>$id))->find();
        setPitchon($results['user_id']);
        //清除session
        session('ShopData',null);
        $mealList = selectMealList();
        $shopList = M('addons_lunchfeast_shop')->where(array('id'=>$results['shop_id']))->find();
        $results['meal'] = date('Y-m-d',$results['date']).' '.$mealList[$results['meal_id']];
        $results['shopName'] = $shopList['shop_name'];
        $this->assignData['results'] = $results;
        return $this->assignData;
    }
    //支付回调
    public function payBack(){
        $id = I("id");
        $where  = array(
            'user_id'=>$this -> userInfo['user_id'],
            'status'=>'0',
            'id'=>$id,
        );
        $res = M('addons_lunchfeast_order')->where($where)->find();
        if(!empty($res)){
            M('addons_lunchfeast_order')->where($where)->delete();
            M('addons_lunchfeast_order_user')->where(array('order_id'=>$res['id']))->delete();
            header("Location: " . U("Mobile/Addons/lunchFeast",array('pluginName' => "pageSubmit")));
        }
        exit;
    }

    //获取日期列表
    public function getDateList(){
        $shopId = I("shopId");
        $returnArray = array();
        $mealList = selectMealList();
        $today = strtotime(date('Y-m-d',strtotime("+1 day")));
        $lastDay = strtotime(date("Y-m-d",strtotime("+1 month +1 day")));
        for( $i =$today ;$i < $lastDay; $i+=24*60*60 ){
            $returnArray[$i] = array(
                "is_null" => true,
                "date" => $i,
                "dateView" => date("Y-m-d", $i),
                "htmlView" => date("d", $i),
            );
            foreach ($mealList as $mealKey => $mealItem){
                $returnArray[$i][$mealKey] = array("is_null"=>true);
            }
        }
        $condition = array(
            "shop_id" => $shopId
        );
        $goodsList = selectDataWithCondition( TB_GOODS , $condition );
        if( !empty( $goodsList ) ){
            foreach ( $goodsList as $goodsItem  ){
                if( !empty( $goodsItem["content"] ) && !empty( $goodsItem["money"] ) && $goodsItem["money"] > 0 && !empty($returnArray[$goodsItem['date']])){
                    $returnArray[$goodsItem['date']][$goodsItem['meal_id']] = $goodsItem;
                    $returnArray[$goodsItem['date']][$goodsItem['is_null']] = false;
                    $returnArray[$goodsItem['date']]["is_null"] = false;
                }
            }
        }
        $return = array(
            "marginLeft" =>( (date("w",$today) * 40) + 5),
            "date" => $returnArray,
        );
        exit(json_encode(callback(true,"",$return)));
    }
}