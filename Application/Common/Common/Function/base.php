<?php





/**
 * 获取系统数据
 * @return array
 */
function getShopConfig(){
    $shopConfig = array();
    $config = M('config')->cache(true,MY_CACHE_TIME)->select();
    foreach($config as $k => $v)
    {
        if($v['name'] == 'hot_keywords'){
            $shopConfig['hot_keywords'] = explode('|', $v['value']);
        }
        $shopConfig[$v['inc_type'].'_'.$v['name']] = $v['value'];
    }
    return $shopConfig;
}

/**
 * @param $arr
 * @param $key_name
 * @return array
 * 将数据库中查出的列表以指定的 id 作为数组的键名 
 */
function convert_arr_key($arr, $key_name)
{
	$arr2 = array();
	foreach($arr as $key => $val){
		$arr2[$val[$key_name]] = $val;        
	}
	return $arr2;
}

function encrypt($str){
	return md5(C("AUTH_CODE").$str);
}
            
/**
 * 获取数组中的某一列
 * @param type $arr 数组
 * @param type $key_name  列名
 * @return type  返回那一列的数组
 */
function get_arr_column($arr, $key_name)
{
	$arr2 = array();
	foreach($arr as $key => $val){
		$arr2[] = $val[$key_name];        
	}
	return $arr2;
}


/**
 * 获取url 中的各个参数  类似于 pay_code=alipay&bank_code=ICBC-DEBIT
 * @param type $str
 * @return type
 */
function parse_url_param($str){
    $data = array();
    $parameter = explode('&',end(explode('?',$str)));
    foreach($parameter as $val){
        $tmp = explode('=',$val);
        $data[$tmp[0]] = $tmp[1];
    }
    return $data;
}


/**
 * 二维数组排序
 * @param $arr
 * @param $keys
 * @param string $type
 * @return array
 */
function array_sort($arr, $keys, $type = 'desc')
{
    $key_value = $new_array = array();
    foreach ($arr as $k => $v) {
        $key_value[$k] = $v[$keys];
    }
    if ($type == 'asc') {
        asort($key_value);
    } else {
        arsort($key_value);
    }
    reset($key_value);
    foreach ($key_value as $k => $v) {
        $new_array[$k] = $arr[$k];
    }
    return $new_array;
}


/**
 * 多维数组转化为一维数组
 * @param 多维数组
 * @return array 一维数组
 */
function array_multi2single($array)
{
    static $result_array = array();
    foreach ($array as $value) {
        if (is_array($value)) {
            array_multi2single($value);
        } else
            $result_array [] = $value;
    }
    return $result_array;
}

/**
 * 友好时间显示
 * @param $time
 * @return bool|string
 */
function friend_date($time)
{
    if (!$time)
        return false;
    $fdate = '';
    $d = time() - intval($time);
    $ld = $time - mktime(0, 0, 0, 0, 0, date('Y')); //得出年
    $md = $time - mktime(0, 0, 0, date('m'), 0, date('Y')); //得出月
    $byd = $time - mktime(0, 0, 0, date('m'), date('d') - 2, date('Y')); //前天
    $yd = $time - mktime(0, 0, 0, date('m'), date('d') - 1, date('Y')); //昨天
    $dd = $time - mktime(0, 0, 0, date('m'), date('d'), date('Y')); //今天
    $td = $time - mktime(0, 0, 0, date('m'), date('d') + 1, date('Y')); //明天
    $atd = $time - mktime(0, 0, 0, date('m'), date('d') + 2, date('Y')); //后天
    if ($d == 0) {
        $fdate = '刚刚';
    } else {
        switch ($d) {
            case $d < $atd:
                $fdate = date('Y年m月d日', $time);
                break;
            case $d < $td:
                $fdate = '后天' . date('H:i', $time);
                break;
            case $d < 0:
                $fdate = '明天' . date('H:i', $time);
                break;
            case $d < 60:
                $fdate = $d . '秒前';
                break;
            case $d < 3600:
                $fdate = floor($d / 60) . '分钟前';
                break;
            case $d < $dd:
                $fdate = floor($d / 3600) . '小时前';
                break;
            case $d < $yd:
                $fdate = '昨天' . date('H:i', $time);
                break;
            case $d < $byd:
                $fdate = '前天' . date('H:i', $time);
                break;
            case $d < $md:
                $fdate = date('m月d日 H:i', $time);
                break;
            case $d < $ld:
                $fdate = date('m月d日', $time);
                break;
            default:
                $fdate = date('Y年m月d日', $time);
                break;
        }
    }
    return $fdate;
}


