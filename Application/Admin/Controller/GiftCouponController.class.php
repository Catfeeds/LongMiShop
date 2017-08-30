<?php
namespace Admin\Controller;
use Think\AjaxPage;

class GiftCouponController extends BaseController {
    /**----------------------------------------------*/
    /*                优惠券控制器                  */
    /**----------------------------------------------*/
    /*
     * 优惠券类型列表
     */
    public function index(){
        //获取优惠券列表

        $count =  M('gift_coupon')->count();
        $Page = new \Think\Page($count,10);
        $show = $Page -> show();
        $lists = M('gift_coupon')->order('create_time desc')->limit($Page->firstRow.','.$Page->listRows)->select();
        if( !empty($lists)){
            foreach ($lists as $key =>$item){
                $condition = array(
                    'state' => "2",
                    'user_id'=>array("neq",0),
                    'gift_coupon_id'=>$item['id'],
                );
                $lists[$key]["use_num"] = getCountWithCondition( 'coupon_code' , $condition );
            }
        }
        $this -> assign('lists',$lists);
        $this -> assign('page',$show);// 赋值分页输出
        $this -> assign('coupons',C('COUPON_TYPE'));
        $this -> display();
    }

    /*
     * 添加编辑一个优惠券类型
     */
    public function coupon_info(){
        if(IS_POST){
            $data = I('post.');
            $data['send_start_time'] = strtotime($data['send_start_time']);
            $data['send_end_time'] = strtotime($data['send_end_time']);
            $data['use_end_time'] = strtotime($data['use_end_time']);
            $data['use_start_time'] = strtotime($data['use_start_time']);
            if($data['send_start_time'] > $data['send_end_time']){
                $this->error('发放日期填写有误');
            }





            if(empty($data['id'])){
                $data['create_time'] = time();
                $data['is_create_code'] = 0;
                $row = M('gift_coupon')->add($data);
            }else{
                $data['update_time'] = time();
                $row =  M('gift_coupon') -> where(array('id'=>$data['id']))->save($data);
            }

            foreach($data['list'] as $key=>$item){
                $lisTdata['goods_id'] = $item['goods_id'];
                $lisTdata['goods_sn'] = $item['goods_sn'];
                $lisTdata['goods_name'] = $item['goods_name'];
                $lisTdata['admin_id'] = $item['admin_id'];
                if( !empty($item['spec']) ){
                    foreach($item['spec'] as $item_sp){
                        $lisTdata['goods_num'] = $item_sp['goods_num'];
                        $lisTdata['spec_key_name'] = $item_sp['spec_key_name'];
                        $lisTdata['spec_key'] = $item_sp['spec_key'];
                    }
                }else{
                    $lisTdata['goods_num'] = $item['goods_num'];
                }


                // dd($lisTdata);

                if( empty($data['id'])){
                    $lisTdata['gift_coupon_id'] = $row;
                    $lisTdata['create_time'] = time();
                    M('gift_coupon_goods_list')->add($lisTdata);
                }else{
                    if(empty($item['id'])){
                        $lisTdata['gift_coupon_id'] = $data['id'];
                        $lisTdata['create_time'] = time();
                        M('gift_coupon_goods_list')->add($lisTdata);continue;
                    }
                    $lisTdata['gift_coupon_id'] = $data['id'];
                    $lisTdata['update_time'] = time();
                    $lisTdata['id'] = $item['id'];
                    M('gift_coupon_goods_list') -> where($where)->save($lisTdata);

                }


            }


            if(!$row)
                $this->error('编辑礼品券失败');
            $this->success('编辑礼品券成功',U('Admin/GiftCoupon/index'));
            exit;
        }
        $cid = I('get.id');
        if($cid){
            $gift_coupon = M('gift_coupon') -> where(array('id'=>$cid))->find();
            $goodsList = M('gift_coupon_goods_list') -> where(array('gift_coupon_id'=>$cid))->select();
            // dd($goodsList);
            $this -> assign('coupon',$gift_coupon);
            $this -> assign('goodsList',$goodsList);
        }else{
            $def['send_start_time'] = strtotime("+1 day");
            $def['send_end_time'] = strtotime("+1 month");
            $def['use_start_time'] = strtotime("+1 day");
            $def['use_end_time'] = strtotime("+2 month");
            $this -> assign('coupon',$def);
        }
        $this -> display();
    }

