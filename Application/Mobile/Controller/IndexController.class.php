<?php

namespace Mobile\Controller;
class IndexController extends MobileBaseController {

    function exceptAuthActions()
    {
        return array(
            "index",
            "index2",
            "goodsList"
        );
    }

    public function  _initialize() {
        parent::_initialize();
    }
    public function index()
    {

        $usersLogic = new \Common\Logic\UsersLogic();
        $result = $usersLogic -> getCoupon( $this->user_id);
        $this -> assign('couponCount', $result['data']['count']);

        $newGoods = M("goods")->where(array("is_new" => 1))->order("sort")->limit('2')->select();
//        $this -> assign('newGoodsNumber',count($newGoods));
        $this->assign('newGoods', $newGoods);
//
//        $hotGoods = M("goods") -> where(array("is_hot"=>1)) -> order("sort" ) -> limit('6') -> select();
//        $this -> assign('hotGoods',$hotGoods);
//
//        $hot_goods = M('goods') -> where("is_hot=1 and is_on_sale=1")->order('goods_id DESC')->limit(20)->cache(true,MY_CACHE_TIME)->select();//首页热卖商品
//        $thems = M('goods_category') -> where('level=1')->order('sort_order')->limit(9)->cache(true,MY_CACHE_TIME)->select();
//        $this -> assign('thems',$thems);
//        $this -> assign('hot_goods',$hot_goods);
        $favourite_goods = M('goods') -> where("is_recommend=1 and is_on_sale=1")->order('goods_id DESC')->limit(20)->cache(true,MY_CACHE_TIME)->select();//首页推荐商品
        $this -> assign('favourite_goods',$favourite_goods);

//
//        $goods_id = 1;
////        $goods_id = 2;
//        $goods = findDataWithCondition("goods", array("goods_id" => $goods_id));
//
        $condition = array(
            'user_id'    => $this->user_id,   // 用户id
            'session_id' => $this->session_id,   // sessionid
        );
        $cart_data = M('cart')->where($condition)->select();
//        $spec_goods_price = selectDataWithCondition('spec_goods_price', array("goods_id" => $goods_id));
//        foreach ($spec_goods_price as $spec_goods_price_key => $spec_goods_price_item) {
//            $img = "";
//            switch ($spec_goods_price_item['key']) {
//                case "49":
//                    $img = "goods1_pic_sz.jpg";
//                    break;
//                case "50":
//                    $img = "goods1_pic_me.jpg";
//                    break;
//                case "51":
//                    $img = "goods1_pic_xm.jpg";
//                    break;
//                case "52":
//                    $img = "goods1_pic_ld.jpg";
//                    break;
//                case "53":
//                    $img = "goods1_pic_lr.jpg";
//                    break;
//                case "54":
//                    $img = "goods1_pic_xys.jpg";
//                    break;
//                case "55":
//                    $img = "goods1_pic_lm.jpg";
//                    break;
//            }
//            $spec_goods_price[$spec_goods_price_key]["img"] = $img;
//        }
//
//        $this->assign('spec_goods_price', $spec_goods_price);
        $this->assign('cart_data', $cart_data);
//        $this->assign('goods_id', $goods_id);
//        $this->assign('goods', $goods);
        $this->display();
    }

    public function index2(){
        $this -> display();
    }
//    /**
//     * 分类列表显示
//     */
//    public function categoryList(){
//        $this -> display();
//    }

//    /**
//     * 模板列表
//     */
//    public function mobanlist(){
//        $arr = glob("D:/wamp/www/svn_tpshop/mobile--html/*.html");
//        foreach($arr as $key => $val)
//        {
//            $html = end(explode('/', $val));
//            echo "<a href='http://www.php.com/svn_tpshop/mobile--html/{$html}' target='_blank'>{$html}</a> <br/>";
//        }
//    }
    
    /**
     * 商品列表页
     */
    public function goodsList(){
        $goodsLogic = new \Common\Logic\GoodsLogic(); // 前台商品操作逻辑类
        $id = I('get.id',0); // 当前分类id
        $lists = getCatGrandson($id);
        $this -> assign('lists',$lists);
        $this -> display();
    }
//
//    public function ajaxGetMore(){
//    	$p = I('p',1);
//    	$favourite_goods = M('goods') -> where("is_recommend=1 and is_on_sale=1")->order('goods_id DESC')->page($p,10)->cache(true,MY_CACHE_TIME)->select();//首页推荐商品
//    	$this -> assign('favourite_goods',$favourite_goods);
//    	$this -> display();
//    }
}