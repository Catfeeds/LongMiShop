<?php
/**
 * 供应商结算定时任务部分
 */

$condition = array(
    "role_id" => 3,

);

 $adminList = M('admin') -> where( $condition ) -> select();
 if ( !empty( $adminList ) ){
     foreach ( $adminList as $adminItem ) {

         $condition2 = array(
             "is" => 3,

         );
         M("order_goods") -> where($condition2) -> field(" ") -> select();

//         findDataWithCondition( "admin"  , array() );


     }
 }