/**
 * 返回状态和信息
 * @param $status
 * @param $info
 * @return array
 */
function arrayRes($status, $info, $url = "")
{
    return array("status" => $status, "info" => $info, "url" => $url);
}
       
/**
 * @param $arr
 * @param $key_name
  * @param $key_name2
 * @return array
 * 将数据库中查出的列表以指定的 id 作为数组的键名 数组指定列为元素 的一个数组
 */
function get_id_val($arr, $key_name,$key_name2)
{
	$arr2 = array();
	foreach($arr as $key => $val){
		$arr2[$val[$key_name]] = $val[$key_name2];
	}
	return $arr2;
}

/**
 *  自定义函数 判断 用户选择 从下面的列表中选择 可选值列表：不能为空
 * @param type $attr_values
 * @return boolean
 */
function checkAttrValues($attr_values)
{        
    if((trim($attr_values) == '') && ($_POST['attr_input_type'] == '1'))        
        return false;
    else
        return true;
 }
 
 // 定义一个函数getIP() 客户端IP，
function getIP(){            
    if (getenv("HTTP_CLIENT_IP"))
         $ip = getenv("HTTP_CLIENT_IP");
    else if(getenv("HTTP_X_FORWARDED_FOR"))
            $ip = getenv("HTTP_X_FORWARDED_FOR");
    else if(getenv("REMOTE_ADDR"))
         $ip = getenv("REMOTE_ADDR");
    else $ip = "Unknow";
    return $ip;
}
// 服务器端IP
 function serverIP(){   
  return gethostbyname($_SERVER["SERVER_NAME"]);   
 }  
 
 
 /**
  * 自定义函数递归的复制带有多级子目录的目录
  * 递归复制文件夹
  * @param type $src 原目录
  * @param type $dst 复制到的目录
  */                        
//参数说明：            
//自定义函数递归的复制带有多级子目录的目录
function recurse_copy($src, $dst)
{
	$now = time();
	$dir = opendir($src);
	@mkdir($dst);
	while (false !== $file = readdir($dir)) {
		if (($file != '.') && ($file != '..')) {
			if (is_dir($src . '/' . $file)) {
				recurse_copy($src . '/' . $file, $dst . '/' . $file);
			}
			else {
				if (file_exists($dst . DIRECTORY_SEPARATOR . $file)) {
					if (!is_writeable($dst . DIRECTORY_SEPARATOR . $file)) {
						exit($dst . DIRECTORY_SEPARATOR . $file . '不可写');
					}
					@unlink($dst . DIRECTORY_SEPARATOR . $file);
				}
				if (file_exists($dst . DIRECTORY_SEPARATOR . $file)) {
					@unlink($dst . DIRECTORY_SEPARATOR . $file);
				}
				$copyrt = copy($src . DIRECTORY_SEPARATOR . $file, $dst . DIRECTORY_SEPARATOR . $file);
				if (!$copyrt) {
					echo 'copy ' . $dst . DIRECTORY_SEPARATOR . $file . ' failed<br>';
				}
			}
		}
	}
	closedir($dir);
}


/**
 * 递归删除文件夹
 * @param $dir
 * @param string $file_type
 */
