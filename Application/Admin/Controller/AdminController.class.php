<?php
namespace Admin\Controller;

use Think\Verify;

class AdminController extends BaseController {

    public function index(){
    	$res = $list = array();
    	$keywords = I('keywords');
    	if(empty($keywords)){
    		$res = D('admin')->select();
    	}else{
    		$res = D()->query("select * from __PREFIX__admin where user_name like '%$keywords%' order by admin_id");
    	}
    	$role = D('admin_role')->getField('role_id,role_name');
    	if($res && $role){
    		foreach ($res as $val){
    			$val['role'] =  $role[$val['role_id']];
    			$val['add_time'] = date('Y-m-d H:i:s',$val['add_time']);
    			$list[] = $val;
    		}
    	}
    	$this->assign('list',$list);
        $this->display();
    }
    
    public function admin_info(){
    	$admin_id = I('get.admin_id',0);   	
    	if($admin_id){
    		$info = D('admin')->where("admin_id=$admin_id")->find();
                $info['password'] =  "";
    		$this->assign('info',$info);
    	}
    	$act = empty($admin_id) ? 'add' : 'edit';
    	$this->assign('act',$act);
    	$role = D('admin_role')->where('1=1')->select();
    	$this->assign('role',$role);
    	$this->display();
    }
    
    public function adminHandle(){
    	$data = I('post.');
    	if(empty($data['password'])){
    		unset($data['password']);
    	}else{
    		$data['password'] = encrypt($data['password']);
    	}

    	if($_FILES['headerpic']['size'] != 0){
            $uploadConfig = array(
                "savePath" =>"adminAccount/",
                "exts"     => array('jpg','gif','png','jpeg'),
                "saveName" => session('admin_id').'_'.mt_rand(),
                "replace"  => True,
                "maxSize"  => 1024*1024,
            );
            $upload = new \Think\Upload($uploadConfig);//实例化上传类
            $info = $upload->upload();
//            dd($info);
            if($info){
                $data['headerpic'] = $info['headerpic']['urlpath'];
            }else{
                $this->error('头像上传失败');
                exit;
            }
        }
    	if($data['act'] == 'add'){
    		unset($data['admin_id']);    		
    		$data['add_time'] = time();
    		if(D('admin')->where("user_name='".$data['user_name']."'")->count()){
    			$this->error("此用户名已被注册，请更换",U('Admin/Admin/admin_info'));
    		}else{
    			$r = D('admin')->add($data);
    		}
    	}
    	
    	if($data['act'] == 'edit'){
    		$r = D('admin')->where('admin_id='.$data['admin_id'])->save($data);
    	}
    	
        if($data['act'] == 'del' && $data['admin_id']>1){
    		$r = D('admin')->where('admin_id='.$data['admin_id'])->delete();
    		exit(json_encode(1));
    	}
    	
    	if($r){
    		$this->success("操作成功",U('Admin/Admin/index'));
    	}else{
    		$this->error("操作失败",U('Admin/Admin/index'));
    	}
    }
    
    
    /*
     * 管理员登陆
     */
    public function login(){
        if(session('?admin_id') && session('admin_id')>0){
             $this->error("您已登录",U('Admin/Index/index'));
        }
      
        if(IS_POST){
            $verify = new Verify();
            if (!$verify->check(I('post.vertify'), "Admin/Login")) {
            	exit(json_encode(array('status'=>0,'msg'=>'验证码错误')));
            }
            $condition['user_name'] = I('post.username');
            $condition['password'] = I('post.password');
            if(!empty($condition['user_name']) && !empty($condition['password'])){
                $condition['password'] = encrypt($condition['password']);
               	$admin_info = M('admin')->join('__ADMIN_ROLE__ ON __ADMIN__.role_id=__ADMIN_ROLE__.role_id')->where($condition)->find();
                if(is_array($admin_info)){
                    session('admin_id',$admin_info['admin_id']);
                    session('admin_role_id',$admin_info['role_id']);
                    session('act_list',$admin_info['act_list']);
                    $last_login_time = M('admin_log')->where("admin_id = ".$admin_info['admin_id']." and log_info = '后台登录'")->order('log_id desc')->limit(1)->getField('log_time');
                    M('admin')->where("admin_id = ".$admin_info['admin_id'])->save(array('last_login'=>time(),'last_ip'=>  getIP()));
                    session('last_login_time',$last_login_time);                            
                    adminLog('后台登录',__ACTION__);
                    $url = session('from_url') ? session('from_url') : U('Admin/Index/index');
                    exit(json_encode(array('status'=>1,'url'=>$url)));
                }else{
                    exit(json_encode(array('status'=>0,'msg'=>'账号密码不正确')));
                }
            }else{
                exit(json_encode(array('status'=>0,'msg'=>'请填写账号密码')));
            }
        }
        
        $this->display();
    }
    
