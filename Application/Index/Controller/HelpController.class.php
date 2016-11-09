<?php
namespace Index\Controller;

class HelpController extends IndexBaseController {

    function exceptAuthActions()
    {
        return array(
            "buy",
            "pay",
            "postage",
        );
    }

    public function _initialize() {
        parent::_initialize();
    }



    public function buy(){
        $this -> display();
    }
    public function pay(){
        $this -> display();
    }
    public function postage(){
        $this -> display();
    }




}