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
            M("addons_map_html")->save(array("html"=>$html));
            return addonsSuccess("修改成功");
        }
        $this -> assignData["html"] = M("addons_map_html") ->getField("html");
        return $this -> assignData;
    }
}