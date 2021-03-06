<?php
namespace Admin\Controller;
use Admin\Logic\OrderLogic;
use Think\AjaxPage;

class OrderController extends BaseController {
    public  $order_status;
    public  $shipping_status;
    public  $pay_status;
    /**
     * 初始化操作
     */
    public function _initialize() {
        parent::_initialize();
        C('TOKEN_ON',false); // 关闭表单令牌验证
        // 订单 支付 发货状态
        $this -> order_status = C('ORDER_STATUS');
        $this -> shipping_status = C('SHIPPING_STATUS');
        $this -> pay_status = C('PAY_STATUS');
        $this -> assign('order_status',C('ORDER_STATUS'));
        $this -> assign('pay_status',C('PAY_STATUS'));
        $this -> assign('shipping_status',C('SHIPPING_STATUS'));
    }

    /**
     * 订单首页
     */
    public function index(){
    	$thirtyDays= date('Y/m/d',(time()-30*60*60*24));//30天前
    	$end = date('Y/m/d',strtotime('+1 days'));
        $sevenDays = date('Y/m/d',(time()-7*60*60*24));//7天前
        $expressList = include_once 'Application/Common/Conf/express.php'; //快递名称
        $this -> assign('expressList',$expressList);
        $this -> assign('thirtyDays',$thirtyDays);
        $this -> assign('end',$end);
        $this -> assign('sevenDays',$sevenDays);
        $this -> display();
    }

    /**
     * Ajax订单首页
     */
    public function ajaxIndex(){

        $orderLogic = new OrderLogic();

        //搜索条件
        $begin          = strtotime(I('begin'));
        $end            = strtotime(I('end'));
        $search_type    = I('search_type');
        $search_name    = I('search_name');
        $type           = I('type');

        $condition = array();
        I('pay_status')      != ''   ? $condition['pay_status']      = I('pay_status')                              : false;
        I('pay_code')        != ''   ? $condition['pay_code']        = I('pay_code')                                : false;
        I('shipping_status') != ''   ? $condition['shipping_status'] = I('shipping_status')                         : false;
        $begin && $end               ? $condition['add_time']        = array('between',"$begin,$end")               : false;
        is_supplier()                ? $condition['admin_list']      = array('like',"%[".session("admin_id")."]%")  : false;//供应商判断
        $type == "notPayment"        ? $condition["_string"]         = " 1=1 " . C('WAITPAY')                       : false;//订单查询状态 待支付
        $type == "nonDeliverGoods"   ? $condition["_string"]         = " 1=1 " . C('WAITSEND')                      : false;//订单查询状态 待发货
        $type == "delivered"         ? $condition["_string"]         = " 1=1 " . C('WAITRECEIVE')                   : false;//订单查询状态 待收货
        $type == "Completed"         ? $condition["_string"]         = " 1=1 " . C('FINISHED')                      : false;//订单查询状态 已完成
        $type == "close"             ? $condition["_string"]         = " 1=1 " . C('CANCEL')                        : false;//订单查询状态 已取消
        $search_type == 1 && !empty($search_name)            ? $condition['order_sn']        = $search_name                                 : false ;
        $search_type == 2 && !empty($search_name)            ? $condition['consignee']       = $search_name                                 : false ;
        $search_type == 3 && !empty($search_name)            ? $condition['mobile']          = $search_name                                 : false ;
        $search_type == 4 && !empty($search_name)            ? $condition['user_id']         = findUserId($search_name)                     : false ;
        //排序
        $sort_order = I('order_by','add_time').' '.I('sort','DESC');

        $limit = 10;
        $count = getCountWithCondition( 'order' , $condition );
        $Page  = new \Admin\Common\AjaxPage( $count , $limit );
        foreach($condition as $key => $val) {//搜索条件下 分页赋值
            $Page->parameter[$key]   =  urlencode($val);
        }
        $show = $Page -> show();

        //获取订单列表
        $orderList = $orderLogic -> getOrderList( $condition , $sort_order , $Page->firstRow , $Page->listRows );
        $orderList = $orderLogic -> getOrderListInfo( $orderList );

        $this -> assign('page',$show);
        $this -> assign('orderList',$orderList);
        $this -> display();
    }

    /**
     * 发货单列表
     */
    public function delivery_list(){
        $this -> display();
    }

    /**
    * ajax 发货订单列表
    */
    public function ajaxDelivery(){
    	$orderLogic         = new OrderLogic();
        $type               = I('type');
        $returnGoodsField   = $orderLogic -> getReturnGoodsField( false );

    	$condition = array();
    	$condition['order_status']      = array( 'eq' , 1 );
        $condition['pay_status']        = 1;

        is_supplier()               ? $condition['admin_list']      = array('like',"%[".session("admin_id")."]%")   : false ;//供应商判断
        $type == 'unfilledOrders'   ? $condition["shipping_status"] = "0"                                           : false ;//待发货
        $type == 'delivered'        ? $condition["shipping_status"] = "1"                                           : false ;//已发货
        $type == 'partDelivered'    ? $condition["shipping_status"] = "2"                                           : false ;//部分发货
        !empty( $returnGoodsField ) ? $condition["order_id"]        = array('not in', implode($returnGoodsField))   : false ;//排除售后订单

        //排序
        $sort_order = 'add_time DESC';

        $limit = 10;
        $count = getCountWithCondition( 'order' , $condition );
        $Page  = new \Admin\Common\AjaxPage( $count , $limit );
    	foreach( $condition as $key => $val ) {//搜索条件下 分页赋值
    		$Page -> parameter[$key]   =   urlencode($val);
    	}
        $show = $Page -> show();


        //获取订单列表
        $orderList = $orderLogic -> getOrderList( $condition , $sort_order , $Page->firstRow , $Page->listRows );
        $orderList = $orderLogic -> getOrderListShippingPrice( $orderList , true ) ;

        $this -> assign('page',$show);
        $this -> assign('orderList',$orderList);
    	$this -> display();
    }
    
