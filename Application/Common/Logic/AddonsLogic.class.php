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

//        @include $this -> classPath .'function/base.php';
        @include $this -> classPath . 'Core' . $module.'.class.php';
        $mainJson = @file_get_contents($this -> classPath . "main.json");
        $this -> addonsConfig = @json_decode( $mainJson , true );

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
            die($e->getMessage());
        }
    }

    /**
     * 执行插件代码
     * @return array
     */
    public function run(){

        $actionName = $this -> actionName;
        $cls_methods = get_class_methods( $this -> classController );
        if( in_array( $actionName , $cls_methods )){
            $data = $this -> classController -> $actionName();
            return is_array($data) ? $data : array();
        }
        die("Not Find This Addons Action");
    }

    public function install(){
        if( file_exists( $this -> classPath . "install.lock" ) ){
            die("请勿重复安装！");
        }

    }

    public function uninstall(){

    }

}