function delFile($dir,$file_type='') {
	if(is_dir($dir)){
		$files = scandir($dir);
		//打开目录 //列出目录中的所有文件并去掉 . 和 ..
		foreach($files as $filename){
			if($filename!='.' && $filename!='..'){
				if(!is_dir($dir.'/'.$filename)){
					if(empty($file_type)){
						unlink($dir.'/'.$filename);
					}else{
						if(is_array($file_type)){
							//正则匹配指定文件
							if(preg_match($file_type[0],$filename)){
								unlink($dir.'/'.$filename);
							}
						}else{
							//指定包含某些字符串的文件
							if(false!=stristr($filename,$file_type)){
								unlink($dir.'/'.$filename);
							}
						}
					}
				}else{
					delFile($dir.'/'.$filename);
					rmdir($dir.'/'.$filename);
				}
			}
		}
	}else{
		if(file_exists($dir)) unlink($dir);
	}
}

 
/**
 * 多个数组的笛卡尔积
*
* @param unknown_type $data
*/
function combineDika() {
	$data = func_get_args();
	$data = current($data);
	$cnt = count($data);
	$result = array();
    $arr1 = array_shift($data);
	foreach($arr1 as $key=>$item) 
	{
		$result[] = array($item);
	}		

	foreach($data as $key=>$item) 
	{                                
		$result = combineArray($result,$item);
	}
	return $result;
}


/**
 * 两个数组的笛卡尔积
 * @param unknown_type $arr1
 * @param unknown_type $arr2
*/
function combineArray($arr1,$arr2) {		 
	$result = array();
	foreach ($arr1 as $item1) 
	{
		foreach ($arr2 as $item2) 
		{
			$temp = $item1;
			$temp[] = $item2;
			$result[] = $temp;
		}
	}
	return $result;
}
/**
 * 将二维数组以元素的某个值作为键 并归类数组
 * array( array('name'=>'aa','type'=>'pay'), array('name'=>'cc','type'=>'pay') )
 * array('pay'=>array( array('name'=>'aa','type'=>'pay') , array('name'=>'cc','type'=>'pay') ))
 * @param $arr 数组
 * @param $key 分组值的key
 * @return array
 */
function group_same_key($arr,$key){
    $new_arr = array();
    foreach($arr as $k=>$v ){
        $new_arr[$v[$key]][] = $v;
    }
    return $new_arr;
}

/**
 * 获取随机字符串
 * @param int $randLength  长度
 * @param int $addtime  是否加入当前时间戳
 * @param int $includenumber   是否包含数字
 * @return string
 */
function get_rand_str($randLength=6,$addtime=1,$includenumber=0){
    if ($includenumber){
        $chars='abcdefghijklmnopqrstuvwxyzABCDEFGHJKLMNPQEST123456789';
    }else {
        $chars='abcdefghijklmnopqrstuvwxyz';
    }
    $len=strlen($chars);
    $randStr='';
    for ($i=0;$i<$randLength;$i++){
        $randStr.=$chars[rand(0,$len-1)];
    }
    $tokenvalue=$randStr;
    if ($addtime){
        $tokenvalue=$randStr.time();
    }
    return $tokenvalue;
}

/**
 * CURL请求
 * @param $url 请求url地址
 * @param $method 请求方法 get post
 * @param null $postfields post数据数组
 * @param array $headers 请求header信息
 * @param bool|false $debug  调试开启 默认false
 * @return mixed
 */
function httpRequest($url, $method, $postfields = null, $headers = array(), $debug = false) {
    $method = strtoupper($method);
    $ci = curl_init();
    /* Curl settings */
    curl_setopt($ci, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
    curl_setopt($ci, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.2; WOW64; rv:34.0) Gecko/20100101 Firefox/34.0");
    curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, 60); /* 在发起连接前等待的时间，如果设置为0，则无限等待 */
    curl_setopt($ci, CURLOPT_TIMEOUT, 7); /* 设置cURL允许执行的最长秒数 */
    curl_setopt($ci, CURLOPT_RETURNTRANSFER, true);
    switch ($method) {
        case "POST":
            curl_setopt($ci, CURLOPT_POST, true);
            if (!empty($postfields)) {
                $tmpdatastr = is_array($postfields) ? http_build_query($postfields) : $postfields;
                curl_setopt($ci, CURLOPT_POSTFIELDS, $tmpdatastr);
            }
            break;
        default:
            curl_setopt($ci, CURLOPT_CUSTOMREQUEST, $method); /* //设置请求方式 */
            break;
    }
    $ssl = preg_match('/^https:\/\//i',$url) ? TRUE : FALSE;
    curl_setopt($ci, CURLOPT_URL, $url);
    if($ssl){
        curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, FALSE); // https请求 不验证证书和hosts
        curl_setopt($ci, CURLOPT_SSL_VERIFYHOST, FALSE); // 不从证书中检查SSL加密算法是否存在
    }
    //curl_setopt($ci, CURLOPT_HEADER, true); /*启用时会将头文件的信息作为数据流输出*/
    curl_setopt($ci, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ci, CURLOPT_MAXREDIRS, 2);/*指定最多的HTTP重定向的数量，这个选项是和CURLOPT_FOLLOWLOCATION一起使用的*/
    curl_setopt($ci, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ci, CURLINFO_HEADER_OUT, true);
    /*curl_setopt($ci, CURLOPT_COOKIE, $Cookiestr); * *COOKIE带过去** */
    $response = curl_exec($ci);
    $requestinfo = curl_getinfo($ci);
    $http_code = curl_getinfo($ci, CURLINFO_HTTP_CODE);
    if ($debug) {
        echo "=====post data======\r\n";
        var_dump($postfields);
        echo "=====info===== \r\n";
        print_r($requestinfo);
        echo "=====response=====\r\n";
        print_r($response);
    }
    curl_close($ci);
    return $response;
	//return array($http_code, $response,$requestinfo);
}

