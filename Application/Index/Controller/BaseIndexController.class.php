<?php
namespace Index\Controller;

use Common\Base\BaseController;

abstract class BaseIndexController extends BaseController {

    public function _initialize() {
        parent::_initialize();

        if( $this -> needAuth() ){

           /**
            *  验证部分
            */

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