<?php
@include 'Addons/fiveYuanBuying/Function/base.php';
class fiveYuanBuyingMobileController
{

    public $assignData = array();
    public $userInfo = array();

    private $activeVersion = null;

    public function __construct( $userInfo )
    {
        $this -> userInfo = $userInfo;

    }


    public function initial()
    {

        return $this->assignData;

    }


    public function createActivity()
    {
        $userId = $this -> userInfo["user_id"];
//        findDataWithCondition($userId);
        exit;
    }


    //初始页面
    public function index()
    {

        return $this->assignData;

    }

}