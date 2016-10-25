<?php



/**
 * 是否为供应商
 * @return bool
 */
function is_supplier(){
    if(session('admin_role_id') == 3){
        return true;
    }
    return false;
}



function getAccountInfo(){
    $condition = array(
        "admin_id" => session("admin_id"),
    );
    return findDataWithCondition("admin",$condition);
}