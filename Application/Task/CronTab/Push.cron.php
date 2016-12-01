<?php
/**
 * 供应商结算定时任务部分
 * 2016.10.27
 */

class PushCronClass
{

    private $thisTime = null;
    private $nineItem = null;

    public function init()
    {
        $this->thisTime = date("Y-m-d",time());
        $this->nineItem = date("H",$this->thisTime);

        if($this->nineItem == '17'){
            setLogResult(  $this->nineItem );
            $orderInfo = M('addons_lunchfeast_order')->where(array('status'=>1))->select();
            foreach($orderInfo as $item){
                $orderTime = date('Y-m-d',$item['date']);
                if($orderTime == $this->thisTime){
                    $user = findDataWithCondition( 'users',array( "user_id" => $orderInfo['user_id'] ), 'openid' );
                    $shopInfo = findDataWithCondition("addons_lunchfeast_shop",array('id'=>$orderInfo['shop_id']),'shop_name');
                    //$mealInfo = findDataWithCondition("addons_lunchfeast_meal_list",array('id'=>$orderInfo['meal_id']),'name');
                    $text = "今天中午12:30，宴午".$shopInfo['shop_name']."期待您的大驾！人数：".$orderInfo['number'];
                    $jsSdkLogic = new \Common\Logic\JsSdkLogic();
                    $jsSdkLogic -> push_msg( $user['openid'] , $text );
                }
            }
        }


    }
}

$supplierCronClassObj = new PushCronClass();
$supplierCronClassObj -> init();