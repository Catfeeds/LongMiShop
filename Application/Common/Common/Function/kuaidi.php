<?php


/**
 * 快递查询
 * @param $invoice_no
 * @param $shipping_name
 * @return bool|mixed
 */
function kuaidi($invoice_no, $shipping_name) {
    switch ($shipping_name) {
        case '中国邮政':$logi_type = 'ems';
            break;
        case '申通快递':$logi_type = 'shentong';
            break;
        case '圆通速递':$logi_type = 'yuantong';
            break;
        case '顺丰':$logi_type = 'shunfeng';
            break;
        case '韵达快运':$logi_type = 'yunda';
            break;
        case '天天快递':$logi_type = 'tiantian';
            break;
        case '中通速递':$logi_type = 'zhongtong';
            break;
        case '增益速递':$logi_type = 'zengyisudi';
            break;
    }
    if(empty($logi_type)){
        return false;
    }
    $kurl = 'http://www.kuaidi100.com/query?type=' . $logi_type . '&postid=' . $invoice_no;
    $get_content = file_get_contents($kurl);
    $data = json_decode($get_content,true);
    return $data;
}
