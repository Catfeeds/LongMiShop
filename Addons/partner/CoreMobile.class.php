<?php
@include 'Addons/partner/Function/base.php';

class partnerMobileController
{

    public $userInfo = null;
    public $assignData = array();



    //初始化
    public function __construct($userInfo)
    {
        $this->assignData["userInfo"] = $this->userInfo = $userInfo;
    }

    public function index()
    {
        return $this->assignData;
    }
    public function save()
    {
        return $this->assignData;
    }

}