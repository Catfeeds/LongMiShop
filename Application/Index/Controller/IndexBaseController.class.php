<?php
namespace Index\Controller;

use Common\Base\BaseController;

abstract class IndexBaseController extends BaseController {

    public $user_id     = null;
    public $user        = null;
    public $user_info   = null;



    abstract function exceptAuthActions();

    public function _initialize() {
        parent::_initialize();

        //验证部分
        if ( !isLoginState() ) {
            if( $this -> needAuth() ){
                $redirectedUrl = $_SERVER["REQUEST_SCHEME"].'://'.$_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
                $redirectedUrl = urlencode($redirectedUrl);
                header("Location: ". U( '/Index/User/login' , array("redirectedUrl" => $redirectedUrl)));
                exit;
            }
        }
        $this -> user_id = session(__UserID__);
        $userLogic = new \Common\Logic\UsersLogic();
        $user_info = $userLogic->get_info($this -> user_id);
        if(!empty($user_info['result'])){
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
        }
        return true;
    }


    /**
     * 跳过报错
     */
    public function  _empty(){
        header("Location: ".U("Index/Index/index"));
        exit;
    }
}