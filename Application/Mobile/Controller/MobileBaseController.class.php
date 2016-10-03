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
        if ( !isLoginState() ) {
            if( $this -> needAuth() ){
                header("location:".U('Mobile/User/login'));
                exit;
            }
        }

        $this -> user_id = session(__UserID__);
        $userLogic = new \Common\Logic\UsersLogic();
        $user_info = $userLogic -> get_info($this -> user_id);
        if(!empty($user_info['result'])){
            $this -> user_info  = $user_info['result'];
            $this -> user  = $this -> user_info;
            $this -> assign('user',$this -> user_info );
            $this -> assign('auth',true);
        }
        if( isWeChatBrowser() ){

            $this -> weChatLogic    = new \Common\Logic\WeChatLogic();
            $this -> weChatConfig   = $this -> weChatLogic -> weChatConfig;

            $this -> weChatLogic -> authorization();
            $this->assign('wechat_config', $this->weChatConfig);

            $signPackage = $this -> weChatLogic -> getSignPackage();
            $this->assign('signPackage', $signPackage);

        }else{
            /**
             * 普通手机页面入口
             */
//            echo "请在微信端访问！";exit;
        }



        $this -> public_assign();
    }

    /**
     * 保存公告变量到 smarty中 比如 导航
     */
    public function public_assign()
    {
        //用户上次访问时间
        $push_message_time = push_message_time($this->user_id);
        $this->assign('push_message_time',$push_message_time);


//        $mobileMessage = cookie("mobileMessage");
//        if ( !empty($mobileMessage) ){
//            $this->assign('mobileMessage', $mobileMessage);
////            if( cookie('haveMobileMessage') == 1 ){
////                session( "mobileMessage" , null );
////            }
//        }


//        $brand_list = M('brand')->cache(true,MY_CACHE_TIME)->field('id,parent_cat_id,logo,is_hot')->where("parent_cat_id>0")->select();
//        $this->assign('brand_list', $brand_list);

        $this->assign('lmshop_config', $this -> shopConfig);

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