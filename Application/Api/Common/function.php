<?php

function jsonReturn($status=0,$msg='',$data=''){
    if(empty($data))
        $data = '';
    $info['status'] = $status ? 1 : $status;
    $info['msg'] = $msg;
    $info['result'] = $data;
    exit(json_encode($info));
}

function changeAddress($array,$field){
    $serverNname = 'http://'.$_SERVER['SERVER_NAME'];
    foreach($array as $key=>$item){
        $array[$key][$field] =  $serverNname.$item[$field];
    }
    return $array;
}