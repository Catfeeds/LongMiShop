<?php

class incubationAdminController {

    public $assignData = array();

    public function __construct()
    {
    }

    //初始页面
    public function index(){
        return $this -> assignData;
    }
}