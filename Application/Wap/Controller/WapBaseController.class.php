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
        if ( !isLoginState() ) {
            if( $this -> needAuth() ){
                printJson(-1,"请先登录后再操作");
            }
        }
        $this -> shopConfig = getShopConfig();
        $this -> session_id = session_id();
        $this -> user_id = session(__UserID__);
        $user_info = get_user_info($this -> user_id);
        if(!empty($user_info)){
            $this -> user_info  = $user_info;
            $this -> user  = $this -> user_info;
        }
    }


    /**
     * 是否需要登录
     * @return bool
     */
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