<?php


/**
 * 写日志，方便测试
 * 注意：服务器需要开通 fopen 配置
 * @param string $word 要写入日志里的文本内容 默认值：空值
 * @param string $fileName
 * @param string $suffix 文件后缀
 */
function setLogResult($word='' ,$fileName = "base" ,$suffix = "html") {
    $logPath = "data/log/";
    if (! file_exists ( $logPath )) {
        mkdir ( $logPath, 0777, true );
    }
    $suffixArray = array( "html" , "log" , "txt" );
    if( !in_array( $suffix , $suffixArray ) ){
        $suffix = "html";
    }
    $newLine = $suffix == "html" ? "<br><br>\n\n" : "\n\n";
    $fileUrl = $logPath.date('Y-m-d')."-".$fileName.".".$suffix;

    $content  = "Run Time:".date("Y-m-d H:i:s",time());
    $content .= $newLine;
    $content .= $word;
    $content .= $newLine;
    $content .= "==============================";
    $content .= $newLine;


    $fp = fopen($fileUrl,"a");
    flock($fp, LOCK_EX) ;
    fwrite($fp,$content);
    flock($fp, LOCK_UN);
    fclose($fp);
}

