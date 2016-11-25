<?php

namespace Api\Controller;

class AddonsController  {


    const APPOINTED = "Api";

    /**
     * 析构流函数
     */
    public function  __construct() {   
//        parent::__construct();

    }


    /**
     * 跳过报错
     */
    public function  _empty(){

        $pluginName = I( "pluginName" , "index" );
        $addonsLogic = new \Common\Logic\AddonsLogic();
        $addonsLogic -> loadAddons( ACTION_NAME , $pluginName , self::APPOINTED  );
        $addonsLogic -> run() ;
    }

}