    /**
     * 退出登陆
     */
    public function logout(){
        session_unset();
        session_destroy();
        $this->success("退出成功",U('Admin/Admin/login'));
    }
    
    /**
     * 验证码获取
     */
    public function vertify()
    {
        $config = array(
            'fontSize' => 30,
            'length' => 4,
            'useCurve' => true,
            'useNoise' => false,
        );    
        $Verify = new Verify($config);
        $Verify->entry("Admin/Login");
    }
    
    public function role(){
    	$list = D('admin_role')->order('role_id desc')->select();
    	$this->assign('list',$list);
    	$this->display();
    }
    
    public function role_info(){
    	$role_id = I('get.role_id');
    	$tree = $detail = array();
    	if($role_id){
    		$detail = D('admin_role')->where("role_id=$role_id")->find();
    		$this->assign('detail',$detail);
    	}

    	$res = D('system_module')->order('mod_id ASC')->select();
    	if($res){
    		foreach($res as $k=>$v){
    			if($detail['act_list']){
    				$act_list = explode(',', $detail['act_list']);
    				$v['enable'] = in_array($v['mod_id'], $act_list) ? 1 : 0;
    			}else{
    				$v['enable'] = 0 ;
    			}    		
    			$modules[$v['mod_id']] = $v;
    		}
    		
    		if($modules){
    			foreach($modules as $k=>$v){
    				if($v['module'] == 'top'){
    					$tree[$k] = $v;
    				}
    			}
    			foreach($modules as $k=>$v){
    				if($v['module'] == 'menu'){
    					$tree[$v['parent_id']]['menu'][$k] = $v;
    				}
    			}
    			foreach($modules as $k=>$v){
    				if($v['module'] == 'module'){
    					$ppk = $modules[$v['parent_id']]['parent_id'];
    					$tree[$ppk]['menu'][$v['parent_id']]['menu'][$k] = $v;
    				}
    			}
    		}
    	}

    	$this->assign('menu_tree',$tree);
    	$this->display();
    }
    
    public function roleSave(){
    	$data = I('post.');
    	$res = $data['data'];
    	$res['act_list'] = is_array($data['menu']) ? implode(',', $data['menu']) : '';
    	if(empty($data['role_id'])){
    		$r = D('admin_role')->add($res);
    	}else{
    		$r = D('admin_role')->where('role_id='.$data['role_id'])->save($res);
    	}
		if($r){
			adminLog('管理角色',__ACTION__);
			$this->success("操作成功!",U('Admin/Admin/role_info',array('role_id'=>$data['role_id'])));
		}else{
			$this->success("操作失败!",U('Admin/Admin/role'));
		}
    }
    
    public function roleDel(){
    	$role_id = I('post.role_id');
    	$admin = D('admin')->where('role_id='.$role_id)->find();
    	if($admin){
    		exit(json_encode("请先清空所属该角色的管理员"));
    	}else{
    		$d = M('admin_role')->where("role_id=$role_id")->delete();
    		if($d){
    			exit(json_encode(1));
    		}else{
    			exit(json_encode("删除失败"));
    		}
    	}
    }
    
    public function log(){
    	$Log = M('admin_log');
    	$p = I('p',1);
    	$logs = $Log->join('__ADMIN__ ON __ADMIN__.admin_id =__ADMIN_LOG__.admin_id')->order('log_time DESC')->page($p.',20')->select();
    	$this->assign('list',$logs);
    	$count = $Log->where('1=1')->count();
    	$Page = new \Think\Page($count,20);
    	$show = $Page->show();
    	$this->assign('page',$show); 	
    	$this->display();
    }

