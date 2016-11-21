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
            $result[] = $configArray;
        }

    }
    return $result;
}