/**
 * 过滤数组元素前后空格 (支持多维数组)
 * @param $array 要过滤的数组
 * @return array|string
 */
function trim_array_element($array){
    if(!is_array($array))
        return trim($array);
    return array_map('trim_array_element',$array);
}

/**
 * 检查手机号码格式
 * @param $mobile 手机号码
 */
function check_mobile($mobile){
    if(preg_match('/1[34578]\d{9}$/',$mobile))
        return true;
    return false;
}

/**
 * 检查邮箱地址格式
 * @param $email 邮箱地址
 */
function check_email($email){
    if(filter_var($email,FILTER_VALIDATE_EMAIL))
        return true;
    return false;
}


/**
 *   实现中文字串截取无乱码的方法
 */
function getSubstr($string, $start, $length) {
      if(mb_strlen($string,'utf-8')>$length){
          $str = mb_substr($string, $start, $length,'utf-8');
          return $str.'...';
      }else{
          return $string;
      }
}


/**
 * 判断当前访问的用户是  PC端  还是 手机端  返回true 为手机端  false 为PC 端
 * @return boolean
 */
/**
　　* 是否移动端访问访问
　　*
　　* @return bool
　　*/
function isMobile()
{
        // 如果有HTTP_X_WAP_PROFILE则一定是移动设备
    if (isset ($_SERVER['HTTP_X_WAP_PROFILE']))
    return true;

    // 如果via信息含有wap则一定是移动设备,部分服务商会屏蔽该信息
    if (isset ($_SERVER['HTTP_VIA']))
    {
    // 找不到为flase,否则为true
    return stristr($_SERVER['HTTP_VIA'], "wap") ? true : false;
    }
    // 脑残法，判断手机发送的客户端标志,兼容性有待提高
    if (isset ($_SERVER['HTTP_USER_AGENT']))
    {
        $clientkeywords = array ('nokia','sony','ericsson','mot','samsung','htc','sgh','lg','sharp','sie-','philips','panasonic','alcatel','lenovo','iphone','ipod','blackberry','meizu','android','netfront','symbian','ucweb','windowsce','palm','operamini','operamobi','openwave','nexusone','cldc','midp','wap','mobile');
        // 从HTTP_USER_AGENT中查找手机浏览器的关键字
        if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT'])))
            return true;
    }
        // 协议法，因为有可能不准确，放到最后判断
    if (isset ($_SERVER['HTTP_ACCEPT']))
    {
    // 如果只支持wml并且不支持html那一定是移动设备
    // 如果支持wml和html但是wml在html之前则是移动设备
        if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false) && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html'))))
        {
            return true;
        }
    }
            return false;
 } 

