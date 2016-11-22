<?php

class lunchFeastMobileController
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

        return $this->assignData;

    }

}