<?php
namespace Common\Logic;

use Common\Logic\Base\BaseLogic;
use Think\Model;

class BuyLogic extends BaseLogic
{
    public  $userId                  = null;
    public  $model                   = null;
    private $cartLogic               = null;
//    private $UserAddressModel        = null;

    public function __construct()
    {
        parent::__construct();
        $this -> cartLogic = new \Common\Logic\CartLogic();
        $this -> userId = session(__UserID__);
//        $this -> UserAddressModel = new \Common\Model\UserAddress();
    }


    /**
     * 订单生成
     */
    public function createOrder()
    {
        $this -> model = new Model();

        try {
            $this -> model -> startTrans();
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

            $this -> model -> commit();

            return callback(true,'',array());

        } catch (\Exception $e){
            $this -> model -> rollback();
            dd(callback(false, $e->getMessage()));//调试使用
            return callback(false, $e->getMessage());
        }

    }


    //第1步 表单验证
    private function _createOrderStep1(){
        if( is_null($this -> userId) ){
            throw new \Exception('登录超时请重新登录');
        }
        $address_id = $this -> _post_data['address_id'];
        if( !$address_id ){
            throw new \Exception('请先填写收货人信息！');
        }
        $address = M('UserAddress')->where("address_id = $address_id")->find();
        if( !$address ){
            throw new \Exception('收货人信息有误！');
        }
        $this -> _post_data['address'] = $address;
    }

    //第2步 得到购买商品信息
    private function _createOrderStep2(){
        $post = $this -> _post_data;

        $cart_count = $this -> cartLogic -> cart_count($this->userId,1);
        if( $cart_count == 0 ){
            throw new \Exception('你的购物车没有选中商品');
        }


        throw new \Exception('121');
    }

    //第3步 得到购买相关金额计算等信息
    private function _createOrderStep3(){
        throw new \Exception('11');
    }

    //第4步 生成订单
    private function _createOrderStep4(){
        throw new \Exception('11');
    }

    //第5步 订单后续处理
    private function _createOrderStep5(){
        throw new \Exception('11');
    }





}