    //删除商品
    public function delGoodsList(){
        $where['id'] = I('id');
        $res = M('gift_coupon_goods_list') -> where($where)->delete();
        if($res){
            exit(json_encode(callback(true,'删除成功')));
        }
        exit(json_encode(callback(false,'删除失败')));
    }

    //生成兑换码
    public function convert(){
        $data['gift_coupon_id'] = I('id');
        $return = findDataWithCondition("gift_coupon",array('id'=>$data['gift_coupon_id'],'is_create_code'=>0));
        if(!empty($return)){
            for($i= 0;$i < $return['create_num'];$i++){
                $data['state'] = 0;
                $data['create_time'] = time();
                do{
                    $code = get_rand_str(8,0,1);//获取随机8位字符串
                    $check_exist = findDataWithCondition('coupon_list',array('code'=>$code),"code");
                    if( empty( $check_exist ) ){
                        $check_exist = findDataWithCondition('coupon_code',array('code'=>$code),"code");
                    }
                }while($check_exist);
                $data['code'] = $code;
                M('coupon_code')->add($data);
            }
            M('gift_coupon')->save(array('id'=>$data['gift_coupon_id'],'is_create_code'=>1));
            exit(json_encode(callback(true,'兑换码生成成功')));
        }
        exit(json_encode(callback(false,'兑换码生成失败')));
    }


    //导入excel
    public function excelCreate(){
        $data['gift_coupon_id'] = I('id');
        $saveName = mt_rand(1,9999999);
        $uploadConfig = array(
            "savePath" =>"execl/",
            "exts"     => array('xls'),
            "saveName" => ''.$saveName.'',
            "replace"  => True,
            "maxSize"  => 1024*1024,
            'autoSub'  => false,
        );
        $upload = new \Think\Upload($uploadConfig);//实例化上传类
        $info = $upload->upload();
        if(!$info) {// 上传错误提示错误信息
            exit(json_encode(callback(false,$upload->getError())));
        }
        $path = $_SERVER['DOCUMENT_ROOT'].$info['create']['urlpath'];

        $resDate = excel_import($path);
        if(empty($resDate)){
            exit(json_encode(callback(false,'兑换码生成失败')));
        }
        foreach($resDate as $item){
            $data['state'] = 0;
            $data['create_time'] = time();
            $data['code'] = $item['A'];
            M('coupon_code')->add($data);
        }
        M('gift_coupon')->save(array('id'=>$data['gift_coupon_id'],'is_create_code'=>1));
        delFile('./Public/upload/execl');
        exit(json_encode(callback(true,'兑换码生成成功')));

    }


    /**
     * 选择搜索商品
     */
    public function search_goods()
    {
        $brandList =  M("brand")->select();
        $categoryList =  M("goods_category")->select();
        $this -> assign('categoryList',$categoryList);
        $this -> assign('brandList',$brandList);
        $where = ' is_on_sale = 1 ';//搜索条件
        I('intro')  && $where = "$where and ".I('intro')." = 1";
        if(I('cat_id')){
            $this -> assign('cat_id',I('cat_id'));
            $grandson_ids = getCatGrandson(I('cat_id'));
            $where = " $where  and cat_id in(".  implode(',', $grandson_ids).") "; // 初始化搜索条件

        }
        if(I('brand_id')){
            $this -> assign('brand_id',I('brand_id'));
            $where = "$where and brand_id = ".I('brand_id');
        }
        if(!empty($_REQUEST['keywords']))
        {
            $this -> assign('keywords',I('keywords'));
            $where = "$where and (goods_name like '%".I('keywords')."%' or keywords like '%".I('keywords')."%')" ;
        }
        $goodsList = M('goods') -> where($where)->order('goods_id DESC')->limit(10)->select();

        foreach($goodsList as $key => $val)
        {
            $spec_goods = M('spec_goods_price') -> where("goods_id = {$val['goods_id']}")->select();
            $goodsList[$key]['spec_goods'] = $spec_goods;
        }
        // dd($goodsList);
        $this -> assign('goodsList',$goodsList);
        $this -> display();
    }

