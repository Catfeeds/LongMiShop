<?php

namespace Wap\Controller;
use Think\Controller;

abstract class WapBaseController extends Controller {

    public $user_id     = null;
    public $user        = null;
    public $user_info   = null;
    public $session_id   = null;

    public $shopConfig          = array();
    public $weChatLogic         = null;
    public $weChatConfig        = array();


    abstract function exceptAuthActions();

    /**
     * 初始化操作
     */
    public function _initialize() {

        $this -> shopConfig = getShopConfig();
//        if( !isWeChatBrowser() ){
//            exit;
//        }
//        //验证部分
//        if( !isWeChatBrowser() ){
//            if ( !isLoginState() ) {
//                if( $this -> needAuth() ){
//                    $redirectedUrl = session("redirectedUrl");
//                    if( empty( $redirectedUrl ) ){
//                        session("redirectedUrl",$_SERVER["REQUEST_SCHEME"].'://'.$_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"]) ;
//                    }
////                echo "请在微信端访问！";exit;
//                    header("location:".U('Mobile/User/login'));
//                    exit;
//                }
//            }
//        }
//
        $this -> session_id = session_id();
        $this -> user_id = session(__UserID__);
        $userLogic = new \Common\Logic\UsersLogic();
        $user_info = $userLogic -> get_info($this -> user_id);
        if(!empty($user_info['result'])){
            $this -> user_info  = $user_info['result'];
            $this -> user  = $this -> user_info;
        }
//        if( isWeChatBrowser() ){
//
//            $this -> weChatLogic    = new \Common\Logic\WeChatLogic();
//            $this -> weChatConfig   = $this -> weChatLogic -> weChatConfig;
//
//            $this -> weChatLogic -> authorization();
//            $this -> assign('wechat_config', $this->weChatConfig);
//
//            $signPackage = $this -> weChatLogic -> getSignPackage();
//            $this -> assign('signPackage', $signPackage);
//
//        }else{
//            /**
//             * 普通手机页面入口
//             */
//        }
//
//
//
//        $this -> public_assign();
    }


    protected function needAuth(){
        if ($this->exceptAuthActions() == null) {
            return true;
        }
        if (in_array(ACTION_NAME, $this->exceptAuthActions())) {
            return false;
        }
        return true;
    }



    /**
     * 跳过报错
     */
    public function  _empty($method,$args){
        dd($method);
//        setLogResult();$method,$args);
        exit();
    }



}