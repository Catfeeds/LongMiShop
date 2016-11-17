<?php
namespace Common\Behaviors;
use \Think\Behavior;
class BaseBehavior extends Behavior{
    //行为执行入口
    public function run(&$params){
        if(MODULE_NAME == "Home"){
            exit;
        }

        if(MODULE_NAME == "Admin"){
            if( isMobile() ){
                C("DEFAULT_THEME","Moving");
                C("TMPL_PARSE_STRING.__STATIC__","/Template/admin/Moving/Static");
                $routeArray =  array(
                    '/Admin/Index/index'=>'/Admin/Index/welcome',
                );

                if(!empty($routeArray[__ACTION__])){
                    header("Location: ".U($routeArray[__ACTION__])."");
                    exit;
                }
            }else{
                C("DEFAULT_THEME","new");
                C("TMPL_PARSE_STRING.__STATIC__","/Template/admin/new/Static");
            }
        }
    }
}