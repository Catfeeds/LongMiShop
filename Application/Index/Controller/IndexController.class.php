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
        $sql = "";
        foreach ($tables as $table){
            $sql .= "alter table ".$table['Tables_in_longmi_master']." engine=innodb;";

//            M()->query($sql);
//            echo $table['tables_in_tpshop'].' is ok;<br>';
        }
            echo $sql;exit;
    }


}