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

//         $result = getExpress($id);
//         if( callbackIsTrue($result) ){
//            if($result['data']['status'] == 1){
//                //支付时间
//                $pay_time = M('order')->field('pay_time')->where("order_id = '".$id."'")->find();
//                $this->assign('pay_time',$pay_time['pay_time']);
//                $this->assign('expressData', $result['data']);
//            }else{
//                $this->assign('expressMessage', $result['data']['message']);
//            }
//         }else{
//             $this->assign('expressMessage', $result['msg'] );
//         }
        $delivery = M('delivery_doc')->where("order_id='$id'")->limit(1)->find();
        if(empty($delivery)){
            $this->assign('expressMessage', '查询物流失败' );
        }
        $result = kuaidi($delivery['invoice_no'],$delivery['shipping_name']);

        if( $result == false ){
            $dataList = array(
                '安能小包' => 'http://www.ane66.com/',
                '安能快递' => 'http://www.ane56.com/',
                'aae全球专递' => 'http://cn.aaeweb.com/',
                '安捷快递' => 'http://www.anjelex.com/',
                '凤凰快递' => 'phoenixexp.com',
                '民航快递' => 'http://www.cae.com.cn/',
                '配思货运' => 'http://www.peisiwuliu.com/',
                '文捷航空速递' => 'http://www.wjexpress.com/',
                '伍圆' => 'http://www.f5xm.com/',
                '中铁快运' => 'http://www.cre.cn/',
            );
            $notFind = true;
            foreach($dataList as $key=>$item){
                if($delivery['shipping_name'] == $key){
                    $queryUrl = $item;
                    $notFind = false;
                }
            }
            $this->assign('invoice_no', $delivery['invoice_no']);
            $this->assign('shipping_name', $delivery['shipping_name']);
            $this->assign('queryUrl',$queryUrl);
            $this->assign('notFind',$notFind);
            $this->assign('isNoFindApi',true);
            $this->display();
            exit;
        }

        if( $result['status'] == 200  ){
//            dd($result['data']);
            //支付时间
            $pay_time = M('order')->field('pay_time')->where("order_id = '".$id."'")->find();
            $this->assign('pay_time',$pay_time['pay_time']);
            $this->assign('expressData', $result);
            $this->assign('odd_numbers',$delivery['invoice_no']);
        }else{
            $this->assign('expressMessage', $result['message'] );
        }
        $this->display();
    }
}