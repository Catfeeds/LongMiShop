<?php

namespace Common\Base;
use Think\Controller;

class BaseController extends Controller
{
    public $session_id = null;
    public $shopConfig = array();

    public function _initialize() {
//        if( isMobile() ){
//            cookie('is_mobile','1',3600);
//        }else{
//            cookie('is_mobile','0',3600);
//        }

        $this->session_id = session_id();
        define('SESSION_ID', $this->session_id);

        $this -> _publicAssign();
    }

    /**
     * 保存公共变量
     */
    private function _publicAssign()
    {
        $shopConfig = getShopConfig();
        $this -> shopConfig = $shopConfig;
        $this -> assign('shopConfig', $shopConfig);
        $versionToken = "v2.8";
        $this -> assign('versionToken', $versionToken);
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