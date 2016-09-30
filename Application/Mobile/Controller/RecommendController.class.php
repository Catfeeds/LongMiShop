<?php

namespace Mobile\Controller;

class RecommendController extends MobileBaseController {

    function exceptAuthActions()
    {
        return array(
            'index',
        );
    }
    public function  _initialize() {
        parent::_initialize();
    }

    public function index(){
        $this -> display();
    }

    public function recommendList(){

        $this -> assign('list',"");
        $this -> display();
    }

    public function share(){

        $this -> display();
    }

}