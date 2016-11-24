<?php

    return array(
        "lunchFeast" => array(
            array(
                "module"  => "menu",
                "level"   => 2,
                "title"   => "宴午",
                "subMenu" => array(
                    array(
                        "module" => "module",
                        "level"  => 3,
                        "title"  => "基础设置",
                        "ctl"    => "Addons",
                        "act"    => "lunchFeast",
                        "url"    => "/Admin/Addons/lunchFeast/pluginName/config"
                    ),
                    array(
                        "module" => "module",
                        "level"  => 3,
                        "title"  => "店铺列表",
                        "ctl"    => "Addons",
                        "act"    => "lunchFeast",
                        "url"    => "/Admin/Addons/lunchFeast/pluginName/shopList"
                    ),
                    array(
                        "module" => "module",
                        "level"  => 3,
                        "title"  => "订单列表",
                        "ctl"    => "Addons",
                        "act"    => "lunchFeast",
                        "url"    => "/Admin/Addons/lunchFeast/pluginName/orderList"
                    )
                )
            )
        )
    );
