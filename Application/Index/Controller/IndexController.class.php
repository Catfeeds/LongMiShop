<?php
namespace Index\Controller;
//use Think\Controller;
class IndexController extends BaseController {

    function __construct()
    {
        parent::__construct();
    }

    public function index(){
    	$this->display();
    }
}