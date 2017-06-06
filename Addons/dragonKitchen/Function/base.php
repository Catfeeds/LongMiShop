<?php

/**
 * 安全验证
 * @param $data
 * @return bool
 */
function signVerification( $data ){

    if( !$data["sign_time"] || !$data["sign_str"] ){
        return false;
    }
    $sign_string = encryption( $data["sign_time"] );
    if( $data["sign_str"] != $sign_string ){
        return false;
    }
    return true;
}


/**
 * 加密操作
 * @param $time
 * @return string
 */
function encryption( $time ){
    $tokenKey = "ZHT_Token_1484724233";
    return md5( $time . $tokenKey . $time);
}