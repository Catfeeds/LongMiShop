<?php


//function setViewData( $dataList , &$obj ){
//    if( !empty( $dataList ) ){
//        foreach ( $dataList as $dataKey => $dataItem ){
//            $obj -> assign( $dataKey , $dataItem );
//        }
//    }
//
//
//}


/**
 * 获取插件列表
 * @param $path
 * @return array
 */
function getAddonsList( $path )
{
    $dirList  = scandir( $path );
    $result = array();
    foreach($dirList as $dirItem)
    {
        if( $dirItem === '.' ||  $dirItem  === '..'){
            continue;
        }
        $addonsItemPath =  $path . "/" . $dirItem . "/" ;
        $configJsonPath = $addonsItemPath . "main.json";
        if( file_exists( $configJsonPath ) ){
            $configJson = file_get_contents( $configJsonPath );
            $configArray = json_decode( $configJson , true );
            if( empty( $configArray ) ){
                continue;
            }
            $lockFilePath = $addonsItemPath . "install.lock";
            if( file_exists( $lockFilePath ) ){
                $configArray["inInstall"] = true;
            }else{
                $configArray["inInstall"] = false;
            }
            if($configArray["is_hide"]){
                continue;
            }
            $result[] = $configArray;
        }

    }
    return $result;
}


/**
 * 插件错误返回
 * @param $msg
 * @param null $url
 * @param null $time
 * @return array
 */
function addonsError( $msg , $url = null , $time = null){
    if( is_null( $url ) ){
        $url = $_SERVER['HTTP_REFERER'];
    }
    return array(
        "__error" => array(
            "msg" => $msg,
            "url" => $url,
            "time" => $time
        ),
    );
}
/**
 * 插件成功返回
 * @param $msg
 * @param null $url
 * @param null $time
 * @return array
 */
function addonsSuccess( $msg , $url = null , $time = null){
    return array(
        "__success" => array(
            "msg" => $msg,
            "url" => $url,
            "time" => $time
        ),
    );
}

/**
 * 插件微信支付跳转
 * @param $orderId
 * @param $addonsName
 */
function addonsWeChatPay( $orderId , $addonsName ){
    header("Location: " . U("Mobile/Payment/getCode",array("pay_code"=>"weixin" , "order_id"=>"addons_".$addonsName."_".$orderId)));
    exit;
}