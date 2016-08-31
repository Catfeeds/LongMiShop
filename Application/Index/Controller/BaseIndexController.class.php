<?php
namespace Index\Controller;

use Common\Base\BaseController;
use Common\Model\User;
use Common\Logic\UsersLogic;

abstract class BaseIndexController extends BaseController {

    public $user_id     = null;
    public $user        = null;
    public $user_info   = null;


    /**
     * 免登陆页面函数声明
     */
    abstract function exceptAuthActions();



    public function _initialize() {
        parent::_initialize();
        session(__UserID__,1);
        session('auth',true);
//        session(null);

        /**
         * 调试使用 start
         */
        $this -> user_id = session(__UserID__);
        $userLogic = new UsersLogic();
        $user_info = $userLogic->get_info($this -> user_id);
        $this -> user_info  = $user_info['result'];
        $this -> user  = $this -> user_info;
        /**
         * 调试使用 end
         */


        if( $this -> needAuth() ){
            //验证部分
            if (session('auth') != true) {
                redirect( U( '/Index/User/login' ) , 0);
                return;
            }
            session('auth',true);

//            $userModel = User::currentInfo();
//            $this -> assign('user',$userModel -> getInfo());

            $this -> user_id = User::getCurrentUserID();
            $userLogic = new UsersLogic();
            $user_info = $userLogic->get_info($this -> user_id);
            $this -> user_info  = $user_info['result'];
            $this -> user  = $this -> user_info;
            $this -> assign('user',$this -> user_info );
            $this -> assign('auth',true);
        }

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