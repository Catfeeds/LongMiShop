<?php
namespace Index\Controller;

class ShopController extends BaseIndexController {

    public $cartLogic;

    function exceptAuthActions()
    {
        return array(
            'index',
        );
    }

    public function _initialize() {
        $this->cartLogic = new \Common\Logic\CartLogic();
        parent::_initialize();
    }

    public function index(){
        //  form表单提交
        C('TOKEN_ON',true);
        $goodsLogic = new \Common\Logic\GoodsLogic();
        $goods_id = I("get.id");
        $goods_id = 104;
        $goods = M('Goods')->where("goods_id = '$goods_id'")->find();
        if(empty($goods) || ($goods['is_on_sale'] == 0)){
            $this->error('该商品已经下架',U('Index/index'));
        }
        if($goods['brand_id']){
            $brnad = M('brand')->where("id =".$goods['brand_id'])->find();
            $goods['brand_name'] = $brnad['name'];
        }
        $goods_images_list = M('GoodsImages')->where("goods_id = $goods_id")->select(); // 商品 图册
        $goods_attribute = M('GoodsAttribute')->getField('attr_id,attr_name'); // 查询属性
        $goods_attr_list = M('GoodsAttr')->where("goods_id = $goods_id")->select(); // 查询商品属性表
        $filter_spec = $goodsLogic->get_spec($goods_id);
        //商品是否正在促销中
        if($goods['prom_type'] == 1)
        {
            $goods['flash_sale'] = get_goods_promotion($goods['goods_id']);
            $flash_sale = M('flash_sale')->where("id = {$goods['prom_id']}")->find();
            $this->assign('flash_sale',$flash_sale);
        }

        $spec_goods_price  = M('spec_goods_price')->where("goods_id = $goods_id")->getField("key,price,store_count"); // 规格 对应 价格 库存表
        M('Goods')->where("goods_id=$goods_id")->save(array('click_count'=>$goods['click_count']+1 )); //统计点击数
        $commentStatistics = $goodsLogic->commentStatistics($goods_id);// 获取某个商品的评论统计
        $cart_count = M('cart')->where("user_id = '".$this->user_id."'")->count();
        $this->assign('spec_goods_price', json_encode($spec_goods_price,true)); // 规格 对应 价格 库存表
        $this->assign('commentStatistics',$commentStatistics);//评论概览
        $this->assign('goods_attribute',$goods_attribute);//属性值
        $this->assign('goods_attr_list',$goods_attr_list);//属性列表
        $this->assign('filter_spec',$filter_spec);//规格参数
        $this->assign('goods_images_list',$goods_images_list);//商品缩略图
        $this->assign('siblings_cate',$goodsLogic->get_siblings_cate($goods['cat_id']));//相关分类
        $this->assign('look_see',$goodsLogic->get_look_see($goods));//看了又看
        $this->assign('goods',$goods);
        $this->assign('cart_count',$cart_count);
        dump($this->user);exit;
        $this->display();
    }

    function ajaxAddCart()
    {
        $goods_id = I("goods_id"); // 商品id
        $goods_num = I("goods_num");// 商品数量
        $goods_spec = I("goods_spec"); // 商品规格            
        $result = $this->cartLogic->addCart($goods_id, $goods_num, $goods_spec,$this->session_id,$this->user_id); // 将商品加入购物车                     
        exit(json_encode($result));       
    }

    public function cart(){
        $this->display();
    }

    public function cart2(){
         if($this->user_id == 0)
             $this->error('请先登陆',U('Index/User/login'));
        
         if($this->cartLogic->cart_count($this->user_id,1) == 0 )
             $this->error ('你的购物车没有选中商品','Cart/cart');
        
        $result = $this->cartLogic->cartList($this->user, $this->session_id,1,1); // 获取购物车商品
        $sum = 0;
        foreach($result['cartList'] as $item){ //计算总额
            $sum += $item['goods_price'] * $item['goods_num'];
        }    
        $this->assign('cartList', $result['cartList']); // 购物车的商品                
        $this->assign('total_price', $sum); // 总计
        $this->display();
    }


