<?php
namespace Mobile\Controller;
class CartController extends MobileBaseController {
    
    public $cartLogic; // 购物车逻辑操作类

    function exceptAuthActions()
    {
        return array(
            'cart',
            'addCart',
            'ajaxAddCart',
            'ajaxCartList',
            'ajaxChangeCartData',
            'cartList',
            'ajaxGetCartData'
        );
    }
    /**
     * 析构流函数
     */
    public function  _initialize() {
        parent::_initialize();
        $this->cartLogic = new \Common\Logic\CartLogic();
        if($this->user_id){
            M('Cart')->execute("update `__PREFIX__cart` set member_goods_price = goods_price * {$this->user['discount']} where (user_id ={$this->user_id} or session_id = '{$this->session_id}') and prom_type = 0");
        }
    }

    /**
     * 购物车页面
     */
    public function cart(){
        $this -> display();
    }

    /**
     * 将商品加入购物车
     */
    function addCart()
    {
        $goods_id = I("goods_id"); // 商品id
        $goods_num = I("goods_num");// 商品数量
        $goods_spec = I("goods_spec"); // 商品规格                
        $goods_spec = json_decode($goods_spec,true); //app 端 json 形式传输过来
        $unique_id = I("unique_id"); // 唯一id  类似于 pc 端的session id
        $user_id = I("user_id",0); // 用户id        
        $result = $this->cartLogic->addCart($goods_id, $goods_num, $goods_spec,$unique_id,$user_id); // 将商品加入购物车
        exit(json_encode($result)); 
    }
    /**
     * ajax 将商品加入购物车
     */
    function ajaxAddCart()
    {
        $goods_id = I("goods_id"); // 商品id
        $goods_num = I("goods_num");// 商品数量
        $goods_spec = I("goods_spec"); // 商品规格
        $result = $this->cartLogic->addCart($goods_id, $goods_num, $goods_spec,$this->session_id,$this->user_id); // 将商品加入购物车
        exit(json_encode($result));
    }
    /**
     * ajax 修改购物车中的商品数量
     */
    function ajaxChangeCartData()
    {
        $goods_id = I("goods_id"); // 商品id
        $goods_num = $number = I("number");// 商品数量
        $key = I("key"); // 商品规格

        if( empty($goods_id) ||empty($goods_num) ){
            exit(json_encode( array('status'=>-1,'msg'=>'参数错误','result'=>0)));
        }
        $condition = array(
            'user_id'         => $this->user_id,   // 用户id
            'goods_id'        => $goods_id,   // 商品id
        );
        if(! $this->user_id){
            $condition["session_id"] = $this->session_id;
        }
        $goods_spec = "";
        if( !empty($key) && $key != "0")
        {
            $condition["spec_key"] = $key;
            $goods_spec = explode("_",$key);
        }
        $cartInfo =findDataWithCondition("cart",$condition) ;
        if( empty( $cartInfo )){
            if(  $number > 0 ){
                $result = $this->cartLogic->addCart($goods_id, $goods_num, $goods_spec,$this->session_id,$this->user_id); // 将商品加入购物车
                exit(json_encode($result));
            }
        }else{
            $goods_num = $cartInfo["goods_num"];
            $goods_num += $number;
            if(  $goods_num == 0 ){
                M("cart")->where($condition)->delete();
            }else{
                M("cart")->where($condition)->save(array("goods_num"=>$goods_num));
            }
        }
        $cart_count = cart_goods_num($this->user_id,$this->session_id); // 查找购物车数量
        setcookie('cn',$cart_count,null,'/');
        if(  $number < 0 ) {
            exit(json_encode(array('status' => 1, 'msg' => '数量调整成功', 'result' => $cart_count)));
        }
        exit(json_encode(array('status' => 1, 'msg' => '成功加入购物车', 'result' => $cart_count)));
    }







    /*
     * 请求获取购物车列表
     */
    public function cartList()
    {
        $cart_form_data = $_POST["cart_form_data"]; // goods_num 购物车商品数量
        $cart_form_data = json_decode($cart_form_data,true); //app 端 json 形式传输过来

        $unique_id = I("unique_id"); // 唯一id  类似于 pc 端的session id
        $user_id = I("user_id"); // 用户id
        $where = " session_id = '$unique_id' "; // 默认按照 $unique_id 查询
        $user_id && $where = " user_id = ".$user_id; // 如果这个用户已经等了则按照用户id查询
        $cartList = M('Cart') -> where($where)->getField("id,goods_num,selected");
        if($cart_form_data)
        {
            // 修改购物车数量 和勾选状态
            foreach($cart_form_data as $key => $val)
            {
                $data['goods_num'] = $val['goodsNum'];
                $data['selected'] = $val['selected'];
                $cartID = $val['cartID'];
                if(($cartList[$cartID]['goods_num'] != $data['goods_num']) || ($cartList[$cartID]['selected'] != $data['selected']))
                    M('Cart') -> where("id = $cartID")->save($data);
            }
            //$this -> assign('select_all', $_POST['select_all']); // 全选框
        }

        $result = $this->cartLogic->cartList($this->user, $unique_id,0);
        exit(json_encode($result));
    }

