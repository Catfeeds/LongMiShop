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


//    abstract function exceptAuthActions();

    /**
     * 初始化操作
     */
    public function _initialize() {

        //验证部分
        if (session('auth') != true) {
            if( $this -> needAuth() ){
//                $redirectedUrl = $_SERVER["REQUEST_SCHEME"].'://'.$_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
//                $redirectedUrl = urlencode($redirectedUrl);
//                header("Location: ". U( '/Index/User/login' , array("redirectedUrl" => $redirectedUrl)));
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
        }
        $this -> public_assign();
    }
    
    /**
     * 保存公告变量到 smarty中 比如 导航 
     */   
    public function public_assign()
    {

       $goods_category_tree = get_goods_category_tree();    
       $this->cateTrre = $goods_category_tree;
       $this->assign('goods_category_tree', $goods_category_tree);                     
       $brand_list = M('brand')->cache(true,MY_CACHE_TIME)->field('id,parent_cat_id,logo,is_hot')->where("parent_cat_id>0")->select();
       $this->assign('brand_list', $brand_list);

       $this->assign('lmshop_config', $this -> shopConfig);

    }

    protected function needAuth(){
            return false;
//        if ($this->exceptAuthActions() == null) {
//            return true;
//        }
//        if (in_array(ACTION_NAME, $this->exceptAuthActions())) {
//            return false;
//        };
//        return true;
    }



}