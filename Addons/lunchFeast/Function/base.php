<?php

/**
 * 用餐人置空
 */
function setPitchon($userId){
    M('addons_lunchfeast_diningper')->where(array('uid'=>$userId))->save(array('pitchon'=>0));
}


/**
 * 获取 饭点列表
 * @return mixed
 */
function getMealList(){
    return selectDataWithCondition( "addons_lunchfeast_meal_list" , array( "is_show" => 1 , "is_delete" => "0" ) );
}

/**
 * 获取 店铺列表
 * @return mixed
 */
function getShopList(){
    return selectDataWithCondition( "addons_lunchfeast_shop" , array( "is_online" => 1 ) );
}
/**
 * 获取 店铺菜品列表
 * @param $shopId
 * @param null $mealId
 * @return mixed
 */
function getShopMealList( $shopId , $mealId = null ){
    $condition = array( "shop_id" => $shopId );
    if( !is_null( $mealId ) ){
        $condition["meal_id"] = $mealId;
    }
    return M("addons_lunchfeast_shop_goods") -> where( $condition ) -> order("date") -> select();
}