    public function FetchRepeatMemberInArray($array) {
        // 获取去掉重复数据的数组
        $unique_arr = array_unique ( $array );
        // 获取重复数据的数组
        $repeat_arr = array_diff_assoc ( $array, $unique_arr );
        return $repeat_arr;
    }

    /**
     * 购物车第二步确定页面
     */
    public function cart2()
    {
        $region_list = get_region_list();
        $this -> assign('region_list',$region_list);

        $address = getCurrentAddress( $this->user_id , I('address_id',null) );
        addressTheJump(ACTION_NAME);
        if( empty($address) ){
            header("Location: ".U('Mobile/User/edit_address',array('source'=>'cart2')));
            exit;
        }
        $this -> assign('address',$address);

        if($this->cartLogic->cart_count($this->user_id,1) == 0 ){
            $this->error ('你的购物车没有选中商品',U('Mobile/Cart/cart'));
            exit;
        }

        $result = $this -> cartLogic -> cartList($this->user, $this->session_id,1,1); // 获取购物车商品
        $cartList = $result['cartList'];
        $totalPrice = $result['total_price'];

        //计算邮费
        $sum = 0;
        $goods_data = array();
        foreach( $cartList  as $key => $item){

            if($item['selected'] == 1){
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

            if($item['admin_id'] == 0){
                $sum += $item['member_goods_price'];
            }


        }



        $count_postage = count_postage($goods_data); //运费
        $totalPrice['goods_fee'] = $totalPrice['total_fee'];
        $totalPrice['total_fee'] = $totalPrice['total_fee'] + $count_postage['data']['count'];
        // dd($count_postage);
        $shippingList = M('Plugin') -> where("`type` = 'shipping' and status = 1")->select();// 物流公司

        $usersLogic = new \Common\Logic\UsersLogic();
        $result = $usersLogic -> getCanUseCoupon( $this->user_id , $sum ,$goods_data);

        $this -> assign('couponList',$result['data']['result']);
        $this -> assign('shippingList', $shippingList); // 物流公司
        $this -> assign('cartList', $cartList); // 购物车的商品
        $this -> assign('total_price', $totalPrice); // 总计
        $this -> assign('carriage_sum',$count_postage['data']['count']); //总邮费
        $this -> display();
    }



    /**
     * ajax 获取订单商品价格 或者提交 订单
     */
    public function cart3(){
        $buy_logic = new \Common\Logic\BuyLogic();
        $result = $buy_logic -> createOrder();
        die(json_encode($result));
    }	
//    /*
//     * 订单支付页面
//     */
//    public function cart4(){
//
//        $order_id = I('order_id');
//        $order = M('Order') -> where("order_id = $order_id")->find();
//        // 如果已经支付过的订单直接到订单详情页面. 不再进入支付页面
//        if($order['pay_status'] == 1){
//            $order_detail_url = U("Mobile/User/order_detail",array('id'=>$order_id));
//            header("Location: $order_detail_url");
//        }
//
//        $paymentList = M('Plugin') -> where("`type`='payment' and status = 1 and  scene in(0,1)")->select();
//        //微信浏览器
//        if(strstr($_SERVER['HTTP_USER_AGENT'],'MicroMessenger')){
//            $paymentList = M('Plugin') -> where("`type`='payment' and status = 1 and code in('weixin','cod')")->select();
//        }
//        $paymentList = convert_arr_key($paymentList, 'code');
//
//        foreach($paymentList as $key => $val)
//        {
//            $val['config_value'] = unserialize($val['config_value']);
//            if($val['config_value']['is_bank'] == 2)
//            {
//                $bankCodeList[$val['code']] = unserialize($val['bank_code']);
//            }
//        }
//
//        $bank_img = include 'Application/Home/Conf/bank.php'; // 银行对应图片
//        $payment = M('Plugin') -> where("`type`='payment' and status = 1")->select();
//        $this -> assign('paymentList',$paymentList);
//        $this -> assign('bank_img',$bank_img);
//        $this -> assign('order',$order);
//        $this -> assign('bankCodeList',$bankCodeList);
//        $this -> assign('pay_date',date('Y-m-d', strtotime("+1 day")));
//        $this -> display();
//    }


    /*
    * ajax 请求获取购物车列表
    */
    public function ajaxCartList()
    {
        $post_goods_num = I("goods_num"); // goods_num 购物车商品数量
        $post_cart_select = I("cart_select"); // 购物车选中状态
        $where = " session_id = '$this->session_id' "; // 默认按照 session_id 查询
        $this->user_id && $where = " user_id = ".$this->user_id; // 如果这个用户已经等了则按照用户id查询

        $cartList = M('Cart') -> where($where)->getField("id,goods_num,selected,prom_type,prom_id");

        if($post_goods_num)
        {
            // 修改购物车数量 和勾选状态
            foreach($post_goods_num as $key => $val)
            {                
                $data['goods_num'] = $val < 1 ? 1 : $val;
//                if($cartList[$key]['prom_type'] == 1) //限时抢购 不能超过购买数量
//                {
//                    $flash_sale = M('flash_sale') -> where("id = {$cartList[$key]['prom_id']}")->find();
//                    $data['goods_num'] = $data['goods_num'] > $flash_sale['buy_limit'] ? $flash_sale['buy_limit'] : $data['goods_num'];
//                }
//
                $data['selected'] = $post_cart_select[$key] ? 1 : 0 ;
                if(($cartList[$key]['goods_num'] != $data['goods_num']) || ($cartList[$key]['selected'] != $data['selected']))
                    M('Cart') -> where("id = $key")->save($data);
            }
            $this -> assign('select_all', $_POST['select_all']); // 全选框
        }

        $result = $this -> cartLogic-> cartList($this->user, $this->session_id,1,1);
        if(empty($result['total_price'])){
            $result['total_price'] = Array( 'total_fee' =>0, 'cut_fee' =>0, 'num' => 0, 'atotal_fee' =>0, 'acut_fee' =>0, 'anum' => 0);
        }
        $this -> assign('cartList', $result['cartList']); // 购物车的商品
        $this -> assign('total_price', $result['total_price']); // 总计
        $this -> display();
    }

    /*
 * ajax 获取用户收货地址 用于购物车确认订单页面
 */
    public function ajaxAddress(){

        $regionList = M('Region')->getField('id,name');

        $address_list = M('UserAddress') -> where("user_id = {$this->user_id}")->select();
        $c = M('UserAddress') -> where("user_id = {$this->user_id} and is_default = 1")->count(); // 看看有没默认收货地址
        if((count($address_list) > 0) && ($c == 0)) // 如果没有设置默认收货地址, 则第一条设置为默认收货地址
            $address_list[0]['is_default'] = 1;

        $this -> assign('regionList', $regionList);
        $this -> assign('address_list', $address_list);
        $this -> display('ajax_address');
    }

    /**
     * ajax 删除购物车的商品
     */
    public function ajaxDelCart()
    {
        $ids = I("ids"); // 商品 ids
         M("Cart") -> where(" id in ($ids)")->delete();
        exit(json_encode(callback(true,"删除成功")));
    }

    /**
     *ajax计算优惠券
     */
    public function ajaxCoupon(){
        if(IS_AJAX){
            $money = I('money'); //总金额
            $couponId = I('couponId');
            $result = cardDiscountAmountCalculation( $couponId , $this->user_id , $money);
            if( callbackIsTrue($result) ){
                exit(json_encode(callback(true,"计算成功",array('money'=>$result['data']['moneyRes'],'privilege'=>$result['data']['privilege']))));
            }else{
                exit(json_encode(callback(false,"没有此优惠券",array('res'=>$result['data']['couponListRes']))));
            }
        }
        
    }

    /**
     * ajax 获取购物车数量
     */
    public function ajaxGetCartData()
    {

        $where = " session_id = '$this->session_id' "; // 默认按照 session_id 查询
        $this->user_id && $where = " user_id = ".$this->user_id; // 如果这个用户已经等了则按照用户id查询
        $cartList = M('Cart') -> where($where)->field("goods_num") ->select();
        $cartNumber = 0 ;
        if(!empty($cartList)){
            foreach ( $cartList as  $cartItem){
                $cartNumber += $cartItem["goods_num"];
            }
        }
        echo $cartNumber;
    }
    public function ajaxGetCartList(){
        $result = $this -> cartLogic -> cartList($this->user, $this->session_id,1,1); // 获取购物车商品
        $cartList = $result['cartList'];
        if(empty($cartList)){
            exit(json_encode(callback(false)));
        }
        exit(json_encode(callback(true)));
    }


}