    /**
     * 订单详情
     */
    public function detail(){
        $order_id   = I("order_id");
        $orderLogic = new OrderLogic();
        $order = $orderLogic -> getOrderInfo($order_id);

        if( empty( $order )){
            $this -> error('没有此订单');
            exit;
        }
        $couponInfo = findDataWithCondition("coupon_list",array('order_id'=>$order_id));


        $button     = $orderLogic -> getOrderButton( $order );
        $sql = "SELECT g.*,o.*,(o.goods_num * o.member_goods_price) AS goods_total FROM __PREFIX__order_goods o ".
            "LEFT JOIN __PREFIX__goods g ON o.goods_id = g.goods_id  WHERE o.order_id = $order_id ORDER BY o.delivery_id ASC";
        $orderGoods = M()->query($sql);
        $isexist = array();
        foreach($orderGoods as $key=>$item){
            if( empty($isexist[$item['delivery_id']] ) ){
                $isexist[$item['delivery_id']] = $item['delivery_id'];
                $orderGoods[$key]['logistics'] = $item['delivery_id'];
                $shipping_name = M('delivery_doc')->field('shipping_name,invoice_no')->where(array('id'=>$item['delivery_id']))->find();
                $orderGoods[$key]['shipping_name'] = $shipping_name['shipping_name'];
                $orderGoods[$key]['invoice_no'] = $shipping_name['invoice_no'];
            }
            if($item['is_send'] == 0){
                $existSat = '未发货';
            }elseif ($item['is_send'] == 1){
                $existSat = '已发货';
            }elseif ($item['is_send'] == 2){
                $existSat = '已换货';
            }elseif ($item['is_send'] == 3){
                $existSat = '已退货';
            }
            $orderGoods[$key]['existSat']  = $existSat ;
        }
        // 获取操作记录
        $action_log = M('order_action') -> where(array('order_id'=>$order_id))->order('log_time desc')->select();
        $this -> assign('order',$order);
        $this -> assign('couponInfo',$couponInfo);
        $this -> assign('action_log',$action_log);
        $this -> assign('orderGoods',$orderGoods);
        $split = count($orderGoods) >1 ? 1 : 0;
        foreach ($orderGoods as $val){
        	if($val['goods_num']>1){
        		$split = 1;
        	}
        }
        $this -> assign('split',$split);
        $this -> assign('button',$button);
        $this -> display();
    }

    /**
     * 物流信息
     */
    public function express(){
        $orderId = I('get.order_id','','int'); //订单id
        $id = I('get.id','','int'); //物流表id
        !empty($id) ? $where['id'] = $id : $where['order_id'] =  $orderId;
        $delivery = M('delivery_doc') -> where($where)->limit(1)->find();
        if(empty($delivery)){
            $this -> assign('expressMessage', '查询物流失败' );
        }
        $result = kuaidi($delivery['invoice_no'],$delivery['shipping_name']);

        if( $result == false ){
            $dataList = array(
                '安能小包' => 'http://www.ane66.com/',
                '安能快递' => 'http://www.ane56.com/',
                'aae全球专递' => 'http://cn.aaeweb.com/',
                '安捷快递' => 'http://www.anjelex.com/',
                '凤凰快递' => 'phoenixexp.com',
                '民航快递' => 'http://www.cae.com.cn/',
                '配思货运' => 'http://www.peisiwuliu.com/',
                '文捷航空速递' => 'http://www.wjexpress.com/',
                '伍圆' => 'http://www.f5xm.com/',
                '中铁快运' => 'http://www.cre.cn/',
            );
            $notFind = true;
            foreach($dataList as $key=>$item){
                if($delivery['shipping_name'] == $key){
                    $queryUrl = $item;
                    $notFind = false;
                }
            }
            $this -> assign('invoice_no', $delivery['invoice_no']);
            $this -> assign('shipping_name', $delivery['shipping_name']);
            $this -> assign('queryUrl',$queryUrl);
            $this -> assign('notFind',$notFind);
            $this -> assign('isNoFindApi',true);
            $this -> display();
            exit;
        }

        if( $result['status'] == 200  ){
//            dd($result['data']);
            //支付时间
            $pay_time = M('order')->field('pay_time') -> where($where)->find();
            $this -> assign('pay_time',$pay_time['pay_time']);
            $this -> assign('expressData', $result);
            $this -> assign('odd_numbers',$delivery['invoice_no']);
        }else{
            $this -> assign('expressMessage', $result['message'] );
        }
        $this -> display();
    }


    /**
     * 快速发货 手机端页面
     */
    public function fastIndex(){
        $expressList = include_once 'Application/Common/Conf/express.php'; //快递名称
        $this -> assign('expressList',$expressList);
        $this->display();
    }
    public function ajaxFastIndex(){
        $orderLogic = new OrderLogic();
        is_supplier()  ? $condition['admin_list']   = array('like',"%[".session("admin_id")."]%")  : false;//供应商判断
        $condition["_string"]   = " 1=1 " . C('WAITSEND');
        $limit = 20;
        $count = getCountWithCondition( 'order' , $condition );
        $Page  = new \Admin\Common\AjaxPage( $count , $limit );

        $sort_order = "add_time DESC";
        //获取订单列表
        $orderList = $orderLogic -> getOrderList( $condition , $sort_order , $Page->firstRow , $Page->listRows );
        $orderList = $orderLogic -> getOrderListInfo( $orderList );


        $region_list = get_region_list();
        //收货人
        foreach($orderList as $key=>$item){
            $orderList[$key]['site'] = $region_list[$item['province']]['name'].' '.$region_list[$item['city']]['name'].' '.$region_list[$item['district']]['name'].' '.$item['address'].', '.$item['consignee'].' '.$item['mobile'];
        }
        $this -> assign('number',I('p'));
        $this->assign("orderList",$orderList);
        $this -> assign('count',$count);
        $this -> assign('limit',$limit * I('p'));
        $this->display();
    }
    /**
     * 快速发货
     */
    public function fastShipping(){
        $id =  I("id");
        $invoice_no =  I("invoice_no");
        $shipping_name =  I("shipping_name");
        $list = M("order_goods") -> where(array('order_id' => $id )) -> field("rec_id") -> select();
        $goods = array();
        if(!empty($list)){
            foreach ( $list as $item ){
                $goods[] = $item['rec_id'];
            }
        }
        $data = array(
            "order_id" => $id,
            "invoice_no" => $invoice_no,
            "myShippingName" => $shipping_name,
            "goods"=>$goods,
            "note"=>" ",
        );
        $orderLogic = new OrderLogic();
        $result = $orderLogic->deliveryHandle($data);
        exit(json_encode($result));
    }

