<?php
/**
 *  小物件控制器
 */
namespace Admin\Controller;


class WidgetController extends BaseController {



    public function specItem(){
        $spec = array(
            "id" => $_GET['specid']
        );
        $specitem = array(
            "id" => time(),
            "title" => $_GET['title'],
            "show" => 1
        );
        $this -> assign('spec',$spec);
        $this -> assign('specitem',$specitem);
        $this -> display();
    }

    public function spec(){
        $spec = array(
            "id" => time(),
            "title" => $_GET['title']
        );
        $this -> assign('spec',$spec);
        $this -> display();
    }



}