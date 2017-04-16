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
    $hourArray = array("08", "09","20","21");
    if (in_array($myHour, $hourArray)) {
        return true;
    }
    return false;
}

function break_fast_get_status()
{
    $time = array(
        "start" => 0,
        "end" => 1492617600,
    );
    if ($time["end"] <= time()) {
        return 3;
    } else if ($time["start"] <= time()) {
        return 1;
    } else {
        return 2;
    }
}