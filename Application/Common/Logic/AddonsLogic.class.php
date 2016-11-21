<?php

namespace Common\Logic;

use Common\Logic\Base\BaseLogic;

/**
 * 插件逻辑
 * Class AddonsLogic
 * @package Common\Logic
 */
class AddonsLogic extends BaseLogic
{

    public $addonsConfig = null;
    public $classController = null;

    private $classPath = null;
    private $addonsName = null;
    private $actionName = null;


    public function __construct()
    {
        parent::__construct("config");
    }

    /**
     * 获取插件配置
     * @return null
     */
    public function getAddonsConfig(){
        return $this -> addonsConfig;
    }


    /**
     * 加载插件模板
     * @param $addonsName
     * @param string $actionName
     * @param string $module
     * @param array $data
     */
    public function loadAddons( $addonsName , $actionName = "index" , $module = "Mobile" , $data = array() ){

        $this -> addonsName = $addonsName;
        $this -> actionName = $actionName;
        $this -> classPath = 'Addons/'.$addonsName.'/';

        @include_once $this -> classPath . 'Core' . $module.'.class.php';
        $this -> addonsConfig = @include_once $this -> classPath . "config.ini.php";

        if( ! file_exists( $this -> classPath . "install.lock" ) ){
            die("Not Install This Addons");
        }

        try{
            $className = $addonsName . $module ."Controller";
            if( class_exists($className) ){
                $this -> classController =  new $className( $data );
            }else{
                die("Not Find This Addons");
            }
        }catch (\Exception $e) {
            die("Not Find This Addons");
        }
    }

    /**
     * 执行插件代码
     * @return array
     */
    public function run(){

        $actionName = $this -> actionName;
        $data = $this -> classController -> $actionName();
        return is_array($data) ? $data : array();
    }

    public function install(){
        if( file_exists( $this -> classPath . "install.lock" ) ){
            die("请勿重复安装！");
        }

    }

    public function uninstall(){

    }

}