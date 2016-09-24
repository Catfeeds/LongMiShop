<?php
namespace Index\Controller;


class IndexController extends IndexBaseController {

    function exceptAuthActions()
    {
        return array(
            'index'
        );
    }

    public function _initialize() {
        parent::_initialize();
    }

    public function index(){
    	$this->display();
    }

    public function test(){
        $s =  M('s') -> where("status!=1") -> select();
        $ss =  M('ss') -> select();
        $i = 0;
        foreach ($s as $si){
            echo "UPDATE ims_activity_coupon_recode SET code = '".$ss[$i]['aa']."' WHERE id = '".$si['id']."';<br/>";
            $i++;
        }
    }
}