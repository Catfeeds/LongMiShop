<?php

/**
 * 助力活动
 * Class assistWinningMobileController
 */
class assistWinningMobileController{

    public $assignData = array();


    public function index(){
        $this -> assignData["time"]= time();
        return $this -> assignData;
    }
}