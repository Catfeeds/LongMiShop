<?php
namespace Admin\Controller;

class AddonsController extends BaseController {

    const THEME = "default";
    const APPOINTED = "Admin";
    const ADDONS_PATH = "./Addons";

    public $pluginName = null;

    private $addonsLogic = null;
    private $addonsConfig = null;

    public function index(){
        $list = getAddonsList( self::ADDONS_PATH );
        $this -> assign( 'list' , $list );

        $this -> display();
    }
    /**
     * 初始化
     */
    private function _init(){
        $this -> pluginName = I( "pluginName" , "index" );
        $this -> addonsLogic = new \Common\Logic\AddonsLogic();
        $this -> addonsLogic -> loadAddons( ACTION_NAME , $this -> pluginName , self::APPOINTED );

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
    public function  _empty(){
        $this -> _init();
    }


}