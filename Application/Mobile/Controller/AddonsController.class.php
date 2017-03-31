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
        return array(
            "tweetQRCode"
        );
    }

    public function  _initialize() {
        parent::_initialize();
        $this -> _init();
    }

    /**
     * 初始化
     */
    private function _init(){

        $this -> pluginName = I( "pluginName" , null );
        $this -> pluginName = is_null($this -> pluginName) ? I( "pN" , "index" ) : $this -> pluginName;


        $this -> addonsLogic = new AddonsLogic();
        $this -> addonsLogic -> loadAddons( ACTION_NAME , $this -> pluginName , self::APPOINTED , $this -> user_info );
        
        $this -> addonsConfig  = $this -> addonsLogic -> getAddonsConfig();
        $dataList = $this -> addonsLogic -> run() ;
        $theme = self::THEME;
        if( !empty( $dataList ) ){
            foreach ( $dataList as $dataKey => $dataItem ){
                if( $dataKey == "__success"){
                    $this -> success( $dataItem["msg"] , $dataItem["url"] , $dataItem["time"]  );
                    exit;
                }
                if( $dataKey == "__error"){
                    $this -> error( $dataItem["msg"] , $dataItem["url"] , $dataItem["time"]  );
                    exit;
                }
                if( $dataKey == "__theme"){
                    $theme = $dataItem;
                    continue;
                }

                $this -> assign( $dataKey , $dataItem );
            }
        }
        C( "TMPL_PARSE_STRING.__ADDONS__" , '/Addons/' . ACTION_NAME . '/Static' );
        $viewPath = "./Addons/".ACTION_NAME."/Template/" . self::APPOINTED . "/" . $theme . "/Addons_" . $this -> pluginName .".html" ;
        $this -> view -> display($viewPath);
    }

    /**
     * 跳过报错
     */
    public function  _empty(){}


    public function tweetQRCode(){
        header("Location: http://mp.weixin.qq.com/s/ksHv0QFtJEOAUJOv0QNTNQ");
        exit;
    }

}