<?php


/**
 * 写日志，方便测试（看网站需求，也可以改成把记录存入数据库）
 * 注意：服务器需要开通fopen 配置
 * @param string $word 要写入日志里的文本内容 默认值：空值
 * @param string $fileName
 */
function setLogResult($word='' ,$fileName = "base" ) {
    $path = "data/log/";
    if (! file_exists ( $path )) {
        mkdir ( "$path", 0777, true );
    }
    $fileUrl = $path.date('Y-m-d')."-".$fileName.".log";
    $fp = fopen($fileUrl,"a");
    flock($fp, LOCK_EX) ;
    fwrite($fp,"Run Time:".date("Y-m-d H:i:s",time())."\n\n".$word."\n\n==============================\n\n");
    flock($fp, LOCK_UN);
    fclose($fp);
}

