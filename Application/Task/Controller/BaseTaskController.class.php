<?php
namespace Task\Controller;

use Common\Base\BaseController;

abstract class BaseTaskController extends BaseController {

    abstract function exceptAuthActions();



    public function _initialize() {
        parent::_initialize();
        if (session('auth') != true) {
            if( $this -> needAuth() ){
                return;
            }
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