<?php

namespace Common\Base;
use Think\Controller;

class BaseController extends Controller
{
    public $session_id = null;
    public $shopConfig = array();

    public function _initialize() {
        $this -> session_id = session_id();
        $this -> public_assign();
    }



    /**
     * 保存公共变量
     */
    public function public_assign()
    {
        $shopConfig = array();
        $config = M('config')->cache(true,MY_CACHE_TIME)->select();
        foreach($config as $k => $v)
        {
            if($v['name'] == 'hot_keywords'){
                $shopConfig['hot_keywords'] = explode('|', $v['value']);
            }
            $shopConfig[$v['inc_type'].'_'.$v['name']] = $v['value'];
        }
        $this -> shopConfig = $shopConfig;
        $this->assign('shopConfig', $shopConfig);
    }

    /**
     * 魔术方法 有不存在的操作的时候执行
     * @access public
     * @param string $method 方法名
     * @param array $args 参数
     * @return mixed
     */
    public function __call($method,$args) {
        if( 0 === strcasecmp($method,ACTION_NAME.C('ACTION_SUFFIX'))) {
            if(method_exists($this,'_empty')) {
                // 如果定义了_empty操作 则调用
                $this->_empty($method,$args);
            }else{
                E(L('_ERROR_ACTION_').':'.ACTION_NAME);
            }
        }else{
            E(__CLASS__.':'.$method.L('_METHOD_NOT_EXIST_'));
            return;
        }
    }
}