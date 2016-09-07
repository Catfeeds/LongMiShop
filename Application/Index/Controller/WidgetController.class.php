<?php
namespace Index\Controller;

class WidgetController extends BaseIndexController {

    function exceptAuthActions()
    {
        return array(
            "getExpress"
        );
    }

    public function _initialize() {
        parent::_initialize();
    }

    public function express(){
        $id = I('get.id',null);
        $result = getExpress($id);
        if( callbackIsTrue($result) ){
            $this->assign('expressData', $result['data'] );
        }else{
            $this->assign('expressMessage', $result['msg'] );
        }
        $this->display();
    }
}