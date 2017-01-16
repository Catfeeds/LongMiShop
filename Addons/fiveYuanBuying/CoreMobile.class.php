<?php
@include 'Addons/fiveYuanBuying/Function/base.php';
class fiveYuanBuyingMobileController
{

    public $assignData = array();
    public $userInfo = array();


    public function __construct( $userInfo )
    {
        $this -> userInfo = $userInfo;

    }

    //初始页面
    public function index()
    {
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