<?php


/**
 * 规范数据返回函数
 * @param bool $state
 * @param string $msg
 * @param array $data
 * @return array
 */
function callback( $state = true, $msg = '', $data = array() )
{
    if( $state == false ){
        //此处应该插入日志
    }
    $state = $state ? 1 : 0 ;
    return array( 'state' => $state , 'msg' => $msg , 'data' => $data );
}

/**
 * 规范数据返回函数判断
 * @param array $result
 * @return bool
 */
function callbackIsTrue( $result )
{
    if( $result['state'] == 1 ){
        return true;
    }
    return false;
}

/**
 * 获取规范数据返回数据
 * @param $result
 * @return array
 */
function getCallbackData( $result )
{
    return $result['data'] ? $result['data'] : array();
}

/**
 * 获取规范数据返回信息
 * @param $result
 * @param $result
 * @return string
 */
function getCallbackMessage( $result )
{
    return $result['msg'] ? $result['msg'] : "" ;
}