    /*
    * 优惠券发放
    */
    public function make_coupon(){
        //获取优惠券ID
        $cid = I('get.id');
        $type = I('get.type');
        //查询是否存在优惠券
        $data = M('coupon') -> where(array('id'=>$cid))->find();
        $remain = $data['createnum'] - $data['send_num'];//剩余派发量
        if($remain<=0) $this->error($data['name'].'已经发放完了');
        if(!$data) $this->error("优惠券类型不存在");
        if($type != 4) $this->error("该优惠券类型不支持发放");
        if(IS_POST){
            $num  = I('post.num');
            if($num>$remain) $this->error($data['name'].'发放量不够了');
            if(!$num > 0) $this->error("发放数量不能小于0");
            $add['cid'] = $cid;
            $add['type'] = $type;
            $add['send_time'] = time();
            for($i=0;$i<$num; $i++){
                do{
                    $code = get_rand_str(8,0,1);//获取随机8位字符串
                    $check_exist = findDataWithCondition('coupon_list',array('code'=>$code),"code");
                    if( empty( $check_exist ) ){
                        $check_exist = findDataWithCondition('coupon_code',array('code'=>$code),"code");
                    }
                }while($check_exist);
                $add['code'] = $code;
                M('coupon_list')->add($add);
            }
            M('coupon') -> where("id=$cid")->setInc('send_num',$num);
            adminLog("发放".$num.'张'.$data['name']);
            $this->success("发放成功",U('Admin/Coupon/index'));
            exit;
        }
        $this -> assign('coupon',$data);
        $this -> display();
    }

    public function ajax_get_user(){
        //搜索条件
        $condition = array();
        I('mobile') ? $condition['mobile'] = I('mobile') : false;
        I('email') ? $condition['email'] = I('email') : false;
        $nickname = I('nickname');
        if(!empty($nickname)){
            $condition['nickname'] = array('like',"%$nickname%");
        }
        $model = M('users');
        $count = $model->where($condition)->count();
        $Page  = new AjaxPage($count,10);
        foreach($condition as $key=>$val) {
            $Page->parameter[$key] = urlencode($val);
        }
        $show = $Page -> show();
        $userList = $model->where($condition)->order("user_id desc")->limit($Page->firstRow.','.$Page->listRows)->select();

        $user_level = M('user_level')->getField('level_id,level_name',true);
        $this -> assign('user_level',$user_level);
        $this -> assign('userList',$userList);
        $this -> assign('page',$show);
        $this -> display();
    }

    public function send_coupon(){
        $cid = I('cid');
        if(IS_POST){
            $level_id = I('level_id');
            $user_id = I('user_id');
            $insert = '';
            $coupon = M('coupon') -> where("id=$cid")->find();
            if($coupon['createnum']>0){
                $remain = $coupon['createnum'] - $coupon['send_num'];//剩余派发量
                if($remain<=0) $this->error($coupon['name'].'已经发放完了');
            }

            if(empty($user_id) && $level_id>=0){
                if($level_id==0){
                    $user = M('users') -> where("is_lock=0")->select();
                }else{
                    $user = M('users') -> where("is_lock=0 and level_id=$level_id")->select();
                }
                if($user){
                    $able = count($user);//本次发送量
                    if($coupon['createnum']>0 && $remain<$able){
                        $this->error($coupon['name'].'派发量只剩'.$remain.'张');
                    }
                    foreach ($user as $k=>$val){
                        $user_id = $val['user_id'];
                        $time = time();
                        $gap = ($k+1) == $able ? '' : ',';
                        $insert .= "($cid,1,$user_id,$time)$gap";
                    }
                }
            }else{
                $able = count($user_id);//本次发送量
                if($coupon['createnum']>0 && $remain<$able){
                    $this->error($coupon['name'].'派发量只剩'.$remain.'张');
                }
                foreach ($user_id as $k=>$v){
                    $time = time();
                    $gap = ($k+1) == $able ? '' : ',';
                    $insert .= "($cid,1,$v,$time)$gap";
                }
            }
            $sql = "insert into __PREFIX__coupon_list (`cid`,`type`,`uid`,`send_time`) VALUES $insert";
            M()->execute($sql);
            M('coupon') -> where("id=$cid")->setInc('send_num',$able);
            adminLog("发放".$able.'张'.$coupon['name']);
            $this->success("发放成功");
            exit;
        }
        $level = M('user_level')->select();
        $this -> assign('level',$level);
        $this -> assign('cid',$cid);
        $this -> display();
    }

