<?php
namespace Index\Controller;


class IndexController extends IndexBaseController {

    function exceptAuthActions()
    {
        return array(
            'index',
            'test'
        );
    }

    public function _initialize() {
        parent::_initialize();
    }

    public function index(){
    	$this->display();
    }

    public function test(){
        $messageData = array(
            "orderSn" => "4236842368",
        );
        sendWeChatMessage( $_SESSION['openid'] , "下单" ,$messageData  );

    }
}