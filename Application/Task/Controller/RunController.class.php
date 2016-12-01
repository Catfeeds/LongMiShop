<?php
/**
 *  定时任务
 */

namespace Task\Controller;


class RunController extends BaseTaskController {

    const CRON_TB = "cron_list";

    protected $startTime;

    public function _initialize() {
        parent::_initialize();
        define('CRON_PATH', APP_PATH."Task/CronTab/");
        set_time_limit(0);
        ini_set('memory_limit','1024M');
        $this -> startTime = time();
    }


    public function index(){
        $cronList = selectDataWithCondition( self::CRON_TB );
        foreach ( $cronList as $cronItem) {
            $id = $cronItem["id"];
            $name = $cronItem["name"];
            $runTime = $cronItem["value"];
            $run = false;
            switch ( $cronItem['key']){
                case "min":
                    $runTime + 60 <= $this -> startTime ? $run = true : false ;
                    break;
                case "hour":
                    $runTime + ( 60 * 60 ) <= $this -> startTime ? $run = true : false ;
                    break;
                case "day":
                    $runTime + ( 60 * 60 * 24 ) <= $this -> startTime ? $run = true : false ;
                    break;
                case "week":
                    $runTime + ( 60 * 60 * 24 * 7 ) <= $this -> startTime ? $run = true : false ;
                    break;
                case "month":
                    $runTime <= strtotime("-1 month") ? $run = true : false ;
                    break;
                case "year":
                    $runTime <= strtotime("-1 year") ? $run = true : false ;
                    break;
                default:
                    break;
            }
            if( $run ==  true ){
                @include_once  CRON_PATH . $name . ".cron.php";
                saveData( self::CRON_TB , array("id"=>$id),array("value" => $this -> startTime));
                setLogResult(  $cronItem , $name , "cron" );
            }
        }
    }
}