    /**
     * ajax 获取订单商品价格 或者提交 订单
     */
    public function cart3(){
        $buy_logic = new \Common\Logic\BuyLogic();
        $result = $buy_logic -> createOrder();
        die(json_encode($result));
    }


    /*
     * ajax 请求获取购物车列表
     */
    public function ajaxCartList()
    {
        $cartLogic  = new \Common\Logic\CartLogic();
        $post_goods_num = I("goods_num"); // goods_num 购物车商品数量
        $post_cart_select = I("cart_select"); // 购物车选中状态
        $where = " session_id = '$this->session_id' "; // 默认按照 session_id 查询
        $this->user_id && $where = " user_id = ".$this->user_id; // 如果这个用户已经等了则按照用户id查询

        $cartList = M('Cart')->where($where)->getField("id,goods_num,selected,prom_type,prom_id");

        if($post_goods_num)
        {
            // 修改购物车数量 和勾选状态
            foreach($post_goods_num as $key => $val)
            {
                $data['goods_num'] = $val < 1 ? 1 : $val;

                if($cartList[$key]['prom_type'] == 1) //限时抢购 不能超过购买数量
                {
                    $flash_sale = M('flash_sale')->where("id = {$cartList[$key]['prom_id']}")->find();
                    $data['goods_num'] = $data['goods_num'] > $flash_sale['buy_limit'] ? $flash_sale['buy_limit'] : $data['goods_num'];
                }

                $data['selected'] = $post_cart_select[$key] ? 1 : 0 ;
                if(($cartList[$key]['goods_num'] != $data['goods_num']) || ($cartList[$key]['selected'] != $data['selected']))
                    M('Cart')->where("id = $key")->save($data);
            }
            $this->assign('select_all', $_POST['select_all']); // 全选框
        }

        $result = $cartLogic->cartList($this->user, $this->session_id,1,1); // 选中的商品
        if(empty($result['total_price']))
            $result['total_price'] = Array( 'total_fee' =>0, 'cut_fee' =>0, 'num' => 0);

        $this->assign('cartList', $result['cartList']); // 购物车的商品
        $this->assign('total_price', $result['total_price']); // 总计
        $this->display();
    }

    /*
     * ajax 获取用户收货地址 用于购物车确认订单页面
     */
    public function ajaxAddress(){                               
        $address_list = M('UserAddress')->where("user_id = {$this->user_id}")->select();
        if($address_list){
            $area_id = array();
            foreach ($address_list as $val){
                $area_id[] = $val['province'];
                        $area_id[] = $val['city'];
                        $area_id[] = $val['district'];
                        $area_id[] = $val['twon'];                        
            }    
                $area_id = array_filter($area_id);
            $area_id = implode(',', $area_id);
            $regionList = M('region')->where("id in ($area_id)")->getField('id,name');
            $this->assign('regionList', $regionList);
        }
        // dump($address_list);
        $c = M('UserAddress')->where("user_id = {$this->user_id} and is_default = 1")->count(); // 看看有没默认收货地址        
        if((count($address_list) > 0) && ($c == 0)) // 如果没有设置默认收货地址, 则第一条设置为默认收货地址
            $address_list[0]['is_default'] = 1;
                     
        $this->assign('address_list', $address_list);
        $this->display('ajax_address');
    }

    public function add_address(){
        if(IS_POST){ //新增地址
            $data = I('post.');
            $data['user_id'] = $this->user['user_id'];
            $this->address = M('user_address');
            if(!empty($data['subBox'])){
                $where['user_id'] = $this->user['user_id'];
                $where['is_default'] = 1;
                $datas['is_default'] = 0;
                $this->address->where($where)->save($datas);
                $data['is_default'] = 1;
            }
            $res = $this->address->add($data);
            if($res){
                $this->redirect('Index/Shop/cart2');exit;
            }else{
                $this->error('修改失败',U('Index/Shop/cart2'));exit;
            }
        }
        $p = M('region')->where(array('parent_id'=>0,'level'=> 1))->select();
        $this->assign('province',$p);
        $this->display();
    }


}