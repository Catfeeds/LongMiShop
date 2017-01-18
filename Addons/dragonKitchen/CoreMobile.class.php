<?php
@include 'Addons/dragonKitchen/Function/base.php';
class dragonKitchenMobileController
{


    public $assignData = array();
    public $userInfo = array();


    public function __construct( $userInfo )
    {
        $this -> userInfo = $userInfo;

    }

    public function index()
    {

    }

}