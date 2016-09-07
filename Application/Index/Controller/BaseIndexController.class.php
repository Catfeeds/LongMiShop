<?php
namespace Index\Controller;

use Common\Base\BaseController;
use Common\Logic\UsersLogic;

abstract class BaseIndexController extends BaseController {

    public $user_id     = null;
    public $user        = null;
    public $user_info   = null;



    abstract function exceptAuthActions();



    public function _initialize() {
        parent::_initialize();


        //验证部分
        if (session('auth') != true) {
            if( $this -> needAuth() ){
                session('redirectedUrl',$_SERVER["REQUEST_SCHEME"].'://'.$_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"]);
                redirect( U( '/Index/User/login' ) , 0);
                return;
            }
        }

        $this -> user_id = session(__UserID__);
        $userLogic = new UsersLogic();
        $user_info = $userLogic->get_info($this -> user_id);
        if(!empty($user_info['result'])){
            $this -> user_info  = $user_info['result'];
            $this -> user  = $this -> user_info;
            $this -> assign('user',$this -> user_info );
            $this -> assign('auth',true);
        }

//
//
////            $userModel = User::currentInfo();
////            $this -> assign('user',$userModel -> getInfo());
//
//            $this -> user_id = User::getCurrentUserID();
//            $userLogic = new UsersLogic();
//            $user_info = $userLogic->get_info($this -> user_id);
//            $this -> user_info  = $user_info['result'];
//            $this -> user  = $this -> user_info;

    }

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