//php获取中文字符拼音首字母
function getFirstCharter($str){
      if(empty($str))
      {
            return '';          
      }
      $fchar=ord($str{0});
      if($fchar>=ord('A')&&$fchar<=ord('z')) return strtoupper($str{0});
      $s1=iconv('UTF-8','gb2312',$str);
      $s2=iconv('gb2312','UTF-8',$s1);
      $s=$s2==$str?$s1:$str;
      $asc=ord($s{0})*256+ord($s{1})-65536;
     if($asc>=-20319&&$asc<=-20284) return 'A';
     if($asc>=-20283&&$asc<=-19776) return 'B';
     if($asc>=-19775&&$asc<=-19219) return 'C';
     if($asc>=-19218&&$asc<=-18711) return 'D';
     if($asc>=-18710&&$asc<=-18527) return 'E';
     if($asc>=-18526&&$asc<=-18240) return 'F';
     if($asc>=-18239&&$asc<=-17923) return 'G';
     if($asc>=-17922&&$asc<=-17418) return 'H';
     if($asc>=-17417&&$asc<=-16475) return 'J';
     if($asc>=-16474&&$asc<=-16213) return 'K';
     if($asc>=-16212&&$asc<=-15641) return 'L';
     if($asc>=-15640&&$asc<=-15166) return 'M';
     if($asc>=-15165&&$asc<=-14923) return 'N';
     if($asc>=-14922&&$asc<=-14915) return 'O';
     if($asc>=-14914&&$asc<=-14631) return 'P';
     if($asc>=-14630&&$asc<=-14150) return 'Q';
     if($asc>=-14149&&$asc<=-14091) return 'R';
     if($asc>=-14090&&$asc<=-13319) return 'S';
     if($asc>=-13318&&$asc<=-12839) return 'T';
     if($asc>=-12838&&$asc<=-12557) return 'W';
     if($asc>=-12556&&$asc<=-11848) return 'X';
     if($asc>=-11847&&$asc<=-11056) return 'Y';
     if($asc>=-11055&&$asc<=-10247) return 'Z';
     return null;
}




/**
 * 获取文件类型后缀
 * @param $file_name
 * @return mixed|string
 *
 */
function extend($file_name){

    $extend = pathinfo($file_name);

    $extend = strtolower($extend["extension"]);

    return $extend;

}


/**
 * 根据条件查表 返回存不存在
 * @param $tableName
 * @param array $condition
 * @return bool
 */
function isExistenceDataWithCondition( $tableName , $condition = array() ){
    $result = getCountWithCondition( $tableName , $condition);
    if( $result <= 0 ){
        return false;
    }
    return true;
}

/**
 * 根据条件查表 返回数量
 * @param $tableName
 * @param array $condition
 * @return mixed
 */
function getCountWithCondition( $tableName , $condition = array() ){
    return M($tableName) -> where($condition) -> count();
}

/**
 * 根据条件查表 返回数据，单条
 * @param $tableName
 * @param array $condition
 * @param string $field
 * @return mixed
 */
function findDataWithCondition( $tableName , $condition = array() , $field = " * "){
    return M($tableName) -> where($condition) ->field($field) -> find();
}

/**
 * 根据条件查表 返回数据，多条
 * @param $tableName
 * @param array $condition
 * @param string $field
 * @return mixed
 */
function selectDataWithCondition( $tableName , $condition = array() , $field = " * "){
    return M($tableName) -> where($condition) ->field($field) -> select();
}

/**
 * 插入表 返回成不成功
 * @param $tableName
 * @param array $data
 * @return bool
 */
function isSuccessToAddData( $tableName , $data = array() ){
    $result = addData( $tableName , $data );
    if( empty($result) ){
        return false;
    }
    return true;
}

/**
 * 插入表
 * @param $tableName
 * @param array $data
 * @return mixed
 */
function addData( $tableName , $data = array() ){
    return M( $tableName ) -> add( $data );
}

/**
 * 修改数据
 * @param $tableName
 * @param $condition
 * @param $data
 * @return bool
 */
function saveData( $tableName , $condition , $data ){
    return M( $tableName ) -> where( $condition ) -> save( $data );
}


/**
 * 获取购物车数量
 * @param $sessionId
 * @param null $userId
 * @return mixed
 */
function getCartNumber( $sessionId , $userId = null ){
    $condition = array(
        "session_id" => $sessionId
    );
    if( !is_null($userId) ){
        $condition["user_id"] = $userId;
    }
    return M('cart') -> where( $condition )->count();
}

/**
 * 查看是否登录状态
 * @return bool
 */
function isLoginState(){
    if( session('auth') == true){
       return true;
    }
    return false;
}


