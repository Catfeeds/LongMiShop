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
            'user',
            'order'
        );
    }

    public function _initialize() {
        parent::_initialize();
    }


    public function user()
    {
//        set_time_limit(0);
//        for($i = 1; $i < 95;$i++){
//            $res = file_get_contents("http://admin.longmiwang.com/Index/Help/user");
//            echo "【".$i."】res:".$res."<br>";
//
//        }
//        exit;
        $nameLogic = new \Common\Logic\NameLogic();
        $nameLogic->rndChinaName();
        $numbers = array(
//            "01" => "6313",
//            "02" => "400",
//            "03" => "200",
//            "04" => "500",
            "06" => "600",
//            "06" => "3711",
//            "07" => "1331",
//            "08" => "374",
//            "09" => "8226",
//            "10" => "8573",
//            "11" => "3528",
//            "12" => "228"
        );
        $orderNumber = array(
//            "02" => "80",
//            "03" => "480",
//            "04" => "80",
//            "05" => "100",
            "06" => "0",
        );
        $model = new \Think\Model();
        try {
            $model->startTrans();
            foreach ($numbers as $month => $number) {
                $startTime = strtotime("2017-" . $month . "-01");
                if( $month == 12){
                    $endTime = strtotime("2018-01-01");
                }else{
//                    $endTime = strtotime("2017-" . ($month + 1) . "-01");
                    $endTime = strtotime("2017-" .$month . "-07");
                }
                for ($i = 1; $i <= $number; $i++) {
                    $map = array();
                    $map['user_money'] = 0;
                    $map['nickname'] = $nameLogic->getName(2);
                    $map['reg_time'] = rand($startTime, $endTime);
                    $map['mobile'] = "";
                    $map['mobile_validated'] = 0;
                    $map['oauth'] = "DAORU4";
                    $map['head_pic'] = "";
                    $map['sex'] = 1;
                    $userId = M('users')->add($map);
                    if (empty($userId)) {
                        throw new \Exception('添加用户失败');
                    }
                }
            }
            foreach ($orderNumber as $month => $number){

                $sql="SELECT r1.* 
 FROM lm_order AS r1 JOIN
    (SELECT ROUND(RAND() * 
           (SELECT MAX(order_id) 
            FROM lm_order)) AS order_id) 
    AS r2 
WHERE r1.order_id >= r2.order_id  and r1.order_status = 4 
ORDER BY r1.order_id ASC
LIMIT 1;";
                for ($i = 1; $i <= $number; $i++) {
                    $orderInfo = M("order")->query($sql);
                    $orderInfo = $orderInfo[0];
                    $data = $orderInfo;
                    unset($data['order_id']);
                    $time = strtotime("2017-" . $month . "-1");
                    $time = rand(1496160000, 1496763983);
                    $data['order_sn'] = date('YmdHis', $time) . rand(1000, 9999);
                    $data['add_time'] = $time;
                    $data['pay_time'] = $time + $orderInfo['pay_time'] - $orderInfo['add_time'];
                    $data['shipping_time'] = $time + $orderInfo['shipping_time'] - $orderInfo['add_time'];
                    $data['confirm_time'] = $time + $orderInfo['confirm_time'] - $orderInfo['add_time'];
                    $order_id = M("order")->add($data);
                    $order_goods_list = selectDataWithCondition("order_goods", array("order_id" => $orderInfo["order_id"]));
                    if (!empty($order_goods_list)) {
                        foreach ($order_goods_list as $order_goods_item) {
                            $data2 = $order_goods_item;
                            unset($data2['rec_id']);
                            $data2["order_id"] = $order_id;
                            isSuccessToAddData("order_goods", $data2);
                        }
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