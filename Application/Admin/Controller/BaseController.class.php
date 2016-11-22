<?php

namespace Admin\Controller;
use Think\Controller;
use Admin\Logic\UpgradeLogic;
class BaseController extends Controller {

    /**
     * 析构函数
     */
    function __construct() 
    {
        parent::__construct();
//        $upgradeLogic = new UpgradeLogic();
//        $upgradeMsg = $upgradeLogic->checkVersion(); //升级包消息
        $upgradeMsg = null;
        $this -> assign('upgradeMsg',$upgradeMsg);
        //用户中心面包屑导航
        $navigate_admin = navigate_admin();
        $this -> assign('logName',$navigate_admin);
//        $this -> assign('navigate_admin',$navigate_admin);
//        tpversion();

//        dd(session());

//
        delFile('./Application/Runtime');//调试使用
   }    
    
    /*
     * 初始化操作
     */
    public function _initialize() 
    {
        $this -> assign('action',ACTION_NAME);
        //过滤不需要登陆的行为
        if(in_array(ACTION_NAME,array('login','logout','vertify')) || in_array(CONTROLLER_NAME,array('Ueditor','Uploadify'))){
        	//return;
        }else{
        	if(session('admin_id') > 0 ){
        		$this->check_priv();//检查管理员菜单操作权限
        	}else{
        		$this->error('请先登陆',U('Admin/Admin/login'),1);
        	}
        }
        $this->public_assign();
    }
    
    /**
     * 保存公告变量到 smarty中 比如 导航 
     */
    public function public_assign()
    {
       $lmshop_config = array();
       $tp_config = M('config')->select();
       foreach($tp_config as $k => $v)
       {
          $lmshop_config[$v['inc_type'].'_'.$v['name']] = $v['value'];
       }
       $this -> assign('lmshop_config', $lmshop_config);
    }
    
    public function check_priv()
    {
    	$ctl = CONTROLLER_NAME;
    	$act = ACTION_NAME;
        if( $ctl == "Addons" &&  $act != "index" && $act != "install" ){
            $act = "_empty";
        }
		$act_list = session('act_list');
		$no_check = array('login','logout','vertifyHandle','vertify','imageUp','upload');
    	if($ctl == "Index" && $act == 'index'){
    		return true;
    	}elseif(strpos('ajax',$act) || in_array($act,$no_check) || $act_list == 'all'){
    		return true;
    	}else{
    		$mod_id = M('system_module') -> where("ctl='$ctl' and act='$act'")->getField('mod_id');
    		$act_list = explode(',', $act_list);
    		if($mod_id){
    			if(!in_array($mod_id, $act_list)){
                    echo "您的账号没有此菜单操作权限,超级管理员可分配权限 【".$ctl."】/【".$act."】";exit;
    				$this->error('您的账号没有此菜单操作权限,超级管理员可分配权限',U('Admin/Index/index'));
    				exit;
    			}else{
    				return true;
    			}
    		}else{
    		    echo "请系统管理员先在菜单管理页添加该菜单【".$ctl."】/【".$act."】";exit;
    			$this->error('请系统管理员先在菜单管理页添加该菜单',U('Admin/System/menu'));
    			exit;
    		}
    	}
    }
}