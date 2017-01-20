<?php
@include 'Addons/redRain/Function/base.php';
class redRainMobileController
{


    public $assignData = array();
    public $userInfo = array();

    public function __construct( $userInfo )
    {
        $this -> userInfo = $userInfo;
    }

    public function index()
    {
        return $this -> assignData;
    }


}