/**
 * 获取配置数组
 * @return mixed
 */
function getConfigArray(){
    $appPath = dirname($_SERVER['SCRIPT_FILENAME']);
    $configPath = $appPath.'/Application/Common/Conf/weChat.php';
    $configArray =  include_once $configPath;
    return $configArray;
}

/**
 * json_encode 相当于 json_encode($value, JSON_UNESCAPED_UNICODE);
 * @param $value
 * @return mixed|string
 */
function jsonEncodeEx($value)
{
    if (version_compare(PHP_VERSION,'5.4.0','<'))
    {
        $str = json_encode($value);
        $str = preg_replace_callback(
            "#\\\u([0-9a-f]{4})#i",
            function($matchs)
            {
                return iconv('UCS-2BE', 'UTF-8', pack('H4', $matchs[1]));
            },
            $str
        );
        return $str;
    }
    else
    {
        return json_encode($value, JSON_UNESCAPED_UNICODE);
    }
}


/**
 * 手机端吐司提示
 * @param $jumpUrl
 * @param null $message
 * @param null $error
 */
function mobileJumpToast( $jumpUrl , $message = null , $error = null ){
    if( !is_null($message) ){
        session('mobileMessage', $message);
    }
    if( !is_null($error) ){
        session('mobileMessage', $error);
    }

    header("Location: ".$jumpUrl);exit;
//    echo ($_COOKIE['mobileMessage']);
//
//    exit;
    echo "<html>";
    echo "<meta charset='utf-8'>";
    echo "<script>";
//    echo "".cookie('mobileMessage')."";
//    echo "var objName = 'mobileMessage';";
//    echo "var objValue = '".$mobileMessage."';";
//    echo "var objHours = '3600';";
//    echo "var str = objName + '=' + escape(objValue);";
//    echo "if (objHours > 0) {";
//    echo "var date = new Date();";
//    echo "var ms = objHours * 3600 * 1000;";
//    echo "date.setTime(date.getTime() + ms);";
//    echo "str += '; expires=' + date.toGMTString();";
//    echo "}";
//    echo "  document.cookie = str;";
    echo "window.location.href = '".$jumpUrl."' ";
    echo "</script>";
    echo "</html>";
    exit;
}

/**
 * 地址跳转
 *
 */
function addressTheJump($way = null){
    if(empty($way)){
        return cookie('skip_url');
    }
    if($way == 'cart2'){
        $urlJump = 'Cart/'.$way;
    }else if($way == 'edit_details'){
        $urlJump ='User/'.$way;
    }else if($way == 'exchangeInfo' ){

        $urlJump = 'Exchange/'.$way;
    }
    cookie('skip_url',$urlJump);
}

/**
 * 随机数生成
 * @param int $length
 * @return int
 */
function generateCode($length = 4) {
    $min = pow(10 , ($length - 1));
    $max = pow(10, $length) - 1;
    return rand($min, $max);
}

//
//function is_base64($str){
//    if(!is_string($str)){
//        return false;
//    }
//    return $str == base64_encode(base64_decode($str));
//}
//
//function is_serialized($data, $strict = true) {
//    if (!is_string($data)) {
//        return false;
//    }
//    $data = trim($data);
//    if ('N;' == $data) {
//        return true;
//    }
//    if (strlen($data) < 4) {
//        return false;
//    }
//    if (':' !== $data[1]) {
//        return false;
//    }
//    if ($strict) {
//        $lastc = substr($data, -1);
//        if (';' !== $lastc && '}' !== $lastc) {
//            return false;
//        }
//    } else {
//        $semicolon = strpos($data, ';');
//        $brace = strpos($data, '}');
//        if (false === $semicolon && false === $brace)
//            return false;
//        if (false !== $semicolon && $semicolon < 3)
//            return false;
//        if (false !== $brace && $brace < 4)
//            return false;
//    }
//    $token = $data[0];
//    switch ($token) {
//        case 's' :
//            if ($strict) {
//                if ('"' !== substr($data, -2, 1)) {
//                    return false;
//                }
//            } elseif (false === strpos($data, '"')) {
//                return false;
//            }
//        case 'a' :
//        case 'O' :
//            return (bool)preg_match("/^{$token}:[0-9]+:/s", $data);
//        case 'b' :
//        case 'i' :
//        case 'd' :
//            $end = $strict ? '$' : '';
//            return (bool)preg_match("/^{$token}:[0-9.E-]+;$end/", $data);
//    }
//    return false;
//}
//
//function iunserializer($value) {
//    if (empty($value)) {
//        return '';
//    }
//    if (!is_serialized($value)) {
//        return $value;
//    }
//    $result = unserialize($value);
//    if ($result === false) {
//        $temp = preg_replace('!s:(\d+):"(.*?)";!se', "'s:'.strlen('$2').':\"$2\";'", $value);
//        return unserialize($temp);
//    }
//    return $result;
//}

