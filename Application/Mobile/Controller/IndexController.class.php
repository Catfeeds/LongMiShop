<?php

namespace Mobile\Controller;
class IndexController extends MobileBaseController {

    function exceptAuthActions()
    {
        return array(
            "index",
            "recommendPolite"
        );
    }

    public function  _initialize() {
        parent::_initialize();
    }
    public function index()
    {
        $this->display();
    }
    public function sendRed()
    {
        dd(sendWeChatRed("owjy5v4020Mh7yNAT0aVapESwqNM",1));
//        $this->display();
    }
    public function recommendPolite(){
        $this -> display();
    }
    //获取新券
    public function getSendNewsCoupon(){

        $sendNewsCouponsId = M('config') -> where(array('name' => 'send_news_coupons_id'))->getField("value");
        $condition = array(
            "uid"=>$this->user_id,
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