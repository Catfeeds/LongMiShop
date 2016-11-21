<?php

namespace Mobile\Controller;

use Common\Logic\AddonsLogic;

class AddonsController extends MobileBaseController {

    const THEME = "default";
    const APPOINTED = "Mobile";

    public $pluginName = null;

    private $addonsLogic = null;
    private $addonsConfig = null;

    function exceptAuthActions()
    {
        return null;
    }

    public function  _initialize() {
        parent::_initialize();
        $this -> _init();
    }

    /**
     * 初始化
     */
    private function _init(){

        $this -> pluginName = I( "pluginName" , "index" );
        $this -> addonsLogic = new AddonsLogic();
        $this -> addonsLogic -> loadAddons( ACTION_NAME , $this -> pluginName , "Mobile" , $this -> user_info );

        C( "TMPL_PARSE_STRING.__ADDONS__" , '/Addons/' . ACTION_NAME . '/Static' );
        
        $this -> addonsConfig  = $this -> addonsLogic -> getAddonsConfig();
        $dataList = $this -> addonsLogic -> run() ;
        if( !empty( $dataList ) ){
            foreach ( $dataList as $dataKey => $dataItem ){
                $this -> assign( $dataKey , $dataItem );
            }
        }

        $this -> display();
    }


    /**
     * 重新定义 display 方法
     */
    protected function display() {
        $viewPath = "./Addons/".ACTION_NAME."/Template/" . self::APPOINTED . "/" . self::THEME . "/Addons_" . $this -> pluginName . ".html";
        $this -> view -> display($viewPath);
    }


    /**
     * 跳过报错
     */
    public function  _empty(){}


}