    /**
     * 生成发货单
     */
    public function deliveryHandle(){
        $orderLogic = new OrderLogic();
		$data = I('post.');
        if( !empty( $data['shipping_name'] ) ){
            $data["myShippingName"] = $data['shipping_name'] ;
            unset($data['shipping_name']);
        }
		$result = $orderLogic->deliveryHandle($data);
		if( callbackIsTrue($result) ){
			$this->success('操作成功',U('Admin/Order/delivery_info',array('order_id'=>$data['order_id'])));
		}else{
			$this->success(getCallbackMessage($result),U('Admin/Order/delivery_info',array('order_id'=>$data['order_id'])));
		}
    }

    /**
     * 发货单详情
     */
    public function delivery_info(){
    	$order_id = I('order_id');
    	$orderLogic = new OrderLogic();
    	$order      = $orderLogic -> getOrderInfo($order_id);
        if( empty( $order )){
            $this -> error('没有此订单');
            exit;
        }
        $order['goods_list'] = $orderGoods = $orderLogic -> getOrderGoods($order_id);
        $order = setOrderReturnState( $order , $order['user_id'] );

        $condition = array();
        $condition['order_id'] = $order_id;
        if(is_supplier()){
            $condition['admin_id'] = session('admin_id');
        }
		$deliveryRecord = selectDataWithCondition( 'delivery_doc' , $condition );

        $expressList = include_once 'Application/Common/Conf/express.php'; //快递名称

        $this -> assign('order',$order);
        $this -> assign('orderGoods',$orderGoods);
        $this -> assign('expressList',$expressList);
        $this -> assign('delivery_record',$deliveryRecord);//发货记录
    	$this -> display();
    }

    /**
     * 退货单列表
     */
    public function return_list(){
        $order_sn = trim(I('order_sn'));
        if(!empty($order_sn)){
            $this -> assign('order_sn',$order_sn);
        }
        $this -> display();
    }

    /**
     * ajax 退货订单列表
     */
    public function ajax_return_list(){

        //搜索条件
        $type   = I('type');
        $where  = array();
        $where['admin_id']    = is_supplier() ? session("admin_id")  : "0" ;
        $type == 'untreated' ? $where['result'] = "0" : false;
        $type == 'processed' ? $where['result'] = "1" : false;
        $type == 'decline'   ? $where['result'] = "2" : false;

        //排序
        $sort_order = I( 'order_by' , 'id' ) . ' ' . I( 'sort_order' , 'desc' );

        $limit = 10;
        $count = getCountWithCondition( 'return_goods' , $where );
        $Page  = new \Admin\Common\AjaxPage( $count , $limit );
        $show = $Page -> show();

        $list = M('return_goods') -> where($where)->order("$sort_order")->limit("{$Page->firstRow},{$Page->listRows}")->select();
        $goods_id_arr = get_arr_column($list, 'goods_id');
        if(!empty($goods_id_arr)){
            $goods_list = M('goods') -> where("goods_id in (".implode(',', $goods_id_arr).")")->getField('goods_id,goods_name');
        }

        $this -> assign('list',$list);
        $this -> assign('page',$show);// 赋值分页输出
        $this -> assign('goods_list',$goods_list);
        $this -> display();
    }
    
    /**
     * 删除某个退换货申请
     */
    public function return_del(){
        $id = I('get.id');
        M('return_goods') -> where("id = $id")->delete();
        $this -> success('成功删除!');
    }
    /**
     * 退换货操作
     */
    public function return_info()
    {
        $id = I('id');
        $return_goods = findDataWithCondition('return_goods',array('id' => $id));
        if(IS_POST)
        {
            $data['refund_money'] = trim(I('money'));
            $data['refundMoneyType'] = trim(I('refundMoneyType'));
            $data['remark'] = I('remark');

            $result = returnOrderHandle( $return_goods , $data );
            exit( json_encode( $result ) );
        }
        if( empty($return_goods) ){
            $this->error('访问错误!');
        }


        $user = M('users') -> where("user_id = {$return_goods['user_id']}")->find();
        $goods = M('goods') -> where("goods_id = {$return_goods['goods_id']}")->find();
        $orderGoods = M('order_goods') -> where(array('order_id'=>$return_goods['order_id'],'goods_id'=>$return_goods['goods_id']))->find();

        $return_goods['goods_postage'] =$orderGoods['goods_postage'];//运费
        $return_goods['GoodsMoney'] = $orderGoods['member_goods_price'] * $orderGoods['goods_num'];
        $this -> assign('id',$id); // 用户
        $this -> assign('user',$user); // 用户
        $this -> assign('goods',$goods);// 商品
        $this -> assign('return_goods',$return_goods);// 退换货
        $this -> display();
    }
    
    /**
     * 管理员生成申请退货单
     */
    public function add_return_goods()
   {                
            $order_id = I('order_id'); 
            $goods_id = I('goods_id');
                
            $return_goods = M('return_goods') -> where("order_id = $order_id and goods_id = $goods_id")->find();
            if(!empty($return_goods))
            {
                $this->error('已经提交过退货申请!',U('Admin/Order/return_list'));
                exit;
            }
            $order = M('order') -> where("order_id = $order_id")->find();
            
            $data['order_id'] = $order_id; 
            $data['order_sn'] = $order['order_sn']; 
            $data['goods_id'] = $goods_id; 
            $data['addtime'] = time(); 
            $data['user_id'] = $order[user_id];            
            $data['remark'] = '管理员申请退换货'; // 问题描述            
            M('return_goods')->add($data);            
            $this->success('申请成功,现在去处理退货',U('Admin/Order/return_list'));
            exit;
    }



