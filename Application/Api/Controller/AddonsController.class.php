<?php

namespace Api\Controller;

class AddonsController extends BaseController {


    const APPOINTED = "Api";

    /**
     * 析构流函数
     */
    public function  __construct() {   
        parent::__construct();
        setLogResult( "支付33" , "支付33" , "test");

    }


    /**
     * 跳过报错
     */
    public function  _empty(){

        setLogResult( "支324付33" , "2344", "test");
        $pluginName = I( "pluginName" , "index" );
        $addonsLogic = new \Common\Logic\AddonsLogic();
        $addonsLogic -> loadAddons( ACTION_NAME , $pluginName , self::APPOINTED  );
        $addonsLogic -> run() ;
    }

}
