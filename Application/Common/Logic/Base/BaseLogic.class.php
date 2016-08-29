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

    }




}