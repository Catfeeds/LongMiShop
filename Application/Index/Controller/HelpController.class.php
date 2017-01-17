<?php
namespace Index\Controller;

class HelpController extends IndexBaseController {

    function exceptAuthActions()
    {
        return array(
            "buy",
            "pay",
            "postage",
            "about",
            "contact",
            "join",
            'user'
        );
    }

    public function _initialize() {
        parent::_initialize();
    }


    public function user()
    {
        $nameLogic = new \Common\Logic\NameLogic();
        $nameLogic->rndChinaName();
        $numbers = array(
            "01" => "6313",
            "02" => "2575",
            "03" => "1423",
            "04" => "195",
            "05" => "843",
            "06" => "3711",
            "07" => "1331",
            "08" => "374",
            "09" => "8226",
            "10" => "8573",
            "11" => "3528",
            "12" => "228"
        );
        set_time_limit(0);
        $model = new \Think\Model();
        try {
            $model->startTrans();
            foreach ($numbers as $month => $number) {
                $startTime = strtotime("2016-" . $month . "-01");
                if( $month == 12){
                    $endTime = strtotime("2017-01-01");
                }else{
                    $endTime = strtotime("2016-" . ($month + 1) . "-01");
                }
                for ($i = 1; $i <= $number; $i++) {
                    $map = array();
                    $map['user_money'] = 0;
                    $map['nickname'] = $nameLogic->getName(2);
                    $map['reg_time'] = rand($startTime, $endTime);
                    $map['mobile'] = "";
                    $map['mobile_validated'] = 0;
                    $map['oauth'] = "DAORU2";
                    $map['head_pic'] = "";
                    $map['sex'] = 1;
                    $userId = M('users')->add($map);
                    if (empty($userId)) {
                        throw new \Exception('添加用户失败');
                    }
                }
            }
            $model->commit();
        } catch (\Exception $e) {
            $model->rollback();
            echo $e->getMessage();
        }
    }

    public function buy(){
        $this -> display();
    }
    public function pay(){
        $this -> display();
    }
    public function postage(){
        $this -> display();
    }
    public function about(){
        $this -> display();
    }
    public function contact(){
        $this -> display();
    }
    public function join(){
        $this -> display();
    }




}