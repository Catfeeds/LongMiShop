<?php
namespace Index\Controller;


class IndexController extends BaseIndexController {

    function exceptAuthActions()
    {
        return array(
            'index'
        );
    }

    public function _initialize() {
        parent::_initialize();
    }

    public function index(){
    	$this->display();
    }



}