<?php
include_once "/Function/base.php";
class lunchFeastMobileController
{

    public $assignData = array();
    public $userInfo = array();

    public function __construct( $userInfo )
    {
        $this -> userInfo = $userInfo;
        $this -> assignData["headerPath"] = "./Addons/lunchFeast/Template/Mobile/default/Addons_header.html";
        $this -> assignData["footerPath"] = "./Addons/lunchFeast/Template/Mobile/default/Addons_footer.html";
        define("TB_SHOP", "addons_lunchfeast_shop");
        define("TB_MEAL", "addons_lunchfeast_meal_list");
        define("TB_GOODS", "addons_lunchfeast_shop_goods");
        define("TB_CONFIG", "addons_lunchfeast_config");
        define("TB_ORDER", "addons_lunchfeast_order");
        define("TB_ORDER_USER", "addons_lunchfeast_order_user");
    }
    //主页
    public function index()
    {
        $this -> assignData["config"] = findDataWithCondition( TB_CONFIG );
        return $this->assignData;
    }
    //店铺主页
    public function shopDetail()
    {
        $id = I( "id" );
        $shopInfo = findDataWithCondition( TB_SHOP , array( "id" => $id ) );
        if( empty( $shopInfo ) ){
            return addonsError( "没有此店" );
        }
        $this -> assignData["shopInfo"] = $shopInfo;
        return $this->assignData;
    }
    //我的宴午
    public function orderList()
    {
        return $this->assignData;
    }
    //ajax我的宴午
    public function ajaxOrderList()
    {
        $where = array();
        $type = I('type');
        $type = intval($type);
        if ($type == 1) {
            $where['status'] = $type;
        }
        if ($type == 2) {
            $where['status'] = array("in","2,3");
        }
        $where['user_id'] = $this->userInfo ['user_id'];
        $count = getCountWithCondition(TB_ORDER, $where);
        $limit = 10;
        $Page = new \Think\Page($count, $limit);
        $show = $Page->show();
        $order_str = "pay_time DESC";
        $orderList = M( TB_ORDER )->order($order_str)->where($where)->limit($Page->firstRow . ',' . $Page->listRows)->select();
        if( !empty( $orderList ) ){
            foreach ( $orderList as $orderKey => $orderItem ){
                $orderList[$orderKey]["shopData"] = findDataWithCondition( TB_SHOP , array( "id" => $orderItem["shop_id"] ) );
            }
        }
        $this->assignData['show'] = $show;
        $this->assignData['lists'] = $orderList;
        $this->assignData['p'] = I('p');
        $this->assignData['number'] = I('number');
        $this->assignData['count'] = $count;
        $this->assignData['type'] = $type;
        $this->assignData['limit'] = $limit * I('p');
        return $this->assignData;
    }

    //订单详情 我的二维码
    public function orderDetail()
    {
        return $this->assignData;
    }
    //菜品结果
    public function foods()
    {
        return $this->assignData;
    }
    //提交页面
    public function pageSubmit()
    {
        $list = M('addons_lunchfeast_diningper')->where(array('uid'=>$this -> userInfo['user_id'],'pitchon'=>1))->select();
        $this->assignData['list'] = $list;
        return $this->assignData;
    }
    //移除用餐人
    public function removePer(){
        $id = I('delPerId');
        $res = M('addons_lunchfeast_diningper')->where(array('id'=>$id))->save(array('pitchon'=>0));
        if($res){
            exit(json_encode(callback(true)));
        }
        exit(json_encode(callback(false,'移除失败')));
    }
    //添加用餐人
    public function aMeal()
    {
        if(IS_POST){
            $data = I('post.');
            unset($data['pluginName']);
            $where["id"] = array('in',$data['list']);
            $res[] = M('addons_lunchfeast_diningper')->where($where)->save(array('pitchon'=>1));
            if($res >= 1){
                exit(json_encode(callback(true)));
            }
            exit(json_encode(callback(false)));
        }
        $list = M('addons_lunchfeast_diningper')->where(array('uid'=>$this -> userInfo['user_id'],'pitchon'=>0))->order('add_time DESC')->select();
        $this->assignData['list'] = $list;
        return $this->assignData;
    }
    //新增用餐人
    public function addAMeal()
    {
        if(IS_POST){
            $data = I('post.');
            unset($data['pluginName']);
            $data['uid'] = $this -> userInfo['user_id'];
            $data['add_time'] =  time();
            $res = M('addons_lunchfeast_diningper')->add($data);
            if($res){
                exit(json_encode(callback(true,'添加成功')));
            }
            exit(json_encode(callback(false,'添加失败')));
        }
        return $this->assignData;
    }

    //结算页面
    public function payment()
    {
        return $this->assignData;
    }
    //结果页
    public function results()
    {
        return $this->assignData;
    }
}