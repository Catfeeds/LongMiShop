<?php
namespace Wap\Controller;


class CartController extends WapBaseController
{


    function exceptAuthActions()
    {
        return array(
            'ajaxChangeCartData',
        );
    }

    /**
     * 析构流函数
     */
    public function _initialize()
    {
        parent::_initialize();
    }


    /**
     * 结算
     */
    public function settlement()
    {
        $cartLogic = new \Common\Logic\CartLogic();
        if ($cartLogic->cart_count($this->user_id, 1) == 0) {
            printJson(10002, "你的购物车没有选中商品", U('Mobile/Order/order_list'));
        }

        $address = getCurrentAddress($this->user_id, I('address_id', null));
        addressTheJump(ACTION_NAME);
        if (empty($address)) {
            printJson(10001, "正在跳转到添加地址页面", U('Mobile/User/edit_address', array('source' => 'cart2')));
        }
        $return = array();
        $result = $cartLogic->cartList($this->user, $this->session_id, 1, 1); // 获取购物车商品
        $cartList = $result['cartList'];
        $totalPrice = $result['total_price'];
        
        //计算邮费
        $sum = 0;
        $goods_data = array();
        $region_list = get_region_list();
        foreach ($cartList as $key => $item) {
            if ($item['selected'] == 1) {
                $goods_res = M('goods')->field('weight,delivery_way,original_img')->where("goods_id = '" . $item['goods_id'] . "'")->find();
                $cartList[$key]["original_img"] = $goods_res["original_img"];
                $goods_data[$key]['spec_key'] = $item['spec_key']; //商品规格
                $goods_data[$key]['goods_id'] = $item['goods_id']; //商品id
                $goods_data[$key]['goods_num'] = $item['goods_num']; //件数  重量
                $goods_data[$key]['goods_name'] = $item['goods_name']; //商品名称
                $goods_data[$key]['goods_price'] = $item['goods_price']; //商品价格
                $goods_data[$key]['weight'] = $goods_res['weight'];  //商品重量
                $goods_data[$key]['shipping_code'] = $goods_res['delivery_way']; //配送方式
                $goods_data[$key]['site'] = $region_list[$address['province']]['name']; //收获地址
            }
            if ($item['admin_id'] == 0) {
                $sum += $item['member_goods_price'];
            }
        }
        $address["province_name"] = $region_list[$address['province']]['name'];
        $address["city_name"] = $region_list[$address['city']]['name'];
        $address["district_name"] = $region_list[$address['district']]['name'];
        $return['address'] = $address;
        $count_postage = count_postage($goods_data); //运费
        $totalPrice['goods_fee'] = $totalPrice['total_fee'];
        $totalPrice['total_fee'] = $totalPrice['total_fee'] + $count_postage['data']['count'];

        $usersLogic = new \Common\Logic\UsersLogic();
        $result = $usersLogic->getCanUseCoupon($this->user_id, $sum, $goods_data);
        $return["couponList"] = $result['data']['result'];
        $return["couponCount"] = count($return["couponList"]);
        $return["cartList"] = $cartList;
        $return["total_price"] = $totalPrice;
        $return["carriage_sum"] = $count_postage['data']['count'];

        $is_no_money = false;
        if( $this->user['user_money'] < $totalPrice['total_fee']){
            $is_no_money = true;
        }
        $return["is_no_money"] = $is_no_money;
        printJson(true, "", $return);
    }

    /**
     * ajax 修改购物车中的商品数量
     */
    public function ajaxChangeCartData()
    {
        $cartLogic = new \Common\Logic\CartLogic();
        $goods_id = I("goods_id"); // 商品id
        $goods_num = $number = I("number");// 商品数量
        $key = I("key"); // 商品规格

        if (empty($goods_id) || empty($goods_num)) {
            exit(json_encode(array('status' => -1, 'msg' => '参数错误', 'result' => 0)));
        }
        $condition = array(
            'user_id'  => $this->user_id,   // 用户id
            'goods_id' => $goods_id,   // 商品id
        );
        if (!$this->user_id) {
            $condition["session_id"] = $this->session_id;
        }
        $goods_spec = "";
        if (!empty($key) && $key != "0") {
            $condition["spec_key"] = $key;
            $goods_spec = explode("_", $key);
        }
        $cartInfo = findDataWithCondition("cart", $condition);
        if (empty($cartInfo)) {
            if ($number > 0) {
                $result = $cartLogic->addCart($goods_id, $goods_num, $goods_spec, $this->session_id, $this->user_id); // 将商品加入购物车
                exit(json_encode($result));
            }
        } else {
            $goods_num = $cartInfo["goods_num"];
            $goods_num += $number;
            if ($goods_num == 0) {
                M("cart")->where($condition)->delete();
            } else {
                M("cart")->where($condition)->save(array("goods_num" => $goods_num));
            }
        }
        $cart_count = cart_goods_num($this->user_id, $this->session_id); // 查找购物车数量
        setcookie('cn', $cart_count, null, '/');
        if ($number < 0) {
            exit(json_encode(array('status' => 1, 'msg' => '数量调整成功', 'result' => $cart_count)));
        }
        exit(json_encode(array('status' => 1, 'msg' => '成功加入购物车', 'result' => $cart_count)));
    }


    /**
     * ajax 请求获取购物车列表
     */
    public function ajaxCartList()
    {
        $cartLogic = new \Common\Logic\CartLogic();
        $post_goods_num = I("goods_num"); // goods_num 购物车商品数量
        $post_cart_select = I("cart_select"); // 购物车选中状态
        $where = " session_id = '$this->session_id' "; // 默认按照 session_id 查询
        $this->user_id && $where = " user_id = ".$this->user_id; // 如果这个用户已经等了则按照用户id查询
        $cartList = M('Cart') -> where($where)->getField("id,goods_num,selected,prom_type,prom_id");
        if($post_goods_num)
        {
            foreach($post_goods_num as $key => $val)// 修改购物车数量 和勾选状态
            {
                $data['goods_num'] = $val < 1 ? 1 : $val;
                $data['selected'] = $post_cart_select[$key] ? 1 : 0 ;
                if(($cartList[$key]['goods_num'] != $data['goods_num']) || ($cartList[$key]['selected'] != $data['selected']))
                    M('Cart') -> where("id = $key")->save($data);
            }
            $this -> assign('select_all', $_POST['select_all']); // 全选框
        }

        $result = $cartLogic-> cartList($this->user, $this->session_id,1,1);
        if(empty($result['total_price'])){
            $result['total_price'] = Array( 'total_fee' =>0, 'cut_fee' =>0, 'num' => 0, 'atotal_fee' =>0, 'acut_fee' =>0, 'anum' => 0);
        }
        $return = array(
            "cartList" => $result['cartList'],
            "total_price" => $result['total_price'],
        );
        printJson(true, "", $return);
    }
}
