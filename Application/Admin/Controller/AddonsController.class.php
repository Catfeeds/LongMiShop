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

    public function install(){
        $addonsName = I( "addonsName" );
        if( empty( $addonsName ) ){
            exit;
        }
        $sqlPath = "./Addons/" . $addonsName ."/install.sql";
        try{
            $Model = new \Think\Model();
            $Model->execute( $sqlPath );
        }catch (\Exception $e) {
            die("Sql die");
        }
    }







    /**
     * 插件初始化
     */
    private function _init(){
        $this -> pluginName = I( "pluginName" , "index" );
        $this -> addonsLogic = new \Common\Logic\AddonsLogic();
        $this -> addonsLogic -> loadAddons( ACTION_NAME , $this -> pluginName , self::APPOINTED );

        $this -> addonsConfig  = $this -> addonsLogic -> getAddonsConfig();
        $dataList = $this -> addonsLogic -> run() ;
        if( !empty( $dataList ) ){
            foreach ( $dataList as $dataKey => $dataItem ){
                $this -> assign( $dataKey , $dataItem );
            }
        }
        C( "TMPL_PARSE_STRING.__ADDONS__" , '/Addons/' . ACTION_NAME . '/Static' );
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