    public function send_cancel(){

    }

    /*
     * 删除优惠券类型
     */
    public function del_coupon(){
        //获取优惠券ID
        $cid = I('get.id');
        //查询是否存在优惠券
        $row = M('coupon') -> where(array('id'=>$cid))->delete();
        if($row){
            //删除此类型下的优惠券
            M('coupon_list') -> where(array('cid'=>$cid))->delete();
            $this->success("删除成功");
        }else{
            $this->error("删除失败");
        }
    }

    public function coupon_list(){
        $cid = I('get.id');
        $check_coupon = M('gift_coupon')->field('id') -> where(array('id'=>$cid))->find();
        if(!$check_coupon['id'] > 0){
            $this->error('不存在该礼品券');
        }

        //查询该优惠券的列表的数量
        $sql = "SELECT count(1) as c FROM __PREFIX__coupon_code  l ".
            "LEFT JOIN __PREFIX__gift_coupon c ON c.id = l.gift_coupon_id ". //联合优惠券表查询名称
            "LEFT JOIN __PREFIX__order o ON o.order_id = l.g_code_order_id ".     //联合订单表查询订单编号
            "LEFT JOIN __PREFIX__users u ON u.user_id = l.user_id WHERE c.id = ".$cid;    //联合用户表去查询用户名

        $count = M()->query($sql);
        $count = $count[0]['c'];
        $Page = new \Think\Page($count,10);
        $show = $Page -> show();

        //查询该优惠券的列表
        $sql = "SELECT l.*,c.gift_coupon_name,o.order_sn,u.nickname FROM __PREFIX__coupon_code  l ".
            "LEFT JOIN __PREFIX__gift_coupon c ON c.id = l.gift_coupon_id ". //联合优惠券表查询名称
            "LEFT JOIN __PREFIX__order o ON o.order_id = l.g_code_order_id ".     //联合订单表查询订单编号
            "LEFT JOIN __PREFIX__users u ON u.user_id = l.user_id WHERE c.id = ".$cid .    //联合用户表去查询用户名
            " order by l.use_time desc ".
            " limit {$Page->firstRow} , {$Page->listRows}";
        $coupon_list = M()->query($sql);
        $this -> assign('coupon_type',C('COUPON_TYPE'));
        $this -> assign('type',$check_coupon['type']);
        $this -> assign('lists',$coupon_list);
        $this -> assign('page',$show);
        $this -> display();
    }

    /*
     * 优惠券详细查看
     */
    public function excl_out(){
        $cid = I('get.id');
        $check_coupon = M('gift_coupon')->field('id') -> where(array('id'=>$cid))->find();
        if(!$check_coupon['id'] > 0){
            $this->error('不存在该礼品券');
        }
        $data = M("coupon_code") -> where(array("gift_coupon_id"=>$cid))->select();
        if(!empty($data)){
            $status = array("0"=>"未领取","1"=>"已领取","2"=>"已使用");
            $strTable ='<table width="500" border="1">';
            $strTable .= '<tr>';
            $strTable .= '<td style="text-align:center;font-size:12px;width:120px;">兑换码</td>';
            $strTable .= '<td style="text-align:center;font-size:12px;" width="100">状态</td>';
            $strTable .= '</tr>';
            foreach ($data as $val){
                $strTable .= '<tr>';
                $strTable .= '<td >&nbsp;'.$val['code'].'</td>';
                $strTable .= '<td >'.$status[$val['state']].' </td>';
                $strTable .= '</tr>';
            }
            $strTable .= '</table>';
            downloadExcel($strTable,'giftCoupon');
            exit;
        }
        $this->error('没有数据');
        exit;
    }

    /*
     * 删除一张优惠券
     */
    public function coupon_list_del(){
        //获取优惠券ID
        $cid = I('get.id');
        if(!$cid)
            $this->error("缺少参数值");
        //查询是否存在优惠券
        $row = M('coupon_list') -> where(array('id'=>$cid))->delete();
        if(!$row)
            $this->error('删除失败');
        $this->success('删除成功');
    }
}