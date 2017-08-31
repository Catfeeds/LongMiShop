<?php

class mapMobileController {

    public $assignData = array();

    public function __construct()
    {
        $html = M("addons_map_html")->where(array('id'=>1))->getField("html");
        $html = htmlspecialchars_decode($html);
        $html = str_replace("gb2312","utf-8",$html);
        echo $html;
        exit;
    }
    //初始页面
    public function index(){

    }
}