    /**
     * 价钱修改
     */
    public function editprice($order_id){
        exit;
        $orderLogic = new OrderLogic();
        $order = $orderLogic->getOrderInfo($order_id);
        $this->editable($order);
        if(IS_POST){
            $admin_id = session('admin_id');
            if(empty($admin_id)){
                $this->error('非法操作');
                exit;
            }
            $update['discount'] = I('post.discount');
            $update['shipping_price'] = I('post.shipping_price');
            $update['order_amount'] = $order['goods_price'] + $update['shipping_price'] - $update['discount'] - $order['user_money'] - $order['integral_money'] - $order['coupon_price'];
            $row = M('order') -> where(array('order_id'=>$order_id))->save($update);
            if(!$row){
                $this->success('没有更新数据',U('Admin/Order/editprice',array('order_id'=>$order_id)));
            }else{
                $this->success('操作成功',U('Admin/Order/detail',array('order_id'=>$order_id)));
            }
            exit;
        }
        $this -> assign('order',$order);
        $this -> display();
    }

    /**
     * 订单删除
     * @param int $id 订单id
     */
    public function delete_order($order_id){
        $orderLogic = new OrderLogic();
        $del = $orderLogic->delOrder($order_id);
        if($del){
            $this->success('删除订单成功');
        }else{
            $this->error('订单删除失败');
        }
    }

    /**
     * 订单取消付款
     */
    public function pay_cancel($order_id){
        if(I('remark')){
            $data = I('post.');
            $note = array('退款到用户余额','已通过其他方式退款','不处理，误操作项');
            if($data['refundType'] == 0 && $data['amount']>0){
                accountLog($data['user_id'], $data['amount'], 0,  '退款到用户余额');
            }
            $orderLogic = new OrderLogic();
            $orderLogic->orderProcessHandle($data['order_id'],'pay_cancel');
            $d = $orderLogic->orderActionLog($data['order_id'],'pay_cancel',$data['remark'].':'.$note[$data['refundType']]);
            if($d){
                exit("<script>window.parent.pay_callback(1);</script>");
            }else{
                exit("<script>window.parent.pay_callback(0);</script>");
            }
        }else{
            $order = M('order') -> where("order_id=$order_id")->find();
            $this -> assign('order',$order);
            $this -> display();
        }
    }

    /**
     * 订单打印
     * @param int $id 订单id
     */
    public function order_print($order_id){
        exit;
        $orderLogic = new OrderLogic();
        $order = $orderLogic->getOrderInfo($order_id);
        $order['province'] = getRegionName($order['province']);
        $order['city'] = getRegionName($order['city']);
        $order['district'] = getRegionName($order['district']);
        $order['full_address'] = $order['province'].' '.$order['city'].' '.$order['district'].' '. $order['address'];
        $orderGoods = $orderLogic->getOrderGoods($order_id);
        $shop = tpCache('shop_info');
        $this -> assign('order',$order);
        $this -> assign('shop',$shop);
        $this -> assign('orderGoods',$orderGoods);
        $this -> display('print');
    }

    /**
     * 快递单打印
     */
    public function shipping_print(){
        $code = I('get.code');
        $id = I('get.order_id');
        //查询是否存在订单及物流
        $shipping = M('plugin') -> where(array('code'=>$code,'type'=>'shipping'))->find();
        if(!$shipping)
            $this->error('物流插件不存在',U('Admin/Index/index'));
        $orderLogic = new OrderLogic();
        $order = $orderLogic->getOrderInfo($id);
        if(!$order)
            $this->error('订单不存在');
        //检查模板是否存在
        if(!file_exists(APP_PATH."Admin/View/Plugin/shipping/{$code}_print.html"))
            $this->error('请先在插件中心设置打印模板',U('Admin/Index/index'));
        //获取商店信息
        $shop = tpCache('shop_info');
        $order['province'] = getRegionName($order['province']);
        $order['city'] = getRegionName($order['city']);
        $order['district'] = getRegionName($order['district']);
        $order['full_address'] = $order['province'].' '.$order['city'].' '.$order['district'].' '. $order['address'];
        $this -> assign('shop',$shop);
        $this -> assign('order',$order);
        $this -> display("Plugin/shipping/{$code}_print");
    }

    /**
     * 订单操作
     * @param $id
     */
    public function order_action(){    	
        $orderLogic = new OrderLogic();
        $action = I('get.type');
        $order_id = I('get.order_id');
        if($action && $order_id){
        	 $a = $orderLogic->orderProcessHandle($order_id,$action);       	
        	 $res = $orderLogic->orderActionLog($order_id,$action,I('note'));
        	 if($res && $a){
        	 	exit(json_encode(array('status' => 1,'msg' => '操作成功')));
        	 }else{
        	 	exit(json_encode(array('status' => 0,'msg' => '操作失败')));
        	 }
        }else{
        	$this->error('参数错误',U('Admin/Order/detail',array('order_id'=>$order_id)));
        }
    }
    
