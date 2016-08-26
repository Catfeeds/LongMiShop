<?php
namespace Index\Controller;

use Common\Logic\BuyLogic;
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
        $this->display();
    }

    public function cart(){
        $buy_logic  = new BuyLogic();
        $buy_logic -> createOrder();

        exit;
        $this->display();
    }

    public function cart2(){
        $this->display();
    }



}