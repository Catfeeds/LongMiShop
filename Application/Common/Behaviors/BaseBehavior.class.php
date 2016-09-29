<?php
namespace Common\Behaviors;
use \Think\Behavior;
class BaseBehavior extends Behavior{
    //行为执行入口
    public function run(&$params){
        if(MODULE_NAME == "Home"){
            exit;
        }
    }
}