<?php
namespace Admin\Controller;


class IndexController extends BaseController {

    public function index(){
        $this->pushVersion();
        $act_list = session('act_list');
        $menu_list = $this->getRoleMenu($act_list);
        $this->assign('menu_list',$menu_list);
        $admin_info = getAdminInfo(session('admin_id'));
		$order_amount = M('order')->where("order_status=0 and (pay_status=1 or pay_code='cod')")->count();
		$this->assign('order_amount',$order_amount);
		$this->assign('admin_info',$admin_info);
        $this->display();
    }
   
    public function welcome(){

//        $where = "1 = 1";
        if(is_supplier()){
            $id_lists = M('order_goods')->where(array('admin_id' => session('admin_id'))) -> field('order_id') -> select();
            $temp_string = "";
            if(!empty($id_lists)){
                foreach ($id_lists as $id_list){
                    $temp_string .= $id_list['order_id'].",";
                }
            }
            $temp_string .= "0";
            $where .=  "  order_id in(".$temp_string.")";
        }

        $count['not']  = M('order')->where("  `order_status` = 1 AND `pay_status` = 1 AND `shipping_status` <> 1 AND ".$where)->count(); //待发货
        $count['return'] = M('return_goods')->where("   1 = 1  and status = '0'  AND  ".$where)->count(); //退货
//        dd($count);
        $yesterdayTime = strtotime("-1 day");
        $today = date('Y-m-d ')."00:00:00";
        $todayTime = strtotime($today);
        $where .= " AND add_time > ".$yesterdayTime." AND add_time < ".$todayTime."";
        $count['yesterday'] = M('order')->where($where)->count(); //昨天订单
        $money = M('order')->field("SUM(order_amount) as money")->where($where)->find(); //昨天金额
        if(!empty($money['money'])){
            $money = explode(".", $money['money']);
            $count['moneySum'] = $money;
        }else{
            $count['moneySum'][0] = 0;
            $count['moneySum'][1] = 0;
        }


        $logName = M('admin')->field('user_name')->where(array('admin_id'=>session('admin_id')))->find();
        $role_name = M('admin_role')->field('role_name')->where(array('role_id'=>session('admin_role_id')))->find();
        $logName['role_name'] = $role_name['role_name'];
        $this->assign('logName',$logName);
        $this->assign('count',$count);
//        dd($count);
        $this->display();
    }

    public function map(){
    	$all_menu = $this->getRoleMenu('all');
    	$this->assign('all_menu',$all_menu);
    	$this->display();
    }
    
    public function get_sys_info(){
		$sys_info['os']             = PHP_OS;
		$sys_info['zlib']           = function_exists('gzclose') ? 'YES' : 'NO';//zlib
		$sys_info['safe_mode']      = (boolean) ini_get('safe_mode') ? 'YES' : 'NO';//safe_mode = Off		
		$sys_info['timezone']       = function_exists("date_default_timezone_get") ? date_default_timezone_get() : "no_timezone";
		$sys_info['curl']			= function_exists('curl_init') ? 'YES' : 'NO';	
		$sys_info['web_server']     = $_SERVER['SERVER_SOFTWARE'];
		$sys_info['phpv']           = phpversion();
		$sys_info['ip'] 			= GetHostByName($_SERVER['SERVER_NAME']);
		$sys_info['fileupload']     = @ini_get('file_uploads') ? ini_get('upload_max_filesize') :'unknown';
		$sys_info['max_ex_time'] 	= @ini_get("max_execution_time").'s'; //脚本最大执行时间
		$sys_info['set_time_limit'] = function_exists("set_time_limit") ? true : false;
		$sys_info['domain'] 		= $_SERVER['HTTP_HOST'];
		$sys_info['memory_limit']   = ini_get('memory_limit');		
        $sys_info['version']   	    = file_get_contents('./Application/Admin/Conf/version.txt');
		$mysqlinfo = M()->query("SELECT VERSION() as version");
		$sys_info['mysql_version']  = $mysqlinfo['version'];
		if(function_exists("gd_info")){
			$gd = gd_info();
			$sys_info['gdinfo'] 	= $gd['GD Version'];
		}else {
			$sys_info['gdinfo'] 	= "未知";
		}
		return $sys_info;
    }
    
    
    public function pushVersion()
    {            
        if(!empty($_SESSION['isset_push']))
            return false;    
        $_SESSION['isset_push'] = 1;    
//        error_reporting(0);//关闭所有错误报告
//        $app_path = dirname($_SERVER['SCRIPT_FILENAME']).'/';
//        $version_txt_path = $app_path.'/Application/Admin/Conf/version.txt';
//        $curent_version = file_get_contents($version_txt_path);
//
//        $vaules = array(
//                'domain'=>$_SERVER['SERVER_NAME'],
//                'last_domain'=>$_SERVER['SERVER_NAME'],
//                'key_num'=>$curent_version,
//                'install_time'=>INSTALL_DATE,
//                'serial_number'=>SERIALNUMBER,
//         );
//         $url = "http://service.tp-shop.cn/index.php?m=Home&c=Index&a=user_push&".http_build_query($vaules);
//         stream_context_set_default(array('http' => array('timeout' => 3)));
//         file_get_contents($url);
    }
    
    
    public function getRoleMenu($act_list)
    {
    	$modules = $roleMenu = array();
    	$rs = M('system_module')->where('level>1 AND visible=1')->order('mod_id ASC')->select();


        $pmenu = array();
    	if($act_list=='all'){
    		foreach($rs as $row){
    			if($row['level'] == 3){
    				$row['url'] = U("Admin/".$row['ctl']."/".$row['act']."");
    				$modules[$row['parent_id']][] = $row;//子菜单分组
    			}
    			if($row['level'] == 2){
    				$pmenu[$row['mod_id']] = $row;//二级父菜单
    			}
    		}
    	}else{
    		$act_list = explode(',', $act_list);
    		foreach($rs as $row){
    			if(in_array($row['mod_id'],$act_list)){
    				$row['url'] = U("Admin/".trim($row['ctl'])."/".$row['act']."");
    				$modules[$row['parent_id']][] = $row;//子菜单分组
    			}
    			if($row['level'] == 2){
    				$pmenu[$row['mod_id']] = $row;//二级父菜单
    			}
    		}
    	}
    	$keys = array_keys($modules);//导航菜单
    	foreach ($pmenu as $k=>$val){
    		if(in_array($k, $keys)){
    			$val['subMenu'] = $modules[$k];//子菜单
    			$roleMenu[] = $val;
    		}
    	}
//
//    	dd($roleMenu);

//        $roleMenu = include_once 'Application/Admin/Conf/adminMenu.php';
//        $roleMenu = $roleMenu['admin'];
    	return $roleMenu;
    }
    
    /**
     * ajax 修改指定表数据字段  一般修改状态 比如 是否推荐 是否开启 等 图标切换的
     * table,id_name,id_value,field,value
     */
    public function changeTableVal(){  
            $table = I('table'); // 表名
            $id_name = I('id_name'); // 表主键id名
            $id_value = I('id_value'); // 表主键id值
            $field  = I('field'); // 修改哪个字段
            $value  = I('value'); // 修改字段值                        
            M($table)->where("$id_name = $id_value")->save(array($field=>$value)); // 根据条件保存修改的数据
    }	    

}