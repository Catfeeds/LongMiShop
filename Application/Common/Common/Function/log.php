<?php


/**
 * 写日志，方便测试（看网站需求，也可以改成把记录存入数据库）
 * 注意：服务器需要开通fopen 配置
 * @param string $word 要写入日志里的文本内容 默认值：空值
 * @param string $file
 * @param string $path
 */
function setLogResult($word='' ,$file = "log.txt" ,$path = "./data/log/") {
    $fileUrl = $path.date('Y-m-d')."/".$file;
    $fp = fopen($fileUrl,"a");
    flock($fp, LOCK_EX) ;
    fwrite($fp,"Run Time：".strftime("%Y%m%d%H%M%S",time())."\n".$word."\n");
    flock($fp, LOCK_UN);
    fclose($fp);
}

