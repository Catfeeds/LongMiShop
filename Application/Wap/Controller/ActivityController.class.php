<?php

namespace Wap\Controller;

class ActivityController extends WapBaseController
{

    function exceptAuthActions()
    {
        return array(
            'index',
        );
    }

    public function _initialize()
    {
        parent::_initialize();
    }

    /**
     * 活动列表页面
     */
    public function index()
    {
        $data = array(
            "item" => array(
                array(
                    "url"   => U('Mobile/Index/recommendPolite'),
                    "bg"    => "images/new/activity_bg_1.png",
                    "title" => "送米送龙米",
                    "desc"  => "高颜值礼盒套装&nbsp;好友在线随时随地领取",
                ),
                array(
                    "url"   => U('Mobile/User/myPoster'),
                    "bg"    => "images/new/activity_bg_2.png",
                    "title" => "赚米($)攻略",
                    "desc"  => "边吃龙米边赚点米($)",
                ),
                array(
                    "url"   => U('Mobile/Activity/fiveYuanBuying'),
                    "bg"    => "images/new/activity_bg_3.png",
                    "title" => "5元抢购88元龙米大礼包",
                    "desc"  => "优惠多多，还能送福利给好友",
                )
            )
        );
        printJson(true, "", $data);
    }
}