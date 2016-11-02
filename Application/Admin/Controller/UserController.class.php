<?php

namespace Admin\Controller;


use Think\AjaxPage;
use Think\Page;

class UserController extends BaseController {

    public function index(){
        $this->display();
    }

    /**
     * 会员列表
     */
    public function ajaxindex(){
        // 搜索条件
        $condition = array();
        I('mobile') ? $condition['mobile'] = I('mobile') : false;
        I('email') ? $condition['email'] = I('email') : false;
        $sort_order = I('order_by','user_id').' '.I('sort','desc');
               
        $model = M('users');
        $count = $model->where($condition)->count();
        $Page  = new AjaxPage($count,10);
        //  搜索条件下 分页赋值
        foreach($condition as $key=>$val) {
            $Page->parameter[$key]   =   urlencode($val);
        }
        
        $userList = $model->where($condition)->order($sort_order)->limit($Page->firstRow.','.$Page->listRows)->select();
                
        $user_id_arr = get_arr_column($userList, 'user_id');
        if(!empty($user_id_arr))
        {
            $first_leader = M('users')->query("select first_leader,count(1) as count  from __PREFIX__users where first_leader in(".  implode(',', $user_id_arr).")  group by first_leader");
            $first_leader = convert_arr_key($first_leader,'first_leader');
            
            $second_leader = M('users')->query("select second_leader,count(1) as count  from __PREFIX__users where second_leader in(".  implode(',', $user_id_arr).")  group by second_leader");
            $second_leader = convert_arr_key($second_leader,'second_leader');            
            
            $third_leader = M('users')->query("select third_leader,count(1) as count  from __PREFIX__users where third_leader in(".  implode(',', $user_id_arr).")  group by third_leader");
            $third_leader = convert_arr_key($third_leader,'third_leader');            
        }
        $this->assign('first_leader',$first_leader);
        $this->assign('second_leader',$second_leader);
        $this->assign('third_leader',$third_leader);
                                
        $show = $Page->show();
        $this->assign('userList',$userList);
        $this->assign('page',$show);// 赋值分页输出
        $this->display();
    }

    /**
     * 会员详细信息查看
     */
    public function detail(){
        $uid = I('get.id');
        $user = D('users')->where(array('user_id'=>$uid))->find();
        if(!$user)
            exit($this->error('会员不存在'));
        if(IS_POST){
            //  会员信息编辑
            $password = I('post.password');
            $password2 = I('post.password2');
            if($password != '' && $password != $password2){
                exit($this->error('两次输入密码不同'));
            }
            if($password == '' && $password2 == ''){
                unset($_POST['password']);
            }else{
                $_POST['password'] = encrypt($_POST['password']);
            }

            $row = M('users')->where(array('user_id'=>$uid))->save($_POST);
            if($row)
                exit($this->success('修改成功'));
            exit($this->error('未作内容修改或修改失败'));
        }
        
        $user['first_lower'] = M('users')->where("first_leader = {$user['user_id']}")->count();
        $user['second_lower'] = M('users')->where("second_leader = {$user['user_id']}")->count();
        $user['third_lower'] = M('users')->where("third_leader = {$user['user_id']}")->count();
 
        $this->assign('user',$user);
        $this->display();
    }

    /**
     * 用户收货地址查看
     */
    public function address(){
        $uid = I('get.id');
        $lists = D('user_address')->where(array('user_id'=>$uid))->select();
        $regionList = M('Region')->getField('id,name');
        $this->assign('regionList',$regionList);
        $this->assign('lists',$lists);
        $this->display();
    }

    /**
     * 删除会员
     */
    public function delete(){
        $uid = I('get.id');
        $row = M('users')->where(array('user_id'=>$uid))->delete();
        if($row){
            $this->success('成功删除会员');
        }else{
            $this->error('操作失败');
        }
    }

    /**
     * 账户资金记录
     */
    public function account_log(){
        $user_id = I('get.id');
        //获取类型
        $type = I('get.type');
        //获取记录总数
        $count = M('account_log')->where(array('user_id'=>$user_id))->count();
        $page = new Page($count);
        $lists  = M('account_log')->where(array('user_id'=>$user_id))->order('change_time desc')->limit($page->firstRow.','.$page->listRows)->select();

        $this->assign('user_id',$user_id);
        $this->assign('page',$page->show());
        $this->assign('lists',$lists);
        $this->display();
    }

