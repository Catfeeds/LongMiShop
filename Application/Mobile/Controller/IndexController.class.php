<?php

namespace Mobile\Controller;
class IndexController extends MobileBaseController {

    function exceptAuthActions()
    {
        return array(
            "index",
            "goodsList"
        );
    }

    public function index(){
        $hot_goods = M('goods')->where("is_hot=1 and is_on_sale=1")->order('goods_id DESC')->limit(20)->cache(true,MY_CACHE_TIME)->select();//首页热卖商品
        $thems = M('goods_category')->where('level=1')->order('sort_order')->limit(9)->cache(true,MY_CACHE_TIME)->select();
        $this->assign('thems',$thems);
        $this->assign('hot_goods',$hot_goods);
        $favourite_goods = M('goods')->where("is_recommend=1 and is_on_sale=1")->order('goods_id DESC')->limit(20)->cache(true,MY_CACHE_TIME)->select();//首页推荐商品
        $this->assign('favourite_goods',$favourite_goods);
        $this->display();
    }

//    /**
//     * 分类列表显示
//     */
//    public function categoryList(){
//        $this->display();
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
        $this->assign('lists',$lists);
        $this->display();
    }
//
//    public function ajaxGetMore(){
//    	$p = I('p',1);
//    	$favourite_goods = M('goods')->where("is_recommend=1 and is_on_sale=1")->order('goods_id DESC')->page($p,10)->cache(true,MY_CACHE_TIME)->select();//首页推荐商品
//    	$this->assign('favourite_goods',$favourite_goods);
//    	$this->display();
//    }
}