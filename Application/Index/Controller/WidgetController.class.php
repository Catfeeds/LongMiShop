<?php
namespace Index\Controller;

class WidgetController extends BaseIndexController {

    function exceptAuthActions()
    {
        return array(
            "getExpress"
        );
    }

    public function _initialize() {
        parent::_initialize();
    }

    public function express(){
        $id = I('get.id',null);
//        $obj = '{"nu":"116082458010059001","comcontact":"400-010-6660","companytype":"rufengda","com":"rufengda","condition":"F00","status":"1","codenumber":"116082458010059001","state":"3","data":[{"time":"2016-08-27 11:55:58","location":"","context":"运单已送达成功 妥投"},{"time":"2016-08-27 08:35:13","location":"","context":"运单已由配送员沈城林送出，联系电话：18927512871【G20峰会期间进出浙江时效有所增加，请耐心等待！】 已分配"},{"time":"2016-08-27 08:25:33","location":"","context":"运单已由广州市 广州车陂站扫描入站 联系电话：020-82570549 已入站"},{"time":"2016-08-27 08:10:00","location":"","context":"运单已到达广州市 广州车陂站 已卸车"},{"time":"2016-08-27 06:44:40","location":"","context":"运单已从广州市 广州分拣部发出，下一站广州市 广州车陂站 已发车"},{"time":"2016-08-27 02:53:41","location":"","context":"运单已从广州市 广州分拣部发出，下一站广州市 广州车陂站 已分拣"},{"time":"2016-08-27 02:10:42","location":"","context":"运单已到达广州市 广州分拣部 已入库"}],"message":"ok","ischeck":"1","comurl":"http://www.rufengda.com"}';
//        $obj = '{"message":"如风达 单号116082SS458010059001，没有查到相关信息。单号暂未收录或已过期","comcontact":"400-010-6660","ischeck":"0","status":"0","comurl":"http://www.rufengda.com"}';
//        $res = json_decode($obj,true);
         $result = getExpress($id);
         
         if( callbackIsTrue($result) ){
             $this->assign('expressData', $result['data'] );
         }else{
             $this->assign('expressMessage', $result['msg'] );
         }
         // dd($result);
//        $this->assign('expressData',$res);
        $this->display();
    }
}