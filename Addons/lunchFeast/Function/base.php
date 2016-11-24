<?php

/**
 * 用餐人置空
 */
function setPitchon($userId){
    M('addons_lunchfeast_diningper')->where(array('uid'=>$userId))->save(array('pitchon'=>0));
}

/**
 * 饭点查询
 */
function selectMealList(){
    return  M('addons_lunchfeast_meal_list')->getField("id ,name" ,true);
}

/**
 * 店铺名字查询
 */
function selectShopList(){
    return  M('addons_lunchfeast_shop')->getField("id ,shop_name" ,true);
}