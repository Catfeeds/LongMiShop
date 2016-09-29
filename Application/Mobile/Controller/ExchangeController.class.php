<?php

namespace Mobile\Controller;


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

    public function exchange(){
        $this->display();
    }

    public function checkExchangeCode(){
        if( !isLoginState() ){
            exit(json_encode(callback(false,"尚未登录")));
        }
        if( IS_POST ){
            $code = I( 'code' , 0 );
            $result = checkCode( $code );
            exit(json_encode($result));
        }
        exit(json_encode(callback(false,"错误访问")));
    }

    public function exchangeInfo(){
        $code = I( 'code' , 0 );
        $result = checkCode( $code );
        if( !callbackIsTrue($result) ){
            $this -> error( getCallbackMessage($result) );
            exit;
        }
        $region_list = get_region_list();
        $this->assign('region_list',$region_list);
        $address = getCurrentAddress( $this->user_id , I('address_id',null) );
        addressTheJump(ACTION_NAME);
        if( empty($address) ){
            header("Location: ".U('Mobile/User/edit_address',array('source'=>'exchange')));
        }
        $this->assign('exchangeCode',$code);
        $this->assign('region_list',$region_list);
        $this->assign('address',$address);
        $this->display();
    }

    public function createExchangeOrder(){
        $bugLogic = new \Common\Logic\BuyLogic();
        $result = $bugLogic -> createExchangeOrder();
        exit(json_encode($result));
    }

}