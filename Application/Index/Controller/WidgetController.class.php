<?php
namespace Index\Controller;

class WidgetController extends IndexBaseController {

    function exceptAuthActions()
    {
        return array(
            "getExpress"
        );
    }

    public function _initialize() {
        parent::_initialize();
    }

    //物流信息
    public function express(){
        $id = I('get.id',null);
         $result = getExpress($id);
         if( callbackIsTrue($result) ){
            if($result['data']['status'] == 1){
                //支付时间
                $pay_time = M('order')->field('pay_time')->where("order_id = '".$id."'")->find();
                $this->assign('pay_time',$pay_time['pay_time']);
                $this->assign('expressData', $result['data']);
            }else{
                $this->assign('expressMessage', $result['data']['message']);
            }
         }else{
             $this->assign('expressMessage', $result['msg'] );
         }
        $this->display();
    }
}