<?php
namespace Index\Controller;

class HelpController extends IndexBaseController {

    function exceptAuthActions()
    {
        return array(
            "buy",
            "pay",
            "postage",
            "about",
            "contact",
            "join",
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
    public function about(){
        $this -> display();
    }
    public function contact(){
        $this -> display();
    }
    public function join(){
        $this -> display();
    }




}