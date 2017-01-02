<?php

namespace Wap\Controller;
class IndexController extends WapBaseController {

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
//        M("sers")->find();
//        $couponCount = 0 ;
//        if( $this->user_id ){
//            $usersLogic = new \Common\Logic\UsersLogic();
//            $result = $usersLogic -> getCoupon( $this->user_id);
//            $couponCount =  $result['data']['count'];
//        }
//
//
//        $this -> assign('couponCount', $couponCount);
//
////        $inviteData = getGiftInfo( $this -> shopConfig['prize_invite_value'] , $this -> shopConfig['prize_invite'] );
////        $inviteData = getCallbackData($inviteData);
//        $inviteNumber = getCountWithCondition("invite_list" ,array('parent_user_id'=>$this->user_id));
////        if( $inviteNumber > 0){
////            $inviteNumber += $inviteNumber *$inviteData['point'];
////            $inviteNumber += $inviteNumber *$inviteData['balance'];
////            $inviteNumber += $inviteNumber *$inviteData["coupon"]['money'];
////        }
//        $this -> assign('inviteNumber',$inviteNumber);
//
//
//        $newGoods = M("goods")->where(array("is_new" => 1))->order("sort")->limit('2')->select();
//
//        $this->assign('newGoods', $newGoods);
//
//        $favourite_goods = M('goods') -> where("is_recommend=1 and is_on_sale=1")->order('goods_id DESC')->limit(20)->cache(true,MY_CACHE_TIME)->select();//首页推荐商品
//        $this -> assign('favourite_goods',$favourite_goods);
//
//
//
//
//        $condition = array(
//            'user_id'    => $this->user_id,   // 用户id
//        );
//        if( !$this->user_id ){
//            $condition['session_id'] = $this->session_id;
//        }
//        $cart_data = M('cart')->where($condition)->select();
//        if(!empty($cart_data)){
//            $cart_data2 = array();
//            foreach ($cart_data as $cart_data_item){
//                $cart_data2[$cart_data_item['goods_id']."_".intval($cart_data_item['key'])] = $cart_data_item;
//            }
//            $cart_data = $cart_data2;
//        }
//        $this->assign('cart_data', $cart_data);
//
//
        $this->display();
    }

    public function index2(){
        $this -> display();
    }

    public function recommendPolite(){
        $this -> display();
    }


    //获取新券
    public function getSendNewsCoupon(){

        $sendNewsCouponsId = M('config') -> where(array('name' => 'send_news_coupons_id'))->getField("value");
        $condition = array(
            "user_id"=>$this->user_id,
            "cid"=>$sendNewsCouponsId
        );
        if( time() -  $this->user['reg_time'] > 60 * 60 * 5  ){
            header("Location: ".U("Mobile/User/index"));
            exit;
        }
        if( $sendNewsCouponsId > 0 && isExistenceDataWithCondition("coupon",array("id"=>$sendNewsCouponsId)) && !isExistenceDataWithCondition("coupon_list",$condition)){
            addNewCoupon( $sendNewsCouponsId , $this->user_id);
        }

        header("Location: ".U("Mobile/User/coupon"));
        exit;

    }
}