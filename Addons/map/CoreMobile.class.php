<?php

class mapMobileController {

    public $assignData = array();

    public function __construct()
    {
        $html = M("addons_map_html")->where(array('id'=>1))->getField("html");
        echo $html;
        exit;
    }

    //初始页面
    public function index(){

    }
}