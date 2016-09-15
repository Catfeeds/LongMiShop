<?php
namespace Common\Logic;

use Common\Logic\Base\BaseLogic;
use Think\Model;

class ServiceLogic extends BaseLogic
{

    public function __construct()
    {
        parent::__construct("config");
        $this -> userId = session(__UserID__);
    }

    /**
     * 发起申请
     * @return array
     */
    public function createServiceOrder()
    {
        $this -> model = new Model();

        try {
            $this -> model -> startTrans();

            //第1步 验证数据初始化
            $this->_createServiceOrderStep1();

            $this -> model -> commit();

            return callback(true,'',$this -> _post_data['orderData']['order_id']);

        } catch (\Exception $e){
            $this -> model -> rollback();

            return callback(false, $e->getMessage());
        }
    }


    /***
     * 以下是步骤
     */

    private function _createServiceOrderStep1(){

    }
}