    /**
     * 账户资金调节
     */
    public function account_edit(){
        $user_id = I('get.id');
        if(!$user_id > 0)
            $this->error("参数有误");
        if(IS_POST){
            //获取操作类型
            $m_op_type = I('post.money_act_type');
            $user_money = I('post.user_money');
            $user_money =  $m_op_type ? $user_money : 0-$user_money;

            $p_op_type = I('post.point_act_type');
            $pay_points = I('post.pay_points');
            $pay_points =  $p_op_type ? $pay_points : 0-$pay_points;

            $f_op_type = I('post.frozen_act_type');
            $frozen_money = I('post.frozen_money');
            $frozen_money =  $f_op_type ? $frozen_money : 0-$frozen_money;

            $desc = I('post.desc');
            if(!$desc)
                $this->error("请填写操作说明");
            if(accountLog($user_id,$user_money,$pay_points,$desc)){
                $this->success("操作成功",U("Admin/User/account_log",array('id'=>$user_id)));
            }else{
                $this->error("操作失败");
            }
            exit;
        }
        $this->assign('user_id',$user_id);
        $this->display();
    }
    
    public function level(){
    	$act = I('GET.act','add');
    	$this->assign('act',$act);
    	$level_id = I('GET.level_id');
    	$level_info = array();
    	if($level_id){
    		$level_info = D('user_level')->where('level_id='.$level_id)->find();
    		$this->assign('info',$level_info);
    	}
    	$this->display();
    }
    
    public function levelList(){
    	$Ad =  M('user_level');
    	$res = $Ad->where('1=1')->order('level_id')->page($_GET['p'].',10')->select();
    	if($res){
    		foreach ($res as $val){
    			$list[] = $val;
    		}
    	}
    	$this->assign('list',$list);
    	$count = $Ad->where('1=1')->count();
    	$Page = new \Think\Page($count,10);
    	$show = $Page->show();
    	$this->assign('page',$show);
    	$this->display();
    }
    
    public function levelHandle(){
    	$data = I('post.');
    	if($data['act'] == 'add'){
    		$r = D('user_level')->add($data);
    	}
    	if($data['act'] == 'edit'){
    		$r = D('user_level')->where('level_id='.$data['level_id'])->save($data);
    	}
    	 
    	if($data['act'] == 'del'){
    		$r = D('user_level')->where('level_id='.$data['level_id'])->delete();
    		if($r) exit(json_encode(1));
    	}
    	 
    	if($r){
    		$this->success("操作成功",U('Admin/User/levelList'));
    	}else{
    		$this->error("操作失败",U('Admin/User/levelList'));
    	}
    }

    /**
     * 搜索用户名
     */
    public function search_user()
    {
        $search_key = trim(I('search_key'));        
        if(strstr($search_key,'@'))    
        {
            $list = M('users')->where(" email like '%$search_key%' ")->select();        
            foreach($list as $key => $val)
            {
                echo "<option value='{$val['user_id']}'>{$val['email']}</option>";
            }                        
        }
        else
        {
            $list = M('users')->where(" mobile like '%$search_key%' ")->select();        
            foreach($list as $key => $val)
            {
                echo "<option value='{$val['user_id']}'>{$val['mobile']}</option>";
            }            
        } 
        exit;
    }
    
    /**
     * 分销树状关系
     */
    public function ajax_distribut_tree()
    {
          $list = M('users')->where("first_leader = 1")->select();
          $this->display();
    }


    /**
     * 同步粉丝信息
     */
    public function obtainFans(){
        if(IS_POST){
            $data  = I('post.selected');
            if(empty($data)){
                $this->error('没有数据');exit;
            }
            $this->user = M('users');
            $WeChatLogic = new \Common\Logic\WeChatLogic();
            foreach($data as $item){
                $where['user_id'] = $item;
                $user =  $this->user->where($where)->find();
                if( !empty($user["openid"]) ){
                    $userData = $WeChatLogic->WechatFans($user['openid']);
                    $save['head_pic'] = $userData['headimgurl'];
                    $save['nickname'] = $userData['nickname'];
                    if( !empty( $userData['subscribe'] ) ){
                        $save['is_follow'] = 1;
                    }else{
                        $save['is_follow'] = 0;
                    }
                    $save['sync_time'] = time();
                    $res[] = $this->user->where($where) -> save($save);
                    $userRes =  $this->user->where($where)->find();
                    if(empty($userRes['nickname'])){
                        $datas['nickname'] = '龙米会员'.$item;
                        $datas['user_id'] = $item;
                        $this->user->save($datas);
                    }
                    $isin = in_array('1',$res);
                    if($isin){
                        $this->success('拉取成功',U('Admin/User/index'));
                    }else{
                        $this->error("拉取失败");
                    }
                    exit;
                }
            }



        }


    }