    /**
     * 商户提现申请
     */
    public function createWithdrawDeposit()
    {
        if( !is_supplier() ){
            $this -> error("此功能只对供应商开放");
        }
        $accountInfo = getAccountInfo();
        if(IS_POST){
            $data = I('post.');
            $userLogic = new \Common\Logic\UsersLogic();
            $info = $userLogic->sms_code_verify($data['mobile'],$data['verify'],session('admin_id'));
            if($info['status'] != 1){
                $this->error($info['msg']);exit;
            }
            $data['admin_id'] = session('admin_id');
            $data['state'] = 0;
            $data['create_time'] = time();

            M('admin')->where(array('admin_id'=>$data['admin_id']))->setDec('amount',$data['money']);
            M('admin')->where(array('admin_id'=>$data['admin_id']))->setInc('withdrawals_amount',$data['money']);
            $res = M('admin_withdrawals')->add($data);
            if($res){
                $this->success('申请成功');
            }else{
                $this->error('申请失败');
            }
            exit;
        }

        if(!empty($accountInfo['amount'])){
            $moneySum = explode(".",$accountInfo['amount']);
        }else{
            $moneySum[0] = 0;
            $moneySum[1] = 0;
        }



        $phone = M('admin')->field('phone')->where("admin_id = '".session('admin_id')."'")->find();
        $this->assign('accountMoney',$accountInfo['amount']);
        $this->assign('moneySum',$moneySum);
        $this->assign('phone',$phone['phone']);
        $this->assign('sms_time_out',tpCache('sms.sms_time_out')); // 手机短信超时时间
        $this->display();
    }

    //手机修改验证码发送
    public function send_sms_reg(){
        $mobile = I('send');
        if(!check_mobile($mobile)){
            exit(json_encode(array('status'=>-1,'msg'=>'手机号码格式有误')));
        }
        $userLogic = new \Common\Logic\UsersLogic();
        $code =  rand(1000,9999);
        $send = $userLogic->sms_log($mobile,$code,session('admin_id'));
        if($send['status'] != 1)
            exit(json_encode(array('status'=>-1,'msg'=>$send['msg'])));
        exit(json_encode(array('status'=>1,'msg'=>'验证码已发送，请注意查收')));
    }



    /**
     * 商户提现列表
     */
    public function withdrawDeposit()
    {
        $this->display();
    }


    /**
     * ajax商户提现列表
     */
    public function ajaxWithdrawDeposit()
    {
        $type = I('type');
        switch($type){
            case 'untreated':$where['state'] = 0; //未处理
                break;
            case 'processed':$where['state'] = 1; //以处理
                break;
            case 'reject':$where['state'] = 2; //驳回
                break;
        }
        $prefix = C('DB_PREFIX');
        $list = M('admin_withdrawals')->join("LEFT JOIN ".$prefix."admin ON ".$prefix."admin.admin_id = ".$prefix."admin_withdrawals.admin_id")->where($where)->select(); //->limit($Page->firstRow,$Page->listRows)

        $this->assign('list',$list);
        $this->display();
    }


    /**
     * 商户提现操作
     */
    public function checkWithdrawDeposit()
    {
        if(IS_POST){
            $id = I('id');
            $state = I('state');
            $stateRes = M('admin_withdrawals')->field('state,admin_id,money')->where(array('id'=>$id))->find();
            if($stateRes['state'] != 0){
                exit(json_encode(callback(false,"该申请已处理")));
            }
            switch($state){
                case 'processed':$data['state'] = 1; //以处理
                    break;
                case 'reject':$data['state'] = 2; //驳回
                    break;
            }
            $data['id'] = $id;
            $data['update_time'] = time();
            $manageRes = M('admin_withdrawals')->save($data);
            if($data['state'] == 2){ //驳回请求
                M('admin')->where("admin_id = '".$stateRes['admin_id']."'")->setInc('amount',$stateRes['money']);
                M('admin')->where("admin_id = '".$stateRes['admin_id']."'")->setDec('withdrawals_amount',$stateRes['money']);
            }
            if($manageRes){
                exit(json_encode(callback(true,"处理成功")));
            }else{
                exit(json_encode(callback(false,"处理失败")));
            }



        }

        $this->success("操作成功");
    }
}