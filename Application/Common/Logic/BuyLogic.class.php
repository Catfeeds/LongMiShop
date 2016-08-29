<?php
namespace Common\Logic;

use Common\Logic\Base\BaseLogic;

class BuyLogic extends BaseLogic
{

    public function __construct()
    {

        parent::__construct();

    }


    /**
     * 订单生成
     */
    public function createOrder( ){



        try {
            $this -> startTrans();
            //第1步 表单验证
            $this->_createOrderStep1();

            //第2步 得到购买商品信息
            $this->_createOrderStep2();

            //第3步 得到购买相关金额计算等信息
            $this->_createOrderStep3();

            //第4步 生成订单
            $this->_createOrderStep4();

            //第5步 订单后续处理
            $this->_createOrderStep5();

            $this -> commit();

            return callback(true,'',array());

        } catch (\Exception $e){
            $this -> rollback();
            return callback(false, $e->getMessage());
        }

    }

    private function _createOrderStep1(){
        throw new \Exception('11');
    }

    private function _createOrderStep2(){
        throw new \Exception('11');
    }

    private function _createOrderStep3(){
        throw new \Exception('11');
    }

    private function _createOrderStep4(){
        throw new \Exception('11');
    }

    private function _createOrderStep5(){
        throw new \Exception('11');
    }





}