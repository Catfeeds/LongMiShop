<?php
class mapAdminController {

    public $assignData = array();

    public function __construct()
    {
    }

    //初始页面
    public function index(){
        if(IS_POST){
            $html = I("html");
            M("addons_map_html")->where(array('id'=>1))->save(array("html"=>$html));
            return addonsSuccess("修改成功");
        }
        $this -> assignData["html"] = M("addons_map_html")->where(array('id'=>1))->getField("html");
        return $this -> assignData;
    }
}