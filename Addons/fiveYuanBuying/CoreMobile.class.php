<?php
@include 'Addons/fiveYuanBuying/Function/base.php';
class fiveYuanBuyingMobileController
{

    const TB_ORDER = "addons_fiveyuanbuying_order";
    const ORDER_LIMIT = 1;

    public $assignData = array();
    public $userInfo = array();


    public function __construct( $userInfo )
    {
        $this -> userInfo = $userInfo;

    }


    /**
     * 五元抢购ajax
     */
    public function index()
    {
        if( self::ORDER_LIMIT <= getCountWithCondition(self::TB_ORDER , array("user_id" =>$this ->userInfo["user_id"] , "status" => "1" ))){
            exit(json_encode(callback(false,"每人限购".self::ORDER_LIMIT ."份")));
        }else{
            $orderInfo = findDataWithCondition( self::TB_ORDER , array("user_id" =>$this ->userInfo["user_id"] , "status" => "0" ));
            if( !empty($orderInfo)){
                exit(json_encode(callback(true,"",U("Mobile/Addons/fiveYuanBuying",array('pluginName' => "pay",'id'=>$orderInfo["id"])))));
            }else{
                $data = array(
                    "user_id" => $this ->userInfo["user_id"],
                    "order_sn" => date('YmdHis').rand(1000,9999),
                    "status" => "0",
                    "money" => "5",
                    "create_time" => time()
                );
                $id = addData( self::TB_ORDER , $data);
                exit(json_encode(callback(true,"",U("Mobile/Addons/fiveYuanBuying",array('pluginName' => "pay",'id'=>$id)))));
            }
        }
    }

    public function pay(){
        $id = I("id");
        if( $_SESSION['openid'] && strstr($_SERVER['HTTP_USER_AGENT'],'MicroMessenger')){
            $order = findDataWithCondition( TB_ORDER , array("id"=>$id));
            if( !empty( $order ) ){
                addonsWeChatPay( $id , "fiveYuanBuying" );
                exit;
            }
        }else{
            exit;
        }
        exit;
    }

}