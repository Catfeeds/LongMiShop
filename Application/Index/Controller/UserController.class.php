<?php
namespace Index\Controller;


class UserController extends BaseIndexController {

    function exceptAuthActions()
    {
        return array(
            'login',
            'register'
        );
    }

    public function _initialize() {
        parent::_initialize();
    }

    public function login(){
        $this->display();
    }

    public function register(){
        $this->display();
    }



}