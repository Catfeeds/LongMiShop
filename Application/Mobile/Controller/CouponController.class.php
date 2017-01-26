<?php
namespace Mobile\Controller;
class CouponController extends MobileBaseController {


    function exceptAuthActions()
    {
        return array(
        );
    }

    public function  _initialize() {
        parent::_initialize();
    }


    public function index(){

        $id = 24;
        if( empty($id)  || !isExistenceDataWithCondition("coupon",array("id"=>$id))  ){
            $this -> error("未找到优惠券",U("Mobile/Index/index"));
            exit;
        }
        if( getCountWithCondition("coupon_list",array("cid"=>$id)) >= 999 ){
           $this -> error("优惠券已经派发完",U("Mobile/Index/index"));
            exit;
        }
        $condition = array(
            "uid"=>$this->user_id,
            "cid"=>$id
        );
        if( !isExistenceDataWithCondition("coupon_list",$condition)){
            if( $this->user["is_follow"] == 1){
                addNewCoupon( $id , $this->user_id);
                header("Location: ".U("Mobile/User/coupon"));
                exit;
            }else{
                $this -> display();
                exit;
            }
        }

        header("Location: ".U("Mobile/User/index"));
        exit;
    }
}
