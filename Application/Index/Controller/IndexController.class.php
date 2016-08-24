<?php

namespace Index\Controller;

use Index\Controller;
class IndexController extends BaseController {

    public function _initialize() {
    }

    // 官网首页
    public function index()
    {
        echo 1;exit;
        $this->display();
    }
    
}