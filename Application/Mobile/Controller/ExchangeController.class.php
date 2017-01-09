<?php

namespace Mobile\Controller;

use \Common\Logic\BuyLogic;

class ExchangeController extends MobileBaseController {

    function exceptAuthActions()
    {
        return array(
            "checkExchangeCode",
            "createOrder"
        );
    }

    public function _initialize() {
        parent::_initialize();
    }

    /**
     * 兑换码页面
     */
    public function exchange(){
        $this -> display();
    }


    public function redeemCode(){
        $orderNumber = getCountWithCondition("order",array('pay_status' => "1","user_id"=>$this->user_id));
//        $orderNumber = 2;
        if( $orderNumber > 1){
            $is_get = true;
            $condition = array(
                "gift_coupon_id" => 3,
                "user_id"        => $this->user_id,
                "state"          => array('neq', 0),
            );
            if( getCountWithCondition("coupon_code" ,$condition) > 0 ){
                $is_change = true;
            }else{
                $is_change = false;
                $condition = array(
                    "state"=>"0",
                    "gift_coupon_id" => "3",
                );
                $codeInfo = findDataWithCondition("coupon_code",$condition);
                if( empty($codeInfo)){
                    $is_have = false;
                }else{
                    $is_have = true;
                    $this -> assign( 'codeInfo' , $codeInfo );
                }
                $this -> assign( 'is_have' , $is_have );
            }
            $this -> assign( 'is_change' , $is_change );


        }else{
            $is_get = false;
        }
        $this -> assign( 'is_get' , $is_get );
        $this -> display();
    }


    /**
     * 兑换码检测 AJAX
     */
    public function checkExchangeCode(){
        if( !isLoginState() ){
            exit(json_encode(callback(false,"尚未登录")));
        }
        if( IS_POST ){
            $code = I( 'code' , 0 );
            $result = checkCode( $code );
            if( callbackIsTrue( $result ) ){
                session("exchangeCode" , $code);
            }
            exit(json_encode($result));
        }
        exit(json_encode(callback(false,"错误访问")));
    }


    /**
     * 兑换码详情
     */
    public function exchangeInfo(){
        $code = session("exchangeCode");

        $result = checkCode( $code );

        if( !callbackIsTrue($result) ){
            $this -> error( getCallbackMessage($result) , U("Mobile/Exchange/exchange"));
            exit;
        }

        $goodsList =  getExchangeGoodsList( $code );
        if( empty($goodsList) ){
            if( !gainCouponWithCode( $code , $this->user_id) ){
                $this -> error( "礼品获取失败" , U("Mobile/Exchange/exchange") );
                exit;
            }else{
                $this -> assign( 'couponInfo' , getCouponInfoWithCode( $code ) );
                $this -> assign( 'isGetCoupon' , true );
                $this -> display();
                exit;
            }
        }

        $address = getCurrentAddress( $this->user_id , I('address_id',null) );
        addressTheJump(ACTION_NAME);
        if( empty($address) ){
            header("Location: ".U('Mobile/User/edit_address',array('source'=>'exchange')));
        }

        $this -> assign( 'address' , $address );
        $this -> assign( 'exchangeCode' , $code );
        $this -> assign( 'goodsList' , $goodsList );
        $this -> assign( 'region_list' , get_region_list() );
        $this -> display();
    }


    /**
     * 创建订单
     */
    public function createExchangeOrder(){
        $bugLogic = new BuyLogic();
        $result = $bugLogic -> createExchangeOrder();
        if ( callbackIsTrue( $result ) ){
            session("exchangeCode" , null);
        }
        exit(json_encode($result));
    }

}