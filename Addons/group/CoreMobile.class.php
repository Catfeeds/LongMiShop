<?php
@include 'Addons/group/Function/base.php';

class groupMobileController
{
    public $assignData = array();
    public $user = array();

    public function __construct($userInfo)
    {
        $this->user = $userInfo;

    }

    //初始页面
    public function index()
    {
        $goodsInfo = M("goods") ->find();
        $this->assignData["goodsInfo"] = $goodsInfo;
        return $this->assignData;
    }


}