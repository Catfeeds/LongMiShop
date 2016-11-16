<?php

namespace Mobile\Controller;
class GameController extends MobileBaseController {

    function exceptAuthActions()
    {
        return array(
            "changeIndex",
        );
    }

    public function  _initialize() {
        parent::_initialize();
    }
    public function changeIndex(){
        $this->display();
    }


}