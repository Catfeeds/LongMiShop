<?php
/**
 * 积分类定时任务
 */


class PointsCronClass
{

    public function init()
    {
        $conditoin = array(
            "level" => array("lt" => "4"),
            "points_clear_time" => array("lt" => time()),
        );
//        $point

    }

}

$PointsCronClassObj = new PointsCronClass();
$PointsCronClassObj -> init();