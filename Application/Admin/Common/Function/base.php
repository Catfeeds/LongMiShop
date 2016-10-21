<?php

/**
 * 管理员操作记录
 * @param $log_url 操作URL
 * @param $log_info 记录信息
 */
function adminLog($log_info){
    $add['log_time'] = time();
    $add['admin_id'] = session('admin_id');
    $add['log_info'] = $log_info;
    $add['log_ip'] = getIP();
    $add['log_url'] = __ACTION__;
    M('admin_log')->add($add);
}


/**
 * 是否为供应商
 * @return bool
 */
function is_supplier(){
    if(session('admin_role_id') == 3){
        return true;
    }
    return false;
}


function getAdminInfo($admin_id){
    return D('admin')->where("admin_id=$admin_id")->find();
}

//function tpversion()
//{
//    if(!empty($_SESSION['isset_push']))
//        return false;
//    $_SESSION['isset_push'] = 1;
//    error_reporting(0);//关闭所有错误报告
//    $app_path = dirname($_SERVER['SCRIPT_FILENAME']).'/';
//    $version_txt_path = $app_path.'/Application/Admin/Conf/version.txt';
//    $curent_version = file_get_contents($version_txt_path);
//
//    $vaules = array(
//            'domain'=>$_SERVER['HTTP_HOST'],
//            'last_domain'=>$_SERVER['HTTP_HOST'],
//            'key_num'=>$curent_version,
//            'install_time'=>INSTALL_DATE,
//            'cpu'=>'0001',
//            'mac'=>'0002',
//            'serial_number'=>SERIALNUMBER,
//            );
//     $url = "http://service.tp-shop.cn/index.php?m=Home&c=Index&a=user_push&".http_build_query($vaules);
//     stream_context_set_default(array('http' => array('timeout' => 3)));
//     file_get_contents($url);
//}

/**
 * 面包屑导航  用于后台管理
 * 根据当前的控制器名称 和 action 方法
 */
function navigate_admin()
{
    $navigate = include APP_PATH.'Common/Conf/navigate.php';
    $location = strtolower('Admin/'.CONTROLLER_NAME);
    $arr = array(
        '后台首页'=>'javascript:void();',
        $navigate[$location]['name']=>'javascript:void();',
        $navigate[$location]['action'][ACTION_NAME]=>'javascript:void();',
    );
    return $arr;
}

/**
 * 导出excel
 * @param $strTable	表格内容
 * @param $filename 文件名
 */
function downloadExcel($strTable,$filename)
{
    header("Content-type: application/vnd.ms-excel");
    header("Content-Type: application/force-download");
    header("Content-Disposition: attachment; filename=".$filename."_".date('Y-m-d').".xls");
    header('Expires:0');
    header('Pragma:public');
    echo '<html><meta http-equiv="Content-Type" content="text/html; charset=utf-8" />'.$strTable.'</html>';
}

/**
 * 格式化字节大小
 * @param  number $size      字节数
 * @param  string $delimiter 数字和单位分隔符
 * @return string            格式化后的带单位的大小
 */
function format_bytes($size, $delimiter = '') {
    $units = array('B', 'KB', 'MB', 'GB', 'TB', 'PB');
    for ($i = 0; $size >= 1024 && $i < 5; $i++) $size /= 1024;
    return round($size, 2) . $delimiter . $units[$i];
}

/**
 * 根据id获取地区名字
 * @param $regionId id
 */
function getRegionName($regionId){
    $data = M('region')->where(array('id'=>$regionId))->field('name')->find();
    return $data['name'];
}





/***
 * zhonght
 *
 */

function strexists($string, $find) {
    return !(strpos($string, $find) === FALSE);
}



function file_tree($path) {
    $files = array();
    $ds = glob($path . '/*');
    if (is_array($ds)) {
        foreach ($ds as $entry) {
            if (is_file($entry)) {
                $files[] = $entry;
            }
            if (is_dir($entry)) {
                $rs = file_tree($entry);
                foreach ($rs as $f) {
                    $files[] = $f;
                }
            }
        }
    }
    return $files;
}


//
//function getMenu($act_list){
//
//
//
//}