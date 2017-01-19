<?php
@include 'Addons/dragonKitchen/Function/base.php';
class dragonKitchenAdminController
{


    public $assignData = array();


    public function __construct( )
    {
    }

    public function index()
    {
        $this->assignData["list"] = array(
            array(
                "title" => "食谱设置",
                "act"   => "recipes"
            ),
            array(
                "title" => "分类设置",
                "act"   => "classify"
            )
        );
        return $this->assignData;
    }




    public function recipes(){
        return $this->assignData;
    }
    public function recipesDetail(){
        return $this->assignData;
    }

    public function classify(){
        return $this->assignData;
    }
}