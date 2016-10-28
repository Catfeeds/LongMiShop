<?php
/**
 * 供应商结算定时任务部分
 * 2016.10.27
 */

class SupplierCronClass
{

    private $thisTime = null;
    private $thisDayTime = null;
    private $thisMonthTime = null;

    public function init()
    {
        $this -> thisTime = time();
        $this -> thisDayTime = strtotime(date("Y-m-d"));
        $this -> thisMonthTime = strtotime(date("Y-m"));

        $condition = array(
            "role_id" => 3,
        );
        $adminList = M('admin')->where($condition)->select();
        if (!empty($adminList)) {
            foreach ($adminList as $adminItem) {
                $adminId = $adminItem['admin_id'];
                $condition1 = array(
                    "create_time" => array("egt", $this->thisDayTime),
                    "admin_id"    => $adminId,
                    "type"        => 1
                );
                if ( !isExistenceDataWithCondition( "account_statement" , $condition1 ) ) {
                    $this -> refreshAccountMoney( $adminId );
                    $addData = array(
                        "admin_id"    => $adminId,
                        "create_time" => $this->thisTime,
                        "balance"     => $adminItem['amount'],
                        "type"        => 1
                    );
                    $countData = $this -> adminCount( $adminId );
                    $addData["income"] = $countData["income"];
                    $addData["expend"] = $countData["expend"];
                    $addData["income_count"] = $countData["income_count"];
                    $addData["expend_count"] = $countData["expend_count"];
                    isSuccessToAddData( "account_statement" , $addData );
                    $condition2 = array(
                        "create_time" => array("egt", $this->thisMonthTime),
                        "admin_id"    => $adminItem['admin_id'],
                        "type"        => 2
                    );
                    if ( !isExistenceDataWithCondition( "account_statement" , $condition2 ) ) {
                        $addData = array(
                            "admin_id"    => $adminId,
                            "create_time" => $this->thisTime,
                            "balance"     => $adminItem['amount'],
                            "type"        => 2
                        );
                        $countData = $this -> adminCount( $adminId , "month" );
                        $addData["income"] = $countData["income"];
                        $addData["expend"] = $countData["expend"];
                        $addData["income_count"] = $countData["income_count"];
                        $addData["expend_count"] = $countData["expend_count"];
                        isSuccessToAddData( "account_statement" , $addData );
                    }
                }
            }
        }
    }

    private function adminCount( $admin , $type = "day" ){
        $lastTime = null;
        $endTime = null;
        if( $type == "day" ){
            $lastTime  = strtotime(date("Y-m-d")." -1 day");
            $endTime = $this -> thisDayTime;
        }
        if( $type == "month" ){
            $lastTime  = strtotime(date("Y-m")." -1 month");
            $endTime = $this -> thisMonthTime;
        }
        $lastTime = $lastTime - (60*60*24*7);
        $endTime  = $endTime - (60*60*24*7);
        $returnArray = array(
            "income" => 0,
            "expend" => 0,
            "income_count"=>0,
            "expend_count"=>0,
        );

        $where = array();
        $where["_string"] = " ( order_status = 2 or  order_status = 4 ) ";
        $where["confirm_time"] = array( "between" , array( $lastTime , $endTime ) );
        $where["pay_status"] = 1;
        $where["admin_list"] = array("like","%[".$admin."]%");
        $orderList = selectDataWithCondition( "order" , $where , "order_id" );
        if( !empty( $orderList ) ){
            foreach ( $orderList as $orderItem ){
                $condition = array(
                    "order_id" =>  $orderItem['order_id'],
                    "is_send" => 1,
                );
                $orderGoodsInfo = selectDataWithCondition( "order_goods" , $condition , " member_goods_price , goods_num , goods_postage");
                if( !empty( $orderGoodsInfo ) ){
                    foreach ( $orderGoodsInfo as $orderGoodsItem){
                        $returnArray["income"] += $orderGoodsItem["goods_num"] * $orderGoodsItem["member_goods_price"] ;
                        $returnArray["income"] += $orderGoodsItem["goods_postage"] ;
                    }
                }
            }
        }
        $returnArray["income_count"] = count( $orderList );

        $where = array();
        $where["update_time"] = array( "between" , array( $lastTime , $endTime ) );
        $where["state"] = 1;
        $where["admin_id"] = $admin;
        $withdrawalsList = selectDataWithCondition( "admin_withdrawals" , $where , "money" );
        if( !empty( $withdrawalsList ) ){
            foreach ( $withdrawalsList as $withdrawalsItem ){
                $returnArray["expend"] += $withdrawalsItem["money"] ;
            }
        }
        $returnArray["expend_count"] = count( $withdrawalsList );


        return $returnArray;
    }

    private function refreshAccountMoney( $adminId = null ){
        @require_once "Application/Admin/Common/Function/account.php";
        @refreshAccountMoney( $adminId );
    }
}

$supplierCronClassObj = new SupplierCronClass();
$supplierCronClassObj -> init();