<?php

/**
 * 获取活动id
 * @return mixed
 */
function getActivityId(){
    return  M("addons_assistwinning_setprize") -> order("id desc") -> getField("id");
}