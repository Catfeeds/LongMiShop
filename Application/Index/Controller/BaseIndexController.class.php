<?php
namespace Index\Controller;

use Common\Base\BaseController;
use Common\Model\User;

abstract class BaseIndexController extends BaseController {

    public function _initialize() {
        parent::_initialize();
        session('lm_id',14);
        session('auth',true);
//        session(null);
        if( $this -> needAuth() ){
            //验证部分
            if (session('auth') != true) {
                redirect( U( '/Index/User/login' ) , 0);
                return;
            }
            $userModel = User::currentUserInfo();
            $this->assign('user_id', $userModel->getUserID());
            $this->assign('nickname', $userModel->getUsername());

        }


    }

    abstract function exceptAuthActions();

    protected function needAuth(){
        if ($this->exceptAuthActions() == null) {
            return true;
        }
        if (in_array(ACTION_NAME, $this->exceptAuthActions())) {
            return false;
        };
        return true;
    }

}