    public function feedback(){
        $this->display();
    }

    public function ajaxfeedback(){
        // 搜索条件
        $condition = array();
        I('nickname') ? $condition['nickname'] = I('nickname') : false;
        I('user_id') ? $condition['user_id'] = I('user_id') : false;
        $sort_order = I('order_by','user_id').' '.I('sort','desc');

        $model = M('user_feedback');
        $count = $model->where($condition)->count();
        $Page  = new AjaxPage($count,10);
        //  搜索条件下 分页赋值
        foreach($condition as $key=>$val) {
            $Page->parameter[$key]   =   urlencode($val);
        }

        $userList = $model->where($condition)->order($sort_order)->limit($Page->firstRow.','.$Page->listRows)->select();

        $show = $Page->show();
        $this->assign('userList',$userList);
        $this->assign('page',$show);// 赋值分页输出
        $this->display();
    }

    public function detailfeedback(){
        $id = I('id');
        if(empty($id)){
            $this->error('参数错误');exit;
        }
        $list = M('user_feedback')->where(array('id'=>$id))->find();
        $this->assign('list',$list);
        $this->display();
    }


    //提现审核
    public function withdrawDeposit(){
        $this->display();
    }

    public function ajaxWithdrawDeposit(){
        // 搜索条件
        $condition = array();
        I('nickname') ? $condition['nickname'] = I('nickname') : false;
        I('user_id') ? $condition['user_id'] = I('user_id') : false;
        $sort_order = I('order_by','user_id').' '.I('sort','desc');

        $model = M('withdraw_deposit');
        $count = $model->where($condition)->count();
        $Page  = new AjaxPage($count,10);
        //  搜索条件下 分页赋值
        foreach($condition as $key=>$val) {
            $Page->parameter[$key]   =   urlencode($val);
        }

        $userList = $model->where($condition)->order($sort_order)->limit($Page->firstRow.','.$Page->listRows)->select();

        $show = $Page->show();
        $this->assign('userList',$userList);
        $this->assign('page',$show);// 赋值分页输出
        $this->display();
    }

    //审核通过
    public function checkDeposit(){
        $id = I('id','','int');
        $status = I('status','','int');
        $reason = I('reason');
        $result = checkWithdrawDeposit( $id , $status , $reason );
        if( callbackIsTrue( $result ) ){
            $this->success('操作成功',U('Admin/User/withdrawDeposit'));exit;
        }
        $this->error( getCallbackMessage( $result ) );
    }







    public function commentList(){
        $goodId = I( "goodsId" , null);
        if( is_null( $goodId ) ){
            $where = '';
        }else{
            $where = ' AND  goods_id = "'.$goodId.'" ';
        }
        if( is_supplier() ){
            $id_lists = M('goods') -> where( array( 'admin_id' => session('admin_id') ) ) -> getField('goods_id',true);
            if( !empty($id_lists) ){
                $where .=  " AND goods_id in (" . implode( "," , $id_lists ) . ") ";
            }else{
                $where .=  " AND goods_id = 0  ";
            }
        }
        $goodsComment = M('goods_comment');
        $list['allList']    = $goodsComment -> where('1 = 1 ' . $where) -> order('create_time DESC') -> select(); //全部
        $list['well']       = $goodsComment -> where("level in('4','5') AND is_buyer = 1 " . $where) -> order('create_time DESC') -> select(); //4-5星
        $list['middle']     = $goodsComment -> where("level in('2','3') AND is_buyer = 1 " . $where) -> order('create_time DESC') -> select(); //2-3星
        $list['bad']        = $goodsComment -> where("level in('0','1') AND is_buyer = 1 " . $where) -> order('create_time DESC') -> select(); //0-1星
        $list['visitors']   = $goodsComment -> where("is_buyer = 0 " . $where) -> order('create_time DESC') -> select(); //游客

        $this->assign('list',$list);
        $this->display();
    }

    public function ajaxDleComment(){
        $id = I('id');
        $res = M('goods_comment')->where(array('id'=>$id))->save(array('is_delete'=>1));
        if($res){
            exit(json_encode(callback(true,'删除成功')));
        }
        exit(json_encode(callback(false,'删除失败')));

    }



}