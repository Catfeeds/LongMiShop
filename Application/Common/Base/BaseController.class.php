<?php

namespace Common\Base;
use Think\Controller;

class BaseController extends Controller
{
    public $session_id = null;
    public $shopConfig = array();

    public function _initialize() {
        dd(getConfigArray());
        if( isMobile() ){
            cookie('is_mobile','1',3600);
        }else{
            cookie('is_mobile','0',3600);
        }

        $this->session_id = session_id();
        define('SESSION_ID', $this->session_id);

        $this -> _publicAssign();
    }

    /**
     * 保存公共变量
     */
    private function _publicAssign()
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
//
//    /**
//     * 操作错误跳转的快捷方法
//     * @access protected
//     * @param string $message 错误信息
//     * @param string $jumpUrl 页面跳转地址
//     * @param mixed $ajax 是否为Ajax方式 当数字时指定跳转时间
//     * @return void
//     */
//    protected function error($message='',$jumpUrl='',$ajax=false) {
//        $this->dispatchJump($message,0,$jumpUrl,$ajax);
//    }
//
//    /**
//     * 操作成功跳转的快捷方法
//     * @access protected
//     * @param string $message 提示信息
//     * @param string $jumpUrl 页面跳转地址
//     * @param mixed $ajax 是否为Ajax方式 当数字时指定跳转时间
//     * @return void
//     */
//    protected function success($message='',$jumpUrl='',$ajax=false) {
//        $this->dispatchJump($message,1,$jumpUrl,$ajax);
//    }
}