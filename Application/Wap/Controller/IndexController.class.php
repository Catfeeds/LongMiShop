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

        $data = array(
            "top_menu" => array(
                "userMoney" => 0,
                "couponCount" => 0,
                "activityCount" => 2,
                "inviteNumber" => 0
            ),
        );
        $condition['session_id'] = $this->session_id;

        if( $this->user_id ){

            $condition['user_id'] = $this->user_id;

            $data["top_menu"]["userMoney"] = $this->user['user_money'] ? $this->user['user_money'] : 0 ;

            $usersLogic = new \Common\Logic\UsersLogic();
            $result = $usersLogic -> getCoupon( $this->user_id);
            $data["top_menu"]["couponCount"] =  count( $result['data']['count'] );

            $data["top_menu"]["activityCount"] =  2;

            $data["top_menu"]["inviteNumber"] = getCountWithCondition("invite_list" ,array('parent_user_id'=>$this->user_id));

        }

        $data["newGoods"]["item"]=  M("goods")->where(array("is_new" => 1))->order("sort")->limit('2')->select();

        $cart_data = M('cart')->where($condition)->select();
        if(!empty($cart_data)){
            $cart_data2 = array();
            foreach ($cart_data as $cart_data_item){
                $cart_data2[$cart_data_item['goods_id']."_".intval($cart_data_item['key'])] = $cart_data_item;
            }
            $cart_data = $cart_data2;
        }

        $items = M('goods') -> where("is_recommend=1 and is_on_sale=1")->order('goods_id DESC')->limit(20)->cache(true,MY_CACHE_TIME)->select();//首页推荐商品
        foreach ( $items as $key => $item){
            $items[$key]['cartNumber'] = $cart_data[$item['goods_id'].'_0']['goods_num']?$cart_data[$item['goods_id'].'_0']['goods_num']:0 ;
        }
        $data["favouriteGoods"]["item"] = $items;



        $pid =2;
        $ad_position = M("ad_position")->cache(true,MY_CACHE_TIME)->getField("position_id,position_name,ad_width,ad_height");
        $ad = D("ad") -> where("pid=$pid  and enabled = 1 and start_time < ".time()." and end_time > ".time()." ")->order("orderby desc")->cache(true,MY_CACHE_TIME)->limit("5")->select();
        foreach($ad as $key=>$v){
            $ad[$key]['position'] = $ad_position[$v['pid']];
        }

        $data["ad"] = array(
            "item" => $ad,
            "count" => count($ad) - 1 ,
        );

        printJson(true,"",$data);
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