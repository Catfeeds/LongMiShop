<?php
namespace Index\Controller;

class ShopController extends BaseIndexController {

    function exceptAuthActions()
    {
        return array(
            'index',
//            'cart',
        );
    }

    public function _initialize() {
        parent::_initialize();
    }

    public function index(){
        $this->display();
    }

    public function cart(){
        $this->display();
    }

    public function cart2(){
        $this->display();
    }



}