<?php


function break_fast_get_config()
{
    $return = array();
    $configs = selectDataWithCondition("addons_breakfast_config");
    if (!empty($configs)) {
        foreach ($configs as $config) {
            $return[$config['key_name']] = $config['val'];
        }
    }
    return $return;
}


function break_fast_get_this_day()
{
    return strtotime(date("Y-m-d", time()));
}

function break_fast_is_hour()
{
    $myHour = date("H", time());
    $hourArray = array("08", "09","20","21","22");
    if (in_array($myHour, $hourArray)) {
        return true;
    }
    return false;
}

function break_fast_get_status()
{
    $time = array();
    $config = break_fast_get_config();
    $time["start"] = $config['start_time'];
    $time["end"] = $config['start_time'] + $config['days'] * 24*60*60;

    if ($time["end"] <= time()) {
        return 3;
    } else if ($time["start"] <= time()) {
        return 1;
    } else {
        return 2;
    }
}