    public function order_log(){
    	$timegap = I('timegap');
    	if($timegap){
    		$gap = explode('-', $timegap);
    		$begin = strtotime($gap[0]);
    		$end = strtotime($gap[1]);
    	}
    	$condition = array();
    	$log =  M('order_action');
    	if($begin && $end){
    		$condition['log_time'] = array('between',"$begin,$end");
    	}
    	$admin_id = I('admin_id');
		if($admin_id >0 ){
			$condition['action_user'] = $admin_id;
		}
    	$count = $log->where($condition)->count();
    	$Page = new \Think\Page($count,20);
    	foreach($condition as $key=>$val) {
    		$Page->parameter[$key] = urlencode($val);
    	}
    	$show = $Page -> show();
    	$list = $log->where($condition)->order('action_id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
    	$this -> assign('list',$list);
    	$this -> assign('page',$show);
    	$admin = M('admin')->getField('admin_id,user_name');
    	$this -> assign('admin',$admin);
    	$this -> display();
    }

    /**
     * 检测订单是否可以编辑
     * @param $order
     */
    private function editable($order){
        if($order['shipping_status'] != 0){
            $this->error('已发货订单不允许编辑');
            exit;
        }
        return;
    }

    public function export_order()
    {
    	//搜索条件
		$where = 'where 1=1 ';
		$consignee = I('consignee');
		if($consignee){
			$where .= " AND consignee like '%$consignee%' ";
		}
		$order_sn =  I('order_sn');
		if($order_sn){
			$where .= " AND order_sn = '$order_sn' ";
		}
        if(I('pay_status')!=""){
            $where .= " AND pay_status = ".I('pay_status');
        }

        $search_type = I('search_type');//条件
        $search_name = I('search_name');//值
        !empty($search_name) ? $where .= " AND ".$search_type." = ".$search_name."" : '';

        //订单状态
        if(I('order_status')!=""){

            switch (I('order_status')) {
                case 'notPayment': //订单查询状态 待支付
                    $where .=  C('WAITPAY');
                    break;
                case 'nonDeliverGoods': //订单查询状态 待发货
                    $where .= C('WAITSEND');
                    break;
                case 'delivered': //订单查询状态 待收货
                    $where .= C('WAITRECEIVE');
                    break;
                case 'Completed': // 已完成
                    $where .= C('FINISHED');
                    break;
                case 'close':  // 已取消
                    $where .= C('CANCEL');
                    break;
            }
        }
        if(I('shipping_status')!=""){
            $where .= " AND shipping_status = ".I('shipping_status');
        }
        if(I('pay_code')!=""){
            $where .= " AND pay_code = ".I('pay_code');
        }

        $begin = strtotime(I('begin'));
        $end = strtotime(I('end'));
		if($begin && $end){
			$where .= " AND add_time>'$begin' and add_time<'$end' ";
		}

	$region	= M('region')->getField('id,name');
                
	$sql = "select *,FROM_UNIXTIME(add_time,'%Y-%m-%d') as create_time from __PREFIX__order $where order by order_id";
    	$orderList = D()->query($sql);   
    	$strTable ='<table width="500" border="1">';
    	$strTable .= '<tr>';
    	$strTable .= '<td style="text-align:center;font-size:12px;width:120px;">订单编号</td>';
        $strTable .= '<td style="text-align:center;font-size:12px;" width="100">日期</td>';
        $strTable .= '<td style="text-align:center;font-size:12px;" width="*">下单时间</td>';
    	$strTable .= '<td style="text-align:center;font-size:12px;" width="*">收货人</td>';
    	$strTable .= '<td style="text-align:center;font-size:12px;" width="*">收货地址</td>';
    	$strTable .= '<td style="text-align:center;font-size:12px;" width="*">手机</td>';
    	$strTable .= '<td style="text-align:center;font-size:12px;" width="*">订单金额</td>';
    	$strTable .= '<td style="text-align:center;font-size:12px;" width="*">实际支付</td>';
    	$strTable .= '<td style="text-align:center;font-size:12px;" width="*">支付方式</td>';
    	$strTable .= '<td style="text-align:center;font-size:12px;" width="*">支付状态</td>';
        $strTable .= '<td style="text-align:center;font-size:12px;" width="*">发货状态</td>';
        $strTable .= '<td style="text-align:center;font-size:12px;" width="*">物流信息</td>';
        $strTable .= '<td style="text-align:center;font-size:12px;" width="*">商品信息</td>';
        $strTable .= '<td style="text-align:center;font-size:12px;" width="*">商品数量</td>';
        $strTable .= '<td style="text-align:center;font-size:12px;" width="*">用户备注</td>';
    	$strTable .= '</tr>';
    	foreach($orderList as $k=>$val){
            $orderGoods = D('order_goods') -> where('order_id='.$val['order_id'])->select();
            $lineNumber = count($orderGoods);

            $tempString = "";
            $tempString .= '<tr>';
            $tempString .= '<td style="text-align:center;font-size:12px;" rowspan="'.$lineNumber.'">&nbsp;'.$val['order_sn'].'</td>';
            $tempString .= '<td style="text-align:left;font-size:12px;" rowspan="'.$lineNumber.'">'.date("Y-m-d",$val['add_time']).' </td>';
            $tempString .= '<td style="text-align:left;font-size:12px;" rowspan="'.$lineNumber.'">'.date("H:i:d",$val['add_time']).' </td>';
            $tempString .= '<td style="text-align:left;font-size:12px;" rowspan="'.$lineNumber.'">'."{$val['consignee']}".' </td>';
            $tempString .= '<td style="text-align:left;font-size:12px;" rowspan="'.$lineNumber.'">'."{$region[$val['province']]},{$region[$val['city']]},{$region[$val['district']]},{$region[$val['twon']]}".$val['address'].'</td>';
            $tempString .= '<td style="text-align:left;font-size:12px;" rowspan="'.$lineNumber.'">'.$val['mobile'].'</td>';
            $tempString .= '<td style="text-align:left;font-size:12px;" rowspan="'.$lineNumber.'">'.$val['goods_price'].'</td>';
            $tempString .= '<td style="text-align:left;font-size:12px;" rowspan="'.$lineNumber.'">'.$val['order_amount'].'</td>';
            $tempString .= '<td style="text-align:left;font-size:12px;" rowspan="'.$lineNumber.'">'.$val['pay_name'].'</td>';
            $tempString .= '<td style="text-align:left;font-size:12px;" rowspan="'.$lineNumber.'">'.$this->pay_status[$val['pay_status']].'</td>';
            $tempString .= '<td style="text-align:left;font-size:12px;" rowspan="'.$lineNumber.'">'.$this->shipping_status[$val['shipping_status']].'</td>';

            $strGoods=array();
            $shippingArray=array();
            foreach($orderGoods as $key =>  $goods){
                $returnRes = M('return_goods') -> where(array('order_id'=>$goods['order_id'],'goods_id'=>$goods['goods_id'],'spec_key'=>$goods['spec_key']))->find();
                if(!empty($returnRes) && $returnRes['result'] == 0){ //有未处理订单 不能导出该订单
                    continue 2;
                }
                if( $returnRes['result'] == 1 ){ //同意退款 跳出该商品
                    continue;
                }
                $strGoods[$key]['string']= "商品编号：".$goods['goods_sn']." 商品名称：".$goods['goods_name']." ";
                if ($goods['spec_key_name'] != '') $strGoods[$key]['string'] .= " 规格：".$goods['spec_key_name'];
                $strGoods[$key]['number']=$goods['goods_num'];
                $shipping_name = M('delivery_doc')->field('shipping_name,invoice_no')->where(array('id'=>$goods['delivery_id']))->find();
                if( !empty($shipping_name) && !empty($shipping_name['shipping_name']) && !empty($shipping_name['invoice_no']) ){
                    $shippingArray[$key]['shipping_name'] = $shipping_name['shipping_name'];
                    $shippingArray[$key]['invoice_no'] = $shipping_name['invoice_no'];
                }
            }
            unset($orderGoods);
            $tempString .= '<td style="text-align:left;font-size:12px;" rowspan="'.$lineNumber.'">';
            if(!empty($shippingArray)){
                foreach ($shippingArray as $shippingItem){
                    $tempString .= $shippingItem['shipping_name'] .":[".$shippingItem['invoice_no']."]<br>";
                }
            }
            $tempString .= '</td>';
            $tempString .= '<td style="text-align:left;font-size:12px;">'.$strGoods[0]['string'].' </td>';
            $tempString .= '<td style="text-align:left;font-size:12px;"><b style="color:#f00;">'.$strGoods[0]['number'].'</b> </td>';
            $tempString .= '<td style="text-align:left;font-size:12px;" rowspan="'.$lineNumber.'">'.$val['user_note'].'</td>';
            $tempString .= '</tr>';

            if( $lineNumber > 1){
                for( $myI=1;$myI < $lineNumber;$myI++ ){
                    $tempString .= '<tr>';
                    $tempString .= '<td style="text-align:left;font-size:12px;">'.$strGoods[$myI]['string'].' </td>';
                    $tempString .= '<td style="text-align:left;font-size:12px;"><b style="color:#f00;">'.$strGoods[$myI]['number'].'</b> </td>';
                    $tempString .= '</tr>';
                }
            }

            $strTable .= $tempString;
    	}
    	$strTable .='</table>';
    	unset($orderList);
    	downloadExcel($strTable,'order');
    	exit();
    }
    
    /**
     * 添加一笔订单
     */
    public function add_order()
    {
        $order = array();
        //  获取省份
        $province = M('region') -> where(array('parent_id'=>0,'level'=>1))->select();
        //  获取订单城市
        $city =  M('region') -> where(array('parent_id'=>$order['province'],'level'=>2))->select();
        //  获取订单地区
        $area =  M('region') -> where(array('parent_id'=>$order['city'],'level'=>3))->select();
        //  获取配送方式
        $shipping_list = M('plugin') -> where(array('status'=>1,'type'=>'shipping'))->select();
        //  获取支付方式
        $payment_list = M('plugin') -> where(array('status'=>1,'type'=>'payment'))->select();
        if(IS_POST)
        {
            $order['user_id'] = I('user_id');// 用户id 可以为空
            $order['consignee'] = I('consignee');// 收货人
            $order['province'] = I('province'); // 省份
            $order['city'] = I('city'); // 城市
            $order['district'] = I('district'); // 县
            $order['address'] = I('address'); // 收货地址
            $order['mobile'] = I('mobile'); // 手机           
            $order['invoice_title'] = I('invoice_title');// 发票
            $order['admin_note'] = I('admin_note'); // 管理员备注            
            $order['order_sn'] = date('YmdHis').mt_rand(1000,9999); // 订单编号;
            $order['admin_note'] = I('admin_note'); // 
            $order['add_time'] = time(); //                    
            $order['shipping_code'] = I('shipping');// 物流方式
            $order['shipping_name'] = M('plugin') -> where(array('status'=>1,'type'=>'shipping','code'=>I('shipping')))->getField('name');
            $order['pay_code'] = I('payment');// 支付方式            
            $order['pay_name'] = M('plugin') -> where(array('status'=>1,'type'=>'payment','code'=>I('payment')))->getField('name');
                            
            $goods_id_arr = I("goods_id");
            $orderLogic = new OrderLogic();
            $order_goods = $orderLogic->get_spec_goods($goods_id_arr);          
            $result = calculate_price($order['user_id'],$order_goods,$order['shipping_code'],0,$order[province],$order[city],$order[district],0,0,0,0);      
            if($result['status'] < 0)	
            {
                 $this->error($result['msg']);      
            } 
           
           $order['goods_price']    = $result['result']['goods_price']; // 商品总价
           $order['shipping_price'] = $result['result']['shipping_price']; //物流费
           $order['order_amount']   = $result['result']['order_amount']; // 应付金额
           $order['total_amount']   = $result['result']['total_amount']; // 订单总价
           
            // 添加订单
            $order_id = M('order')->add($order);
            if($order_id)
            {
                foreach($order_goods as $key => $val)
                {
                    $val['order_id'] = $order_id;
                    $rec_id = M('order_goods')->add($val);
                    if(!$rec_id)                 
                        $this->error('添加失败');                                  
                }
                $this->success('添加商品成功',U("Admin/Order/detail",array('order_id'=>$order_id)));
                exit();
            }
            else{
                $this->error('添加失败');
            }                
        }     
        $this -> assign('shipping_list',$shipping_list);
        $this -> assign('payment_list',$payment_list);
        $this -> assign('province',$province);
        $this -> assign('city',$city);
        $this -> assign('area',$area);
        $this -> display();
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
    	$this -> assign('goodsList',$goodsList);
    	$this -> display();
    }
    
    public function ajaxOrderNotice(){
        $order_amount = M('order') -> where("order_status=0 and (pay_status=1 or pay_code='cod')")->count();
        echo $order_amount;
    }

    /**
     * ajax请求发货商品
     *
     **/
    public function ajaxShippingList(){
        if(IS_POST){
            $orderId = I('id');
            $region_list = get_region_list();
            $order = M('order') -> where(array('order_id'=>$orderId))->find();
            //收货人
            $site = $region_list[$order['province']]['name'].' '.$region_list[$order['city']]['name'].' '.$region_list[$order['district']]['name'].' '.$order['address'].', '.$order['consignee'].' '.$order['mobile'];
            $where['order_id'] = $orderId;
            $where['admin_id'] =  is_supplier() ? session('admin_id') : '0';
            $goodsList = M('order_goods') -> where($where)->select();
            if(empty($goodsList)){
                exit(json_encode(callback(true,'没有订单数据')));
            }
            $html = '';
            foreach($goodsList as $item){
                //检测是否退货
                $returnRes = M('return_goods') -> where(array('order_id'=>$item['order_id'],'goods_id'=>$item['goods_id']))->find();
                if(!empty($returnRes) && $returnRes['result'] == 0){ //申请退货 未处理
                    continue;
                }
                if(!empty($returnRes) && $returnRes['result'] == 1 ){ //申请退货 已同意
                    continue;
                }
                $invoice_no = '';
                if( $item['is_send'] == 1 && !empty($item['delivery_id']) ){
                    $invoice_no = M('delivery_doc') -> where(array('id'=>$item['delivery_id']))->find();
                    $invoice_no = $invoice_no['invoice_no'];
                }
                switch ($item['is_send']){
                    case 0:
                        $send = '未发货';
                        break;
                    case 1:
                        $send = '已发货';
                        break;
                    case 2:
                        $send = '已换货';
                        break;
                    case 3:
                        $send = '已退货';
                        break;

                }
                $html .= '<tr><td><div class="sp"><div class="sp_l">';
                if($item['is_send'] == 0 && empty($item['delivery_id'])){
                    $html .= '<input type="checkbox" name="selected[]" value="'.$item['rec_id'].'">';
                }
                $html .= '<label for="checkbox"></label></div> <div class="sp_r"><a href="#">'.$item['goods_name'].'</a><p class="p1">'.$item['spec_key_name'].'</p></div></div></td><td>'.$item['goods_num'].'</td><td>'.$invoice_no.'</td><td>'.$send.'</td></tr>';
            }
            exit(json_encode(callback(true,'获取成功',array('html'=>$html,'site'=>$site,'noShipments'=>count($goodsList)))));

        }
    }

    /**
     * ajax发货商品
     *
     **/
    public function ajaxSend()
    {
        if (IS_POST) {
            $data = I('post.');
            $data = array(
                "order_id"       => $data['id'],
                "invoice_no"     => $data['invoice_no'],
                "myShippingName" => $data['shipping_name'],
                "goods"          => $data['rec_id_list'],
                "note"           => " ",
            );
            $orderLogic = new OrderLogic();
            $result = $orderLogic->deliveryHandle($data);
            exit(json_encode($result));
        }
    }


    /**
     * 批量发货
     */
    public function batchDelivery(){
        if( IS_POST ){
            $saveName = session("admin_id") . "_42368";
            $uploadConfig = array(
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
            delFile('.'.$info['create']['urlpath']);
            if(empty($resDate)){
                exit(json_encode(callback(false,'批量发货失败')));
            }
            exit( json_encode( batchDelivery( $resDate ) ) );
        }
        $this -> display();
    }


    /**
     * 订单编辑
     * @param int $id 订单id
     */
    public function edit_order(){
        exit;
        $order_id = I('order_id');
        $orderLogic = new OrderLogic();
        $order = $orderLogic->getOrderInfo($order_id);
        if($order['shipping_status'] != 0){
            $this->error('已发货订单不允许编辑');
            exit;
        }

        $orderGoods = $orderLogic->getOrderGoods($order_id);

        if(IS_POST)
        {
            $order['consignee'] = I('consignee');// 收货人
            $order['province'] = I('province'); // 省份
            $order['city'] = I('city'); // 城市
            $order['district'] = I('district'); // 县
            $order['address'] = I('address'); // 收货地址
            $order['mobile'] = I('mobile'); // 手机
            $order['invoice_title'] = I('invoice_title');// 发票
            $order['admin_note'] = I('admin_note'); // 管理员备注
            $order['admin_note'] = I('admin_note'); //
            $order['shipping_code'] = I('shipping');// 物流方式
            $order['shipping_name'] = M('plugin') -> where(array('status'=>1,'type'=>'shipping','code'=>I('shipping')))->getField('name');
            $order['pay_code'] = I('payment');// 支付方式
            $order['pay_name'] = M('plugin') -> where(array('status'=>1,'type'=>'payment','code'=>I('payment')))->getField('name');
            $goods_id_arr = I("goods_id");
            $new_goods = $old_goods_arr = array();
            //################################订单添加商品
            if($goods_id_arr){
                $new_goods = $orderLogic->get_spec_goods($goods_id_arr);
                foreach($new_goods as $key => $val)
                {
                    $val['order_id'] = $order_id;
                    $rec_id = M('order_goods')->add($val);//订单添加商品
                    if(!$rec_id)
                        $this->error('添加失败');
                }
            }

            //################################订单修改删除商品
            $old_goods = I('old_goods');
            foreach ($orderGoods as $val){
                if(empty($old_goods[$val['rec_id']])){
                    M('order_goods') -> where("rec_id=".$val['rec_id'])->delete();//删除商品
                }else{
                    //修改商品数量
                    if($old_goods[$val['rec_id']] != $val['goods_num']){
                        $val['goods_num'] = $old_goods[$val['rec_id']];
                        M('order_goods') -> where("rec_id=".$val['rec_id'])->save(array('goods_num'=>$val['goods_num']));
                    }
                    $old_goods_arr[] = $val;
                }
            }

            $goodsArr = array_merge($old_goods_arr,$new_goods);
            $result = calculate_price($order['user_id'],$goodsArr,$order['shipping_code'],0,$order['province'],$order['city'],$order['district'],0,0,0,0);
            if($result['status'] < 0)
            {
                $this->error($result['msg']);
            }

            //################################修改订单费用
            $order['goods_price']    = $result['result']['goods_price']; // 商品总价
            $order['shipping_price'] = $result['result']['shipping_price'];//物流费
            $order['order_amount']   = $result['result']['order_amount']; // 应付金额
            $order['total_amount']   = $result['result']['total_amount']; // 订单总价
            $o = M('order') -> where('order_id='.$order_id)->save($order);

            $l = $orderLogic->orderActionLog($order_id,'edit','修改订单');//操作日志
            if($o && $l){
                $this->success('修改成功',U('Admin/Order/editprice',array('order_id'=>$order_id)));
            }else{
                $this->success('修改失败',U('Admin/Order/detail',array('order_id'=>$order_id)));
            }
            exit;
        }
        // 获取省份
        $province = M('region') -> where(array('parent_id'=>0,'level'=>1))->select();
        //获取订单城市
        $city =  M('region') -> where(array('parent_id'=>$order['province'],'level'=>2))->select();
        //获取订单地区
        $area =  M('region') -> where(array('parent_id'=>$order['city'],'level'=>3))->select();
        //获取支付方式
        $payment_list = M('plugin') -> where(array('status'=>1,'type'=>'payment'))->select();
        //获取配送方式
        $shipping_list = M('plugin') -> where(array('status'=>1,'type'=>'shipping'))->select();

        $this -> assign('order',$order);
        $this -> assign('province',$province);
        $this -> assign('city',$city);
        $this -> assign('area',$area);
        $this -> assign('orderGoods',$orderGoods);
        $this -> assign('shipping_list',$shipping_list);
        $this -> assign('payment_list',$payment_list);
        $this -> display();
    }

    /**
     * 拆分订单
     */
    public function split_order(){
        exit;
        $order_id = I('order_id');
        $orderLogic = new OrderLogic();
        $order = $orderLogic->getOrderInfo($order_id);
        if($order['shipping_status'] != 0){
            $this->error('已发货订单不允许编辑');
            exit;
        }
        $orderGoods = $orderLogic->getOrderGoods($order_id);
        if(IS_POST){
            $data = I('post.');
            //################################先处理原单剩余商品和原订单信息
            $old_goods = I('goods');
            foreach ($orderGoods as $val){
                if(empty($old_goods[$val['rec_id']])){
                    M('order_goods') -> where("rec_id=".$val['rec_id'])->delete();//删除商品
                }else{
                    //修改商品数量
                    if($old_goods[$val['rec_id']] != $val['goods_num']){
                        $val['goods_num'] = $old_goods[$val['rec_id']];
                        M('order_goods') -> where("rec_id=".$val['rec_id'])->save(array('goods_num'=>$val['goods_num']));
                    }
                    $oldArr[] = $val;//剩余商品
                }
                $all_goods[$val['rec_id']] = $val;//所有商品信息
            }
            $result = calculate_price($order['user_id'],$oldArr,$order['shipping_code'],0,$order['province'],$order['city'],$order['district'],0,0,0,0);
            if($result['status'] < 0)
            {
                $this->error($result['msg']);
            }
            //修改订单费用
            $res['goods_price']    = $result['result']['goods_price']; // 商品总价
            $res['order_amount']   = $result['result']['order_amount']; // 应付金额
            $res['total_amount']   = $result['result']['total_amount']; // 订单总价
            M('order') -> where("order_id=".$order_id)->save($res);
            //################################原单处理结束

            //################################新单处理
            for($i=1;$i<20;$i++){
                if(empty($_POST[$i.'_goods'])){
                    break;
                }else{
                    $split_goods[] = $_POST[$i.'_goods'];
                }
            }

            foreach ($split_goods as $key=>$vrr){
                foreach ($vrr as $k=>$v){
                    $all_goods[$k]['goods_num'] = $v;
                    $brr[$key][] = $all_goods[$k];
                }
            }

            foreach($brr as $goods){
                $result = calculate_price($order['user_id'],$goods,$order['shipping_code'],0,$order['province'],$order['city'],$order['district'],0,0,0,0);
                if($result['status'] < 0)
                {
                    $this->error($result['msg']);
                }
                $new_order = $order;
                $new_order['order_sn'] = date('YmdHis').mt_rand(1000,9999);
                $new_order['parent_sn'] = $order['order_sn'];
                //修改订单费用
                $new_order['goods_price']    = $result['result']['goods_price']; // 商品总价
                $new_order['order_amount']   = $result['result']['order_amount']; // 应付金额
                $new_order['total_amount']   = $result['result']['total_amount']; // 订单总价
                $new_order['add_time'] = time();
                unset($new_order['order_id']);
                $new_order_id = M('order')->add($new_order);//插入订单表
                foreach ($goods as $vv){
                    $vv['order_id'] = $new_order_id;
                    $nid = M('order_goods')->add($vv);//插入订单商品表
                }
            }
            //################################新单处理结束
            $this->success('操作成功',U('Admin/Order/detail',array('order_id'=>$order_id)));
            exit;
        }

        foreach ($orderGoods as $val){
            $brr[$val['rec_id']] = array('goods_num'=>$val['goods_num'],'goods_name'=>getSubstr($val['goods_name'], 0, 35).$val['spec_key_name']);
        }
        $this -> assign('order',$order);
        $this -> assign('goods_num_arr',json_encode($brr));
        $this -> assign('orderGoods',$orderGoods);
        $this -> display();
    }


}
