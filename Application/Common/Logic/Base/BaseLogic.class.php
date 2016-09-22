<?php
/**
 * 逻辑基类
 *
 * 2016/8/26
 *
 * 钟瀚涛
 *
 */
namespace Common\Logic\Base;

use Think\Model\RelationModel;

class BaseLogic extends RelationModel
{

    public          $nowTime                 = null;
    public          $userId                  = null;
    public          $orderId                 = null;
    public          $user                    = null;
    public          $model                   = null;
    protected       $_post_data              = array();

    public function __construct($name = "")
    {
        parent::__construct($name);
        $this -> _post_data = I('post.');
        $this -> nowTime = time();
    }

    /**
     * 动态方法实现
     * @access public
     * @param string $method 方法名称
     * @param array $args 调用参数
     * @return mixed
     */
    public function __call($method,$args) {
        if(strtolower(substr($method,0,8))=='relation'){
            $type    =   strtoupper(substr($method,8));
            if(in_array($type,array('ADD','SAVE','DEL'),true)) {
                array_unshift($args,$type);
                return call_user_func_array(array(&$this, 'opRelation'), $args);
            }
        }else{
            return parent::__call($method,$args);
        }
    }




}