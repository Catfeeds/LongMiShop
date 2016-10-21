<?php


namespace Admin\Logic;
use Think\Exception;
use Think\Model;
use Think\Model\RelationModel;

class OrderLogic extends RelationModel
{
    /**
     * @param array $condition  搜索条件
     * @param string $order   排序方式
     * @param int $start    limit开始行
     * @param int $page_size  获取数量
     */
    public function getOrderList($condition,$order='',$start=0,$page_size=20){
        $res = M('order')->where($condition)->limit("$start,$page_size")->order($order)->select();

        return $res;

//        if(is_supplier()){
//            $where .= "1=1";
//            !empty($condition['order_sn']) ? $where .= " o.order_sn = '".$condition['order_sn']."'" : false;
//            !empty($condition['order_status'])  ? $where .= " AND o.order_status = '".$condition['order_status']."'" : false;
//            !empty($condition['pay_status']) ? $where .= " AND o.pay_status = '".$condition['pay_status']."'" : false;
//            !empty($condition['pay_code']) ? $where .= " AND o.pay_code = '".$condition['pay_code']."'" : false;
//            !empty($condition['shipping_status']) ? $where .= " AND o.shipping_status = '".$condition['shipping_status']."'" : false;
//            !empty($condition['user_id']) ? $where .= " AND o.shipping_status = '".$condition['shipping_status']."'" : false;
//            !empty($condition['add_time']) ? $where .= " AND o.add_time > '".$condition['begin']."' AND o.add_time < '".$condition['end']."'" : false  ;
//            $where .= " AND g.admin_id = '".session('admin_id')."'";
//            $sql = "SELECT g.*,o.* FROM __PREFIX__order_goods as g LEFT JOIN __PREFIX__order as o ON o.order_id = g.order_id WHERE ".$where." ORDER BY '".$order."'  LIMIT ".$start.",".$page_size."";
//            $res = $this->query($sql);
//            return $res;
//
//        }else{
//            $res = M('order')->where($condition)->limit("$start,$page_size")->order($order)->select();
//            return $res;
//        }

    }
    /**
     * 获取订单商品详情
     * @param $order_id
     * @param null $type
     * @return mixed
     *
     */
    public function getOrderGoods($order_id ,$type = null ){
        $sql = "SELECT g.*,o.*,(o.goods_num * o.member_goods_price) AS goods_total FROM __PREFIX__order_goods o ".
            "LEFT JOIN __PREFIX__goods g ON o.goods_id = g.goods_id WHERE o.order_id = $order_id";

        if( $type != "all" ){
            if(is_supplier() ){
                $sql .= " and o.admin_id='".session('admin_id')."'";
            }
        }

        $res = $this->query($sql);
        return $res;
    }

    /*
     * 获取订单信息
     */
    public function getOrderInfo($order_id)
    {
        //  订单总金额查询语句		
        $order = M('order')->where("order_id = $order_id")->find();
        $order['address2'] = $this->getAddressName($order['province'],$order['city'],$order['district']);
        $order['address2'] = $order['address2'].$order['address'];		
        return $order;
    }

    /*
     * 根据商品型号获取商品
     */
    public function get_spec_goods($goods_id_arr){
    	if(!is_array($goods_id_arr)) return false;
    		foreach($goods_id_arr as $key => $val)
    		{
    			$arr = array();
    			$goods = M('goods')->where("goods_id = $key")->find();
    			$arr['goods_id'] = $key; // 商品id
                $arr['admin_id'] = $goods['admin_id'];
    			$arr['goods_name'] = $goods['goods_name'];
    			$arr['goods_sn'] = $goods['goods_sn'];
    			$arr['market_price'] = $goods['market_price'];
    			$arr['goods_price'] = $goods['shop_price'];
    			$arr['cost_price'] = $goods['cost_price'];
    			$arr['member_goods_price'] = $goods['shop_price'];
    			foreach($val as $k => $v)
    			{
    				$arr['goods_num'] = $v['goods_num']; // 购买数量
    				// 如果这商品有规格
    				if($k != 'key')
    				{
    					$arr['spec_key'] = $k;
    					$spec_goods = M('spec_goods_price')->where("goods_id = $key and `key` = '{$k}'")->find();
    					$arr['spec_key_name'] = $spec_goods['key_name'];
    					$arr['member_goods_price'] = $arr['goods_price'] = $spec_goods['price'];
    					$arr['sku'] = $spec_goods['sku']; // 参考 sku  http://www.zhihu.com/question/19841574
    				}
    				$order_goods[] = $arr;
    			}
    		}
    		return $order_goods;	
    }

    /*
     * 订单操作记录
     */
    public function orderActionLog($order_id,$action,$note=''){    	
        $order = M('order')->where(array('order_id'=>$order_id))->find();
        if( empty($order) ){
            return false;
        }
        $data['order_id'] = $order_id;
        $data['action_user'] = session('admin_id');
        $data['action_note'] = $note;
        $data['order_status'] = $order['order_status'];
        $data['pay_status'] = $order['pay_status'];
        $data['shipping_status'] = $order['shipping_status'];
        $data['log_time'] = time();
        $data['status_desc'] = $action;        
        return M('order_action')->add($data);//订单操作记录
    }

    /*
     * 获取订单商品总价格
     */
    public function getGoodsAmount($order_id){
        $sql = "SELECT SUM(goods_num * goods_price) AS goods_amount FROM __PREFIX__order_goods WHERE order_id = {$order_id}";
        $res = $this->query($sql);
        return $res[0]['goods_amount'];
    }

    /**
     * 得到发货单流水号
     */
    public function get_delivery_sn()
    {
        /* 选择一个随机的方案 */send_http_status('310');
		mt_srand((double) microtime() * 1000000);
        return date('YmdHi') . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
    }

    /*
     * 获取当前可操作的按钮
     */
    public function getOrderButton($order){
        /*
         *  操作按钮汇总 ：付款、设为未付款、确认、取消确认、无效、去发货、确认收货、申请退货
         * 
         */

    	$os = $order['order_status'];//订单状态
    	$ss = $order['shipping_status'];//发货状态
    	$ps = $order['pay_status'];//支付状态
        $btn = array();
        if($order['pay_code'] == 'cod') {
        	if($os == 0 && $ss == 0){
        		$btn['confirm'] = '确认';
        	}elseif($os == 1 && $ss == 0 ){
        		$btn['delivery'] = '去发货';
        		$btn['cancel'] = '取消确认';
        	}elseif($ss == 1 && $os == 1 && $ps == 0){
        		$btn['pay'] = '付款';
        	}elseif($ps == 1 && $ss == 1 && $os == 1){
        		$btn['pay_cancel'] = '设为未付款';
        	}
        }else{
        	if($ps == 0 && $os == 0){
        		$btn['pay'] = '付款';
        	}elseif($os == 0 && $ps == 1){
        		$btn['pay_cancel'] = '设为未付款';
        		$btn['confirm'] = '确认';
        	}elseif($os == 1 && $ps == 1 && $ss==0){
        		$btn['cancel'] = '取消确认';
        		$btn['delivery'] = '去发货';
        	}
        } 
               
        if($ss == 1 && $os == 1 && $ps == 1){
        	$btn['delivery_confirm'] = '确认收货';
        	$btn['refund'] = '申请退货';
        }elseif($os == 2 || $os == 4){
        	$btn['refund'] = '申请退货';
        }elseif($os == 3 || $os == 5){
        	$btn['remove'] = '移除';
        }
        if($os != 5){
        	$btn['invalid'] = '无效';
        }

        if($ss == 2){
            $btn['delivery'] = '去发货';
        }
        return $btn;
    }

    
    public function orderProcessHandle($order_id,$act){
    	$updata = array();
    	switch ($act){
    		case 'pay': //付款
                        $order_sn = M('order')->where("order_id = $order_id")->getField("order_sn");
                        update_pay_status($order_sn); // 调用确认收货按钮
    			return true;    			
    		case 'pay_cancel': //取消付款
    			$updata['pay_status'] = 0;
    			break;
    		case 'confirm': //确认订单
    			$updata['order_status'] = 1;
    			break;
    		case 'cancel': //取消确认
    			$updata['order_status'] = 0;
    			break;
    		case 'invalid': //作废订单
    			$updata['order_status'] = 5;
    			break;
    		case 'remove': //移除订单
    			$this->delOrder($order_id);
    			break;
    		case 'delivery_confirm'://确认收货
//    			confirm_order($order_id); // 调用确认收货按钮
    			return true;
    		default:
    			return true;
    	}
    	return M('order')->where("order_id=$order_id")->save($updata);//改变订单状态
    }
    
    /**
     *	处理发货单
     * @param array $data  查询数量
     */
    public function deliveryHandle($data){

        $model = new Model();
        try{
            $model  -> startTrans();
            $order = $this->getOrderInfo($data['order_id']);
            $orderGoods = $this->getOrderGoods($data['order_id'],"all");
            $selectGoods = $data['goods'];


            $data['order_sn'] = $order['order_sn'];
            $data['delivery_sn'] = $this->get_delivery_sn();
            $data['zipcode'] = $order['zipcode'];
            $data['user_id'] = $order['user_id'];
            $data['admin_id'] = session('admin_id');
            $data['consignee'] = $order['consignee'];
            $data['mobile'] = $order['mobile'];
            $data['country'] = $order['country'];
            $data['province'] = $order['province'];
            $data['city'] = $order['district'];
            $data['district'] = $order['order_sn'];
            $data['address'] = $order['address'];
            $data['shipping_code'] = $order['shipping_code'];
            $data['shipping_name'] = $order['shipping_name'];
            $data['shipping_price'] = $order['shipping_price'];
            $data['create_time'] = time();
            if( !empty($data['myShippingName']) ){
                $data['shipping_name'] = $order['shipping_name'] = $data['myShippingName'] ;
                unset($data['myShippingName']);
            }
            $did = M('delivery_doc')->add($data);
            if( empty($did) ){
                throw new \Exception('生成发货单失败！');
            }
//            $shippingId = null;
            $is_delivery = 0;
            foreach ($orderGoods as $k=>$v){
                if($v['is_send'] == 1){
                    $is_delivery++;
                }
                if($v['is_send'] == 0 && in_array($v['rec_id'],$selectGoods)){
                    $res['is_send'] = 1;
                    $res['delivery_id'] = $did;
//                    if( ! is_null($shippingId) ){
//                        if( $shippingId != $v['delivery_way'] ){
//                            throw new \Exception('不同物流的商品不能同时发货！');
//                        }
//                    }else{
//                        $shippingId = $v['delivery_way'];
//                    }
                    $r = M('order_goods')->where("rec_id=".$v['rec_id'])->save($res);//改变订单商品发货状态
                    if( $r >0 || $r ===0 ){
                        $is_delivery++;
                    }else{
                        throw new \Exception('修改订单商品状态失败！');
                    }

                }
            }
//            if( empty($shippingId) ){
//                throw new \Exception('物流ID错误！');
//            }
//
//            $logisticsInfo = findDataWithCondition("logistics",array('log_id' => $shippingId));


            $update = array();
            $update['shipping_time'] = time();
            if($is_delivery == count($orderGoods)){
                $update['shipping_status'] = 1;
            }else{
                $update['shipping_status'] = 2;
            }
            M('order')->where("order_id=".$data['order_id'])->save($update);//改变订单状态
            sendWeChatMessageUseUserId( $order['user_id'] , "发货" , array("orderId" => $data['order_id']) );



            $s = $this->orderActionLog($order['order_id'],'delivery',$data['note']);//操作日志
            if( !($s) ){
                new \Exception('保存失败！');
            }
//            throw new \Exception('我是断点！');



            $model  -> commit();
            $mobileMessages = array(
                "kuaidiname" => $data['shipping_name'],
                "kuaidisn" => $data['invoice_no'],
            );
            sendMobileMessages( $order['mobile'] , $mobileMessages  );
            return callback(true,'');
        }catch (\Exception $e){
            $model  -> rollback();

            return callback(false, $e->getMessage());
        }

    }

    /**
     * 获取地区名字
     * @param int $p
     * @param int $c
     * @param int $d
     * @return string
     */
    public function getAddressName($p=0,$c=0,$d=0){
        $p = M('region')->where(array('id'=>$p))->field('name')->find();
        $c = M('region')->where(array('id'=>$c))->field('name')->find();
        $d = M('region')->where(array('id'=>$d))->field('name')->find();
        return $p['name'].','.$c['name'].','.$d['name'].',';
    }

    /**
     * 删除订单
     */
    function delOrder($order_id){
    	$a = M('order')->where(array('order_id'=>$order_id))->delete();
    	$b = M('order_goods')->where(array('order_id'=>$order_id))->delete();
    	return $a && $b;
    }

}