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

    /**
     * 表单数据
     */
    protected $_post_data = array();
    /**
     * 会员信息
     */
    protected $_member_info = array();

    public function __construct()
    {
        parent::__construct();
        $this -> _post_data = I('post.');
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