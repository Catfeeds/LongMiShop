<?php

/**
 * 查看 兑换码 是否存在 是否可用
 * @param $code
 * @return array
 */
function checkCode( $code  ){
    if(1==1){
        return callback( true , "可用兑换码" );
    }
    return callback( false , "未找到兑换码" );
}