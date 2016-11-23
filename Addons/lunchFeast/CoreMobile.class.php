<?php

class lunchFeastMobileController
{

    public $assignData = array();
    public $userInfo = array();

    public function __construct( $userInfo )
    {
        $this -> userInfo = $userInfo;
        $this -> assignData["headerPath"] = "./Addons/lunchFeast/Template/Mobile/default/Addons_header.html";
        $this -> assignData["footerPath"] = "./Addons/lunchFeast/Template/Mobile/default/Addons_footer.html";
    }
    //主页
    public function index()
    {
//        $this -> error(1);
        return $this->assignData;
    }
    //店铺主页
    public function shopDetail()
    {
        return $this->assignData;
    }
    //我的宴午
    public function orderList()
    {
        return $this->assignData;
    }
    //订单详情 我的二维码
    public function orderDetail()
    {
        return $this->assignData;
    }
    //菜品结果
    public function foods()
    {
        return $this->assignData;
    }
    //提交页面
    public function pageSubmit()
    {
        return $this->assignData;
    }
    //添加用餐人
    public function AMeal()
    {
        return $this->assignData;
    }
    //新增用餐人
    public function AddAMeal()
    {
        return $this->assignData;
    }
    //结算页面
    public function payment()
    {
        return $this->assignData;
    }
    //结果页
    public function results()
    {
        return $this->assignData;
    }
}