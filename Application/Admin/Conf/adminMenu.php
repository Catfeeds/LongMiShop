<?php

    return array(
        "admin" => array(
            array(
                "module"  => "menu",
                "level"   => 2,
                "title"   => "角色管理",
                "subMenu" => array(
                    array(
                        "module" => "module",
                        "level"  => 3,
                        "title"  => "管理员列表",
                        "ctl"    => "Admin",
                        "act"    => "index",
                    ),
                    array(
                        "module" => "module",
                        "level"  => 3,
                        "title"  => "角色管理",
                        "ctl"    => "Admin",
                        "act"    => "role",
                    ),
                    array(
                        "module" => "module",
                        "level"  => 3,
                        "title"  => "管理员日志",
                        "ctl"    => "Admin",
                        "act"    => "log",
                    )
                )
            ),
            array(
                "module"  => "menu",
                "level"   => 2,
                "title"   => "系统设置",
                "subMenu" => array(
                    array(
                        "module" => "module",
                        "level"  => 3,
                        "title"  => "网站设置",
                        "ctl"    => "System",
                        "act"    => "index",
                    ),
                    array(
                        "module" => "module",
                        "level"  => 3,
                        "title"  => "插件管理",
                        "ctl"    => "Plugin",
                        "act"    => "index",
                    ),
                    array(
                        "module" => "module",
                        "level"  => 3,
                        "title"  => "地区管理",
                        "ctl"    => "Tools",
                        "act"    => "region",
                    )
                )
            ),
            array(
                "module"  => "menu",
                "level"   => 2,
                "title"   => "会员管理",
                "subMenu" => array(
                    array(
                        "module" => "module",
                        "level"  => 3,
                        "title"  => "会员列表",
                        "ctl"    => "User",
                        "act"    => "index",
                    ),
                    array(
                        "module" => "module",
                        "level"  => 3,
                        "title"  => "评论管理",
                        "ctl"    => "Comment",
                        "act"    => "index",
                    ),
                    array(
                        "module" => "module",
                        "level"  => 3,
                        "title"  => "提现列表",
                        "ctl"    => "User",
                        "act"    => "withdrawDeposit",
                    )
                )
            ),
            array(
                "module"  => "menu",
                "level"   => 2,
                "title"   => "商品管理",
                "subMenu" => array(
                    array(
                        "module" => "module",
                        "level"  => 3,
                        "title"  => "商品分类",
                        "ctl"    => "Goods",
                        "act"    => "categoryList",
                    ),
                    array(
                        "module" => "module",
                        "level"  => 3,
                        "title"  => "商品列表",
                        "ctl"    => "Goods",
                        "act"    => "goodsList",
                    ),
                    array(
                        "module" => "module",
                        "level"  => 3,
                        "title"  => "商品品牌",
                        "ctl"    => "Goods",
                        "act"    => "brandList",
                    ),
                    array(
                        "module" => "module",
                        "level"  => 3,
                        "title"  => "配送方式",
                        "ctl"    => "Logistics",
                        "act"    => "index",
                    )
                )
            ),
            array(
                "module"  => "menu",
                "level"   => 2,
                "title"   => "文章管理",
                "subMenu" => array(
                    array(
                        "module" => "module",
                        "level"  => 3,
                        "title"  => "文章分类",
                        "ctl"    => "Article",
                        "act"    => "categoryList",
                    ),
                    array(
                        "module" => "module",
                        "level"  => 3,
                        "title"  => "文章列表",
                        "ctl"    => "Article",
                        "act"    => "articleList",
                    )
                )
            ),
            array(
                "module"  => "menu",
                "level"   => 2,
                "title"   => "广告管理",
                "subMenu" => array(
                    array(
                        "module" => "module",
                        "level"  => 3,
                        "title"  => "广告位",
                        "ctl"    => "Ad",
                        "act"    => "positionList",
                    ),
                    array(
                        "module" => "module",
                        "level"  => 3,
                        "title"  => "广告列表",
                        "ctl"    => "Ad",
                        "act"    => "adList",
                    )
                )
            ),
            array(
                "module"  => "menu",
                "level"   => 2,
                "title"   => "微信管理",
                "subMenu" => array(
                    array(
                        "module" => "module",
                        "level"  => 3,
                        "title"  => "公众号管理",
                        "ctl"    => "Wechat",
                        "act"    => "index",
                    ),
                    array(
                        "module" => "module",
                        "level"  => 3,
                        "title"  => "微信菜单管理",
                        "ctl"    => "Wechat",
                        "act"    => "menu",
                    ),
                    array(
                        "module" => "module",
                        "level"  => 3,
                        "title"  => "文本回复",
                        "ctl"    => "Wechat",
                        "act"    => "text",
                    ),
                    array(
                        "module" => "module",
                        "level"  => 3,
                        "title"  => "图文回复",
                        "ctl"    => "Wechat",
                        "act"    => "img",
                    ),
                    array(
                        "module" => "module",
                        "level"  => 3,
                        "title"  => "分享设置",
                        "ctl"    => "Wechat",
                        "act"    => "share",
                    ),
                    array(
                        "module" => "module",
                        "level"  => 3,
                        "title"  => "提现设置",
                        "ctl"    => "Wechat",
                        "act"    => "merchantConf",
                    )
                )
            ),
            array(
                "module"  => "menu",
                "level"   => 2,
                "title"   => "订单管理",
                "subMenu" => array(
                    array(
                        "module" => "module",
                        "level"  => 3,
                        "title"  => "订单列表",
                        "ctl"    => "Order",
                        "act"    => "index",
                    ),
                    array(
                        "module" => "module",
                        "level"  => 3,
                        "title"  => "发货单列表",
                        "ctl"    => "Order",
                        "act"    => "delivery_list",
                    ),
                    array(
                        "module" => "module",
                        "level"  => 3,
                        "title"  => "退货单列表",
                        "ctl"    => "Order",
                        "act"    => "return_list",
                    )
                )
            ),
            array(
                "module"  => "menu",
                "level"   => 2,
                "title"   => "优惠管理",
                "subMenu" => array(
                    array(
                        "module" => "module",
                        "level"  => 3,
                        "title"  => "优惠券",
                        "ctl"    => "Coupon",
                        "act"    => "index",
                    ),
                    array(
                        "module" => "module",
                        "level"  => 3,
                        "title"  => "礼品券",
                        "ctl"    => "GiftCoupon",
                        "act"    => "index",
                    )
                )
            ),
            array(
                "module"  => "menu",
                "level"   => 2,
                "title"   => "报表统计",
                "subMenu" => array(
                    array(
                        "module" => "module",
                        "level"  => 3,
                        "title"  => "销售概况",
                        "ctl"    => "report",
                        "act"    => "index",
                    ),
                    array(
                        "module" => "module",
                        "level"  => 3,
                        "title"  => "销售排行",
                        "ctl"    => "report",
                        "act"    => "saleTop",
                    ),
                    array(
                        "module" => "module",
                        "level"  => 3,
                        "title"  => "会员排行",
                        "ctl"    => "report",
                        "act"    => "userTop",
                    ),
                    array(
                        "module" => "module",
                        "level"  => 3,
                        "title"  => "销售明细",
                        "ctl"    => "report",
                        "act"    => "saleList",
                    ),
                    array(
                        "module" => "module",
                        "level"  => 3,
                        "title"  => "会员统计",
                        "ctl"    => "report",
                        "act"    => "user",
                    ),
                    array(
                        "module" => "module",
                        "level"  => 3,
                        "title"  => "财务统计",
                        "ctl"    => "report",
                        "act"    => "finance",
                    )
                )
            )
        ),
        "base" => array(
            1 => array(
                "mod_id" => 1,
                "module"=>"menu",
                "level"=>2,
                "ctl"=>"",
                "act"=>"",
                "title"=>"权限管理",
                "visible"=>1,
                "parent_id"=>1,
                "orderby"=>2,
                "icon"=>"fa-cog"
            ),
        )
    );
