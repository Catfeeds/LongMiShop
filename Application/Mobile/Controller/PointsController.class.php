<?php
namespace Mobile\Controller;
class PointsController extends MobileBaseController {


    function exceptAuthActions()
    {
        return array(
            "index"
        );
    }

    public function  _initialize() {
        parent::_initialize();
    }


    public function index(){

        $this -> display();
    }
}
