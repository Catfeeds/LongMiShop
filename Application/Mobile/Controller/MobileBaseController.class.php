<?php

namespace Mobile\Controller;

use Common\Base\BaseController;

class MobileBaseController extends BaseController {

    public $user = array();
    public $user_id = 0;
    public $cateTrre = array();

    public $weChatLogic         = null;
    public $weChatConfig        = array();

    /*
     * 初始化操作
     */
    public function _initialize() {

        if( isWeChatBrowser() ){

            $this -> weChatLogic    = new \Common\Logic\WeChatLogic();

            $this -> weChatConfig   = $this -> weChatLogic -> weChatConfig;
            $this -> weChatLogic -> authorization();
            $signPackage = $this -> weChatLogic -> getSignPackage();

            $this->assign('wechat_config', $this->weChatConfig);
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
        
       $lmshop_config = array();
       $tp_config = M('config')->cache(true,MY_CACHE_TIME)->select();
       foreach($tp_config as $k => $v)
       {
       	  if($v['name'] == 'hot_keywords'){
       	  	 $lmshop_config['hot_keywords'] = explode('|', $v['value']);
       	  }       	  
          $lmshop_config[$v['inc_type'].'_'.$v['name']] = $v['value'];
       }
       $goods_category_tree = get_goods_category_tree();    
       $this->cateTrre = $goods_category_tree;
       $this->assign('goods_category_tree', $goods_category_tree);                     
       $brand_list = M('brand')->cache(true,MY_CACHE_TIME)->field('id,parent_cat_id,logo,is_hot')->where("parent_cat_id>0")->select();
       $this->assign('brand_list', $brand_list);
       $this->assign('lmshop_config', $lmshop_config);
    }      



}