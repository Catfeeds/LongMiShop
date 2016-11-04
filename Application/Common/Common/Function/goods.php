<?php

/*
 * weight 商品 件数/重量
 * log_id 配送方式id
 * site   收获地址
 *
 *
 */
function count_postage($array){
    // error_reporting(E_ALL);
    // $array = array(
    //     0=>array(
    //         'goods_id' =>143, //商品id
    //         'weight'=> 14500, //商品重量
    //         'goods_name'=>'平板液晶电视', //商品名称
    //         'goods_num'=>1, //件数  重量
    //         'shipping_code' =>18, //配送方式
    //         'goods_price'=> 3000, //商品价格
    //         'site' =>array(
    //             'province' => '北京市'
    //         ),
    //     ),
    //     2=>array(
    //         'goods_id' =>143, //商品id
    //         'weight'=> 14500, //商品重量
    //         'goods_name'=>'平板液晶电视', //商品名称
    //         'goods_num'=>1, // 件数  重量
    //         'shipping_code' =>18, //配送方式
    //         'goods_price'=> 3000, //商品价格
    //         'site' =>array(
    //             'province' => '北京市'
    //         ),
    //     ),

    // );

    if(empty($array)){
        return callback(false,"参数错误");
    }
    $postage = 0; //邮费总价

    
    foreach($array as $item){
        $log_res = M('logistics')->where("log_id = ".$item['shipping_code'])->find();

        $mode = $log_res['log_mode']; //记重方式

        $baseValue = $mode == 1 ? $item['goods_num'] : $item['goods_num'] * $item['weight'];

        $base = $log_res['log_amount']; 			//基础件数 or 重量
        $money = $log_res['log_cost'];       	//基础邮费
        $add_base = $log_res['log_amount_add']; 	    //增加件数 or 重量
        $add_money = $log_res['log_cost_add'];	    //增加邮费

        if($log_res['log_is_free'] == 1){ //是否包邮
            $postage += 0;
            $goods_postage[$item['goods_id'].'_LM_'.$item['spec_key']] = 0; //商品单个邮费
            continue;
        }



        /*
        *  no_pinkage 指定区域邮费
        *  pinkage    指定区域包邮
        */

        $condition = unserialize($log_res['log_condition']);


        if(!empty($condition['pinkage'])){ //是否属于包邮地区
            foreach ($condition['pinkage'] as $items) {
                //pinkage_mode  1价钱  2重量
                if($items['pinkage_area'] == $item['site']['province'] && $items['pinkage_mode'] == 1 && $items['pinkage_bound'] >= $item['goods_price']) {
                    $postage += 0;
                    $goods_postage[$item['goods_id'].'_LM_'.$item['spec_key']] = 0; //商品单个邮费
                    break;
                }else if($items['pinkage_area'] == $item['site']['province'] && $items['pinkage_mode'] == 2 && $items['pinkage_bound'] >= $baseValue){
                    $postage += 0;
                    $goods_postage[$item['goods_id'].'_LM_'.$item['spec_key']] = 0; //商品单个邮费
                    break;
                }
            }
        }


        if(!empty($condition['no_pinkage'])) { //是否属于指定地区

            foreach ($condition['no_pinkage'] as $items) {

                if ($items['area'] == $item['site']['province']) {

                    $base = $items['base'];            //基础件数 or 重量
                    $money = $items['money'];        //基础邮费
                    $add_base = $items['add_base'];    //增加件数 or 重量
                    $add_money = $items['add_money'];    //增加邮费
                    break;
                }
            }

        }

        if($baseValue <= $base){

            $postage += $money;
            $goods_postage[$item['goods_id'].'_LM_'.$item['spec_key']] = $money;
            continue;
        }else{
            $exceed = $baseValue - $base; //超出件数
            
            if( ($exceed % $add_base ) == 0){
                $end_money = ($exceed / $add_base) * $add_money + $money;
            }else{
                $end_money = ( ($exceed / $add_base) + 1 ) * $add_money + $money;
            }


            $postage += $end_money;
            $goods_postage[$item['goods_id'].'_LM_'.$item['spec_key']] = $end_money; //商品邮费
        }

        // $goods_postage[$item['goods_id']] = 0;


    }

    return callback(true,"邮费计算成功",array("result" => $goods_postage , "count" => $postage));

}




/**
 * 获取商品一二三级分类
 * @return array type
 */
function getGoodsCategoryTree(){
    $result     = array();
    $arr        = array();
    $tree       = array();
    $cat_list = M('goods_category') -> where("is_show = 1") -> order('sort_order') -> cache(true) -> select();//所有分类
    foreach ($cat_list as $val){
        if($val['level'] == 2){
            $arr[$val['parent_id']][] = $val;
        }
        if($val['level'] == 3){
            $crr[$val['parent_id']][] = $val;
        }
        if($val['level'] == 1){
            $tree[] = $val;
        }
    }
    foreach ($arr as $k => $v){
        foreach ($v as $kk => $vv){
            $arr[$k][$kk]['sub_menu'] = empty($crr[$vv['id']]) ? array() : $crr[$vv['id']];
        }
    }
    foreach ($tree as $val){
        $val['tmenu'] = empty($arr[$val['id']]) ? array() : $arr[$val['id']];
        $result[$val['id']] = $val;
    }
    return $result;
}


//商品销量
function commoditySalesVolume($orderId){
    $orderList = M('order_goods')->where(array('order_id'=>$orderId))->select();
    $Goods = M('goods');
    foreach($orderList as $item){
        if($item){
            $Goods->where(array('goods_id'=>$item['goods_id']))->setInc('sales_sum',$item['goods_num']);
        }
    }
}
