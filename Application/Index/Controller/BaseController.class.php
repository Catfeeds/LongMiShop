<?php
namespace Index\Controller;
use Think\Controller;
class BaseController extends Controller {

    function __construct()
    {
        $this -> _initialize();
    }

    /*
     * 初始化操作
     */
    public function _initialize() {

    }

}