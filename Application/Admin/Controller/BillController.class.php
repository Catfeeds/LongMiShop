<?php

namespace Admin\Controller;

class BillController extends BaseController {


    public function accountStatement(){
        $month = date('m');
        $year = date('Y');
        $adminId = I('adminId');
        $this -> assign('adminId',$adminId);
        $this -> assign('month',$month);
        $this -> assign('year',$year);
        $this -> display();
    }

    public function ajaxAccountStatement(){
        $adminId = I('adminId');
        $type = I('type');
        $year = I('year');
        $month = I('month') ;
        $adminId = !empty($adminId) ? $adminId : session('admin_id');
        $tempTime = date('Y-m-d',strtotime($year.'-'.$month));
        $timeRes = strtotime($tempTime);
        $nextMonth =strtotime("$tempTime +1 month ");
        $condition = array(
            'admin_id'=>$adminId
        );
        $condition['type'] = $type;
        $condition['create_time'] =  array("between",array($timeRes,$nextMonth));


        $list = M('account_statement') -> where($condition)->order('create_time desc')->select();

        $this -> assign('list',$list);
        $this -> display();
    }

}