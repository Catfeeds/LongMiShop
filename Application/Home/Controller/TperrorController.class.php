<?php

namespace Home\Controller;
use Think\Controller;
class TperrorController extends Controller {

	public function tp404($msg='',$url=''){
		$msg = empty($msg) ? '您可能输入了错误的网址，或者该页面已经不存在了哦。' : $msg;
		$this->assign('error',$msg);		
		$lmshop_config = array();
		$tp_config = M('config')->cache(true,MY_CACHE_TIME)->select();
		foreach($tp_config as $k => $v)
		{
			if($v['name'] == 'hot_keywords'){
				$lmshop_config['hot_keywords'] = explode('|', $v['value']);
			}
			$lmshop_config[$v['inc_type'].'_'.$v['name']] = $v['value'];
		}
		$this->assign('goods_category_tree', get_goods_category_tree());
		$brand_list = M('brand')->cache(true,MY_CACHE_TIME)->field('id,parent_cat_id,logo,is_hot')->where("parent_cat_id>0")->select();
		$this->assign('brand_list', $brand_list);
		$this->assign('lmshop_config', $lmshop_config);
		$this->display('Public/tp404');
	}
	
}