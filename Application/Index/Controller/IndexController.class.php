<?php
namespace Index\Controller;


class IndexController extends BaseIndexController {

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
        $tables = M()->query($sql = 'show tables');
        foreach ($tables as $table){
            $sql = "alter table ".$table['tables_in_tpshop']." engine=innodb";
            echo $sql;exit;
            M()->query($sql);
            echo $table['tables_in_tpshop'].' is ok;<br>';
        }
//
    }


}