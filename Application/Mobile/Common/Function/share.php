<?php


/**
 * 获取分享气泡图片
 */
function getShareImages( $goodes_id = null ){

    $temp_string = "****"; //公众号头像;

    if( !is_null($goodes_id) ){
        $temp_string = "http://" . $_SERVER['HTTP_HOST'] . goods_thum_images($goodes_id,400,400);
    }

    return $temp_string;

}