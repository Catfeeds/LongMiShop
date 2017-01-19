<?php

@include 'Addons/dragonKitchen/Function/base.php';

class dragonKitchenApiController
{

    public function __construct()
    {
        $signArray = array("sign_time" => I("sign_time"), "sign_str" => I("sign_str"));
        if (!signVerification($signArray)) {
//            printJson(false, "验证失败!");
        }
    }


    public function index()
    {
    }


    public function getList()
    {
        $key = I("keyword", null);
        is_null($key) ? printJson(false, "关键字错误!") : false;
        $data = array(
            array(
                "id"         => 1,
                "images_url" => "http://www.longmiwang.com/Template/mobile/longmi/Static/images/new/top_menu_3.gif",
                "title"      => "sef"
            ),
            array(
                "id"         => 2,
                "images_url" => "http://www.longmiwang.com/Template/mobile/longmi/Static/images/new/top_menu_1.gif",
                "title"      => "se12sf"
            ),
            array(
                "id"         => 3,
                "images_url" => "http://www.longmiwang.com/Template/mobile/longmi/Static/images/new/top_menu_2.gif",
                "title"      => "sadg时代广场ef"
            )
        );
        printJson(true, "", $data);
    }


    public function getCollection(){
        $key = I("userId", null);
        is_null($key) ? printJson(false, "关键字错误!") : false;
        $data = array(
            array(
                "id"         => 1,
                "images_url" => "http://www.longmiwang.com/Template/mobile/longmi/Static/images/new/top_menu_3.gif",
                "title"      => "sef"
            ),
            array(
                "id"         => 2,
                "images_url" => "http://www.longmiwang.com/Template/mobile/longmi/Static/images/new/top_menu_1.gif",
                "title"      => "se12sf"
            ),
            array(
                "id"         => 3,
                "images_url" => "http://www.longmiwang.com/Template/mobile/longmi/Static/images/new/top_menu_2.gif",
                "title"      => "sadg时代广场ef"
            )
        );
        printJson(true, "", $data);
    }

    public function getDetail()
    {
        $id = I("id", null);
        is_null($id) ? printJson(false, "参数错误!") : false;
        $data = array(
            "banner" => "http://www.longmiwang.com/Template/mobile/longmi/Static/images/new/top_menu_3.gif",

            "title" => "辣白菜",

            "list"=>array(
                array(
                    "title"    => "材料",
                    "is_table" => true,
                    "table"    => array(
                        array(
                            "title" => "白菜",
                            "desc"  => "300克",
                        ),
                        array(
                            "title" => "白菜",
                            "desc"  => "300克",
                        ),
                        array(
                            "title" => "白菜",
                        ),
                    ),
                    "images"   => "",
                    "test"     => ""
                ),
                array(
                    "title"    => "步骤1",
                    "is_table" => false,
                    "table"    => array(),
                    "images"   => "http://www.longmiwang.com/Template/mobile/longmi/Static/images/new/top_menu_3.gif",
                    "test"     => "去皮去皮去皮去皮去皮去皮"
                ),
                array(
                    "title"    => "步骤2",
                    "is_table" => false,
                    "table"    => array(),
                    "images"   => "http://www.longmiwang.com/Template/mobile/longmi/Static/images/new/top_menu_3.gif",
                    "test"     => "去皮去皮去皮去皮去皮去皮"
                ),
                array(
                    "title"    => "步骤3",
                    "is_table" => false,
                    "table"    => array(),
                    "images"   => "http://www.longmiwang.com/Template/mobile/longmi/Static/images/new/top_menu_3.gif",
                    "test"     => "去皮去皮去皮去皮去皮去皮"
                ),
            )

        );
        printJson(true, "", $data);
    }

}