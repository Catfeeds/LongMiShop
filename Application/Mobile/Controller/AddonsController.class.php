<?php

namespace Mobile\Controller;

use Common\Logic\AddonsLogic;

class AddonsController extends MobileBaseController {

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

        C( "TMPL_FILE_DEPR" , "_" );
        C( "VIEW_PATH" , "./Addons/".ACTION_NAME."/Template/Mobile/" );
        C( "DEFAULT_THEME" , "default" );
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
        $this -> view -> display($this -> pluginName);
    }


    /**
     * 跳过报错
     */
    public function  _empty(){}


}