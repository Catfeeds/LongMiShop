<?php

namespace Wap\Controller;
class GameController extends WapBaseController {

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