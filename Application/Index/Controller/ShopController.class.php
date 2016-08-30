<?php
namespace Index\Controller;

class ShopController extends BaseIndexController {

    function exceptAuthActions()
    {
        return array(
            'index',
        );
    }

    public function _initialize() {
        parent::_initialize();
    }

    public function index(){
        //  form表单提交
        C('TOKEN_ON',true);
        $goodsLogic = new \Common\Logic\GoodsLogic();
        $goods_id = I("get.id");
        $goods_id = 43;
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
        $this->assign('spec_goods_price', json_encode($spec_goods_price,true)); // 规格 对应 价格 库存表
        $this->assign('commentStatistics',$commentStatistics);//评论概览
        $this->assign('goods_attribute',$goods_attribute);//属性值
        $this->assign('goods_attr_list',$goods_attr_list);//属性列表
        $this->assign('filter_spec',$filter_spec);//规格参数
        $this->assign('goods_images_list',$goods_images_list);//商品缩略图
        $this->assign('siblings_cate',$goodsLogic->get_siblings_cate($goods['cat_id']));//相关分类
        $this->assign('look_see',$goodsLogic->get_look_see($goods));//看了又看
        $this->assign('goods',$goods);
        $this->display();
    }

    public function cart(){
        $this->display();
    }

    public function cart2(){
//        $buy_logic  = new BuyLogic();
//        $buy_logic -> createOrder();
        $this->display();
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


}