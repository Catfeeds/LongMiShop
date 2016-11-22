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
        $viewPath = "./Addons/".ACTION_NAME."/Template/" . self::APPOINTED . "/" . self::THEME . "/Addons_" . $this -> pluginName  .".html" ;

        $templatePath = C("VIEW_PATH").C("DEFAULT_THEME");
        $this -> view -> display($templatePath."/Public/min-header.html");
        echo "<div class=\"wrapper\">";
        $this -> view -> display($templatePath."/Public/breadcrumb.html");
        echo "<section class=\"content\">";
        echo "<div class=\"container-fluid\">";
        echo "<div class=\"pull-right\">";
        echo "<a href=\"javascript:history.go(-1)\" data-toggle=\"tooltip\" title=\"\" class=\"btn btn-default\" data-original-title=\"返回\"><i class=\"fa fa-reply\"></i></a>";
        echo "</div>";
        echo "<div class=\"panel panel-default\">";
        echo "<div class=\"panel-heading\">";
        echo "<h3 class=\"panel-title\"><i class=\"fa fa-list\"></i>设置</h3>";
        echo "</div>";
        echo "<div class=\"panel-body\">";
        $this -> view -> display($viewPath);
        echo "</div>";
        echo "</div>";
        echo "</div>";
        echo "</section>";
        echo "</div>";
        echo "</body>";
        echo "</html>";
    }

    /**
     * 跳过报错
     */
    public function  _empty(){
        $this -> _init();
    }


}