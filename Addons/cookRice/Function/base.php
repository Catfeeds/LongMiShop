<?php

/**
 * 获取活动id
 * @return mixed
 */
function getActivityId(){
    return  M("addons_assistwinning_setprize") -> order("id desc") -> getField("id");
}


/***
 * 获取基础配置
 * @return array
 */
function cookRiceGetConfig(){
    $array = array(
        "edition" => 1,
        "data"=>array(
            "1" => array(
                "edition" => 1,
                "name" => "加温啦",
                "theme"=>"default"
            ),
            "2" => array(
                "edition" => 2,
                "name" => "加温啦",
                "theme"=>"default"
            ),
        )
    );
    return $array;
}