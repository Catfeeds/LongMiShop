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


        $inviteData = getGiftInfo( $this -> shopConfig['prize_invite_value'] , $this -> shopConfig['prize_invite'] );
        $inviteData = getCallbackData($inviteData);
        $inviteNumber = getCountWithCondition("invite_list" ,array('parent_user_id'=>$this->user_id));
        if( $inviteNumber > 0){
            $inviteNumber += $inviteNumber *$inviteData['point'];
            $inviteNumber += $inviteNumber *$inviteData['balance'];
            $inviteNumber += $inviteNumber *$inviteData["coupon"]['money'];
        }
        $this -> assign('inviteNumber',$inviteNumber);


        $newGoods = M("goods")->where(array("is_new" => 1))->order("sort")->limit('2')->select();

        $this->assign('newGoods', $newGoods);

        $favourite_goods = M('goods') -> where("is_recommend=1 and is_on_sale=1")->order('goods_id DESC')->limit(20)->cache(true,MY_CACHE_TIME)->select();//首页推荐商品
        $this -> assign('favourite_goods',$favourite_goods);




        $condition = array(
            'user_id'    => $this->user_id,   // 用户id
        );
        if( !$this->user_id ){
            $condition['session_id'] = $this->session_id;
        }
        $cart_data = M('cart')->where($condition)->select();
        if(!empty($cart_data)){
            $cart_data2 = array();
            foreach ($cart_data as $cart_data_item){
                $cart_data2[$cart_data_item['goods_id']."_".intval($cart_data_item['key'])] = $cart_data_item;
            }
            $cart_data = $cart_data2;
        }
        $this->assign('cart_data', $cart_data);

        
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