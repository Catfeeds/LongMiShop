<?php
@include 'Addons/getCoupon2/Function/base.php';
class getCoupon2MobileController
{


    public $assignData = array();
    public $userInfo = array();
    public $userId = null;



    public function __construct($userInfo)
    {


    }

    public function index()
    {
        header("Location: ".U('Mobile/Coupon/index'));exit;
    }
}