/**
 * 数组互换
 * @param $array
 * @return array
 */
function arrayExchangeKeyAndValue($array){
    $newArray = array();
    foreach($array as $k=>$v){
        $newArray[$v]=$k;
    }
    return $newArray;
}


/**
 * 检测用户手机型号
 * @return string
 */
function getOS()
{
    $ua = $_SERVER['HTTP_USER_AGENT'];//这里只进行IOS和Android两个操作系统的判断，其他操作系统原理一样
    if (strpos($ua, 'Android') !== false) {//strpos()定位出第一次出现字符串的位置，这里定位为0
        preg_match("/(?<=Android )[\d\.]{1,}/", $ua, $version);
        return 'Platform:Android OS_Version:'.$version[0];
    } elseif (strpos($ua, 'iPhone') !== false) {
        preg_match("/(?<=CPU iPhone OS )[\d\_]{1,}/", $ua, $version);
        return 'Platform:iPhone OS_Version:'.str_replace('_', '.', $version[0]);
    } elseif (strpos($ua, 'iPad') !== false) {
        preg_match("/(?<=CPU OS )[\d\_]{1,}/", $ua, $version);
        return 'Platform:iPad OS_Version:'.str_replace('_', '.', $version[0]);
    }
}

/**
 * 获取海报信息
 * @return array|mixed
 */
function getPosterInfo(){
    $posterInfo = M('poster') -> find();
    if( empty($posterInfo) ){
        return array();
    }
    $setting = unserialize($posterInfo['setting']);
    if( !empty($setting['qrCode']) ){
        $posterInfo['qrCode'] = $setting['qrCode'];
        $tempArray = explode("|",$setting['qrCode']);
        $posterInfo['qrCode_t'] = $tempArray[0];
        $posterInfo['qrCode_l'] = $tempArray[1];
        $posterInfo['qrCode_w'] = $tempArray[2];
        $posterInfo['qrCode_h'] = $tempArray[3];
    }
    if( !empty($setting['avatar']) ){
        $posterInfo['avatar'] = $setting['avatar'];
        $tempArray = explode("|",$setting['avatar']);
        $posterInfo['avatar_t'] = $tempArray[0];
        $posterInfo['avatar_l'] = $tempArray[1];
        $posterInfo['avatar_w'] = $tempArray[2];
        $posterInfo['avatar_h'] = $tempArray[3];
    }
    if( !empty($setting['original_img']) ){
        $posterInfo['original_img'] = $setting['original_img'];
    }
    return $posterInfo;
}


/*
* ============================== 截取含有 html标签的字符串 =========================
* @param (string) $str   待截取字符串
* @param (int)  $lenth  截取长度
* @param (string) $repalce 超出的内容用$repalce替换之（该参数可以为带有html标签的字符串）
* @param (string) $anchor 截取锚点，如果截取过程中遇到这个标记锚点就截至该锚点处
* @return (string) $result 返回值
* @demo  $res = cut_html_str($str, 256, '...'); //截取256个长度，其余部分用'...'替换
* -------------------------------------------------------------------------------
* $ Author: Wang Jian.  |   Email: wj@yurendu.com   |   Date: 2014/03/16
* ===============================================================================
*/
function cutHtmlStr($str, $lenth, $replace='', $anchor='<!-- break -->'){

    $str = htmlspecialchars_decode($str);
    $str = strip_tags($str);
    $_lenth = mb_strlen($str, "utf-8"); // 统计字符串长度（中、英文都算一个字符）
    if($_lenth <= $lenth){
        return $str;    // 传入的字符串长度小于截取长度，原样返回
    }
    $strlen_var = strlen($str);     // 统计字符串长度（UTF8编码下-中文算3个字符，英文算一个字符）
    if(strpos($str, '<') === false){
        return mb_substr($str, 0, $lenth);  // 不包含 html 标签 ，直接截取
    }
    if($e = strpos($str, $anchor)){
        return mb_substr($str, 0, $e);  // 包含截断标志，优先
    }
    $html_tag = 0;  // html 代码标记
    $result = '';   // 摘要字符串
    $html_array = array('left' => array(), 'right' => array()); //记录截取后字符串内出现的 html 标签，开始=>left,结束=>right
    /*
    * 如字符串为：<h3><p><b>a</b></h3>，假设p未闭合，数组则为：array('left'=>array('h3','p','b'), 'right'=>'b','h3');
    * 仅补全 html 标签，<? <% 等其它语言标记，会产生不可预知结果
    */
    for($i = 0; $i < $strlen_var; ++$i) {
        if(!$lenth) break;  // 遍历完之后跳出
        $current_var = substr($str, $i, 1); // 当前字符
        if($current_var == '<'){ // html 代码开始
            $html_tag = 1;
            $html_array_str = '';
        }else if($html_tag == 1){ // 一段 html 代码结束
            if($current_var == '>'){
                $html_array_str = trim($html_array_str); //去除首尾空格，如 <br / > < img src="" / > 等可能出现首尾空格
                if(substr($html_array_str, -1) != '/'){ //判断最后一个字符是否为 /，若是，则标签已闭合，不记录
                    // 判断第一个字符是否 /，若是，则放在 right 单元
                    $f = substr($html_array_str, 0, 1);
                    if($f == '/'){
                        $html_array['right'][] = str_replace('/', '', $html_array_str); // 去掉 '/'
                    }else if($f != '?'){ // 若是?，则为 PHP 代码，跳过
                        // 若有半角空格，以空格分割，第一个单元为 html 标签。如：<h2 class="a"> <p class="a">
                        if(strpos($html_array_str, ' ') !== false){
                            // 分割成2个单元，可能有多个空格，如：<h2 class="" id="">
                            $html_array['left'][] = strtolower(current(explode(' ', $html_array_str, 2)));
                        }else{
                            //若没有空格，整个字符串为 html 标签，如：<b> <p> 等，统一转换为小写
                            $html_array['left'][] = strtolower($html_array_str);
                        }
                    }
                }
                $html_array_str = ''; // 字符串重置
                $html_tag = 0;
            }else{
                $html_array_str .= $current_var; //将< >之间的字符组成一个字符串,用于提取 html 标签
            }
        }else{
            --$lenth; // 非 html 代码才记数
        }
        $ord_var_c = ord($str{$i});
        switch (true) {
            case (($ord_var_c & 0xE0) == 0xC0): // 2 字节
                $result .= substr($str, $i, 2);
                $i += 1; break;
            case (($ord_var_c & 0xF0) == 0xE0): // 3 字节
                $result .= substr($str, $i, 3);
                $i += 2; break;
            case (($ord_var_c & 0xF8) == 0xF0): // 4 字节
                $result .= substr($str, $i, 4);
                $i += 3; break;
            case (($ord_var_c & 0xFC) == 0xF8): // 5 字节
                $result .= substr($str, $i, 5);
                $i += 4; break;
            case (($ord_var_c & 0xFE) == 0xFC): // 6 字节
                $result .= substr($str, $i, 6);
                $i += 5; break;
            default: // 1 字节
                $result .= $current_var;
        }
    }
    if($html_array['left']){ //比对左右 html 标签，不足则补全
        $html_array['left'] = array_reverse($html_array['left']); //翻转left数组，补充的顺序应与 html 出现的顺序相反
        foreach($html_array['left'] as $index => $tag){
            $key = array_search($tag, $html_array['right']); // 判断该标签是否出现在 right 中
            if($key !== false){ // 出现，从 right 中删除该单元
                unset($html_array['right'][$key]);
            }else{ // 没有出现，需要补全
                $result .= '</'.$tag.'>';
            }
        }
    }
    return $result.$replace;
}


