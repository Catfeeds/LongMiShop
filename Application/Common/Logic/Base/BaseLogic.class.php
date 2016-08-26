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

use Think\Model;
class BaseLogic
{

    /**
     * 表单数据
     */
    protected $_post_data = array();
    /**
     * 会员信息
     */
    protected $_member_info = array();
    /**
     * 模块对象
     */
    protected $_model_obj = null;


    public function __construct()
    {
        $this -> _model_obj = new Model();
    }



    /***
     * 开启事务
     */
    protected function startTrans()
    {
        $this -> _model_obj -> startTrans();
    }
    /***
     * 提交事务
     */
    protected function commit()
    {
        $this -> _model_obj -> commit();
    }
    /***
     * 回滚事务
     */
    protected function rollback()
    {
        $this -> _model_obj -> rollback();
    }


}