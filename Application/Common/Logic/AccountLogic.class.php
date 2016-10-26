<?php
namespace Common\Logic;

use Common\Logic\Base\BaseLogic;

class AccountLogic extends BaseLogic
{

    private $adminInfo = null;



    public function refreshAccountMoney( $adminId ){

        $this -> adminInfo = findDataWithCondition( "admin" , array( "admin_id" => $adminId ) );
        $refreshTime = $this -> adminInfo["amount_refresh_time"];

        $cumulativeTransactionAmount = 0;

        $condition = 'o.order_id=og.order_id and og.admin_id = "'.$adminId.'" and o.pay_status > 1 and og.is_send in ( 0 , 1 ) ';
        $orderGoodsList = M() -> table( array( 'order' => 'o' , 'order_goods' => 'og' ) ) -> field( 'og.*' ) -> where( $condition ) -> select();

        if( !empty( $orderGoodsList ) ){
            foreach ( $orderGoodsList as $orderGoodsItem ){
                $cumulativeTransactionAmount += $orderGoodsItem['member_goods_price'] * $orderGoodsItem['goods_num'];
            }
        }






        $condition2 = array(

        );
    }

}