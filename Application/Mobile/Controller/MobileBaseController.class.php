<?php

namespace Mobile\Controller;

use Common\Base\BaseController;

abstract class MobileBaseController extends BaseController {

    public $user_id     = null;
    public $user        = null;
    public $user_info   = null;

    public $cateTrre = array();

    public $weChatLogic         = null;
    public $weChatConfig        = array();


    abstract function exceptAuthActions();

    /**
     * 初始化操作
     */
    public function _initialize() {
        parent::_initialize();
        //验证部分
        if( !isWeChatBrowser() ){
            if ( !isLoginState() ) {
                if( $this -> needAuth() ){
                    $redirectedUrl = session("redirectedUrl");
                    if( empty( $redirectedUrl ) ){
                        session("redirectedUrl",$_SERVER["REQUEST_SCHEME"].'://'.$_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"]) ;
                    }
//                echo "请在微信端访问！";exit;
                    header("location:".U('Mobile/User/login'));
                    exit;
                }
            }
        }

        $this -> user_id = session(__UserID__);
        $user_info = get_user_info($this -> user_id);
        if(!empty($user_info)){
            $this -> user_info  = $user_info;
            $this -> user  = $this -> user_info;
            if( empty($user_info["last_login"]) || $user_info["last_login"] < strtotime(date('Y-m-d',time())) ){
                //登录送积分
                increasePoints("login", $this->user_id);
                saveData("users",array("user_id"=>$this -> user_id),array("last_login"=>time()));
            }
            $this -> assign('user',$this -> user_info );
            $this -> assign('auth',true);
        }
        if( isWeChatBrowser() ){

            $this -> weChatLogic    = new \Common\Logic\WeChatLogic();
            $this -> weChatConfig   = $this -> weChatLogic -> weChatConfig;

            $this -> weChatLogic -> authorization();
            $this -> assign('wechat_config', $this->weChatConfig);

            $signPackage = $this -> weChatLogic -> getSignPackage();
            $this -> assign('signPackage', $signPackage);

        }else{
            /**
             * 普通手机页面入口
             */
        }



        $this -> public_assign();
    }

    /**
     * 保存公告变量到 smarty中 比如 导航
     */
    public function public_assign()
    {
        //用户上次访问时间
//        $push_message_time = push_message_time($this->user_id);
//        $this -> assign('push_message_time',$push_message_time);

//        $mobileMessage = cookie("mobileMessage");
//        if ( !empty($mobileMessage) ){
//            $this -> assign('mobileMessage', $mobileMessage);
////            if( cookie('haveMobileMessage') == 1 ){
////                session( "mobileMessage" , null );
////            }
//        }


        $this -> assign('lmshop_config', $this -> shopConfig);

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






}