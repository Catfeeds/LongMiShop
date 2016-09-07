<?php

/*
 * weight 商品 件数/重量
 * log_id 配送方式id
 * site   收获地址
 *
 *
 */
function count_postage(){
    // error_reporting(E_ALL);
    $array = array(
        0=>array(
            'goods_id' =>1, //商品id
            'weight'=> 14500, //商品重量
            'goods_name'=>'平板液晶电视', //商品名称
            'goods_num'=>1, //件数  重量
            'shipping_code' =>15, //配送方式
            'goods_price'=> 3000, //商品价格
            'site' =>array(
                'province' => '北京市'
            ),
        ),
        2=>array(
            'goods_id' =>1, //商品id
            'weight'=> 14500, //商品重量
            'goods_name'=>'平板液晶电视', //商品名称
            'goods_num'=>1, // 件数  重量
            'shipping_code' =>15, //配送方式
            'goods_price'=> 3000, //商品价格
            'site' =>array(
                'province' => '北京市'
            ),
        ),

    );

    if(empty($array)){
        return '参数错误';
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
            $goods_postage[$item['goods_name']] = 0; //商品单个邮费
            continue;
        }


        /*
        *  no_pinkage 指定区域邮费
        *  pinkage    指定区域包邮
        */

        $condition = unserialize($log_res['log_condition']); 
        dump($condition);exit;

        if(!empty($condition['pinkage'])){ //是否属于包邮地区
            foreach ($condition['pinkage'] as $items) {
            	//pinkage_mode  1价钱  2重量
                if($items['pinkage_area'] == $item['site']['province'] && $items['pinkage_mode'] == 1 && $items['pinkage_bound'] >= $item['goods_price']) {  
                    $postage += 0;
                    $goods_postage[$item['goods_name']] = 0; //商品单个邮费
                    break;
                }else if($items['pinkage_area'] == $item['site']['province'] && $items['pinkage_mode'] == 2 && $items['pinkage_bound'] >= $baseValue){
                	$postage += 0;
                    $goods_postage[$item['goods_name']] = 0; //商品单个邮费
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
            $goods_postage[$item['goods_name']] = $money;
            continue;
        }else{
            $exceed = $baseValue - $base; //超出件数

            if( ($exceed % $add_base ) == 0){
                $end_money = ($exceed / $add_base) * $add_money + $money;
            }else{
                $end_money = ( ($exceed / $add_base) + 1 ) * $add_money + $money;
            }

            $postage += $end_money;
            $goods_postage[$item['goods_name']] = $end_money;
        }

        $goods_postage[$item['goods_name']] = 0;

        
    }





}



?>