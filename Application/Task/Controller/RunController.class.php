<?php
/**
 *  定时任务
 */

namespace Task\Controller;


class RunController extends BaseTaskController {

    protected $startTime;

    function exceptAuthActions()
    {
        return null;
    }

    public function _initialize() {
        parent::_initialize();
        define('CRON_PATH', COMMON_PATH . "CronTab/");
        set_time_limit(0);
        ini_set('memory_limit','1024M');
        $this -> startTime = time();
    }


    public function index(){


        @include_once  CRON_PATH . "Order.cron.php";


    }
}