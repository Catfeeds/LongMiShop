<?php
/**
 *  单例任务
 */

namespace Task\Controller;


class SingleCaseController extends BaseTaskController {

    public function _initialize() {
        parent::_initialize();
        define('CRON_PATH', APP_PATH."Task/CronTab/");
        set_time_limit(0);
        ini_set('memory_limit','1024M');
    }


    public function index(){
        $name = I("name");
        if( empty($name)){
            echo "【警告】参数错误";
        }
        try{
            @include_once  CRON_PATH . $name . ".cron.php";
        } catch (\Exception $e){
            echo "【错误】";
            echo $e -> getMessage();
        }
    }
}