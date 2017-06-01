<?php
class foreverCouponMobileController {

    public $assignData = array();
    public $userInfo = array();
    public $user_id = null;

    const TB_USER = "addons_forevercoupon_user";
    const TB_CONFIG = "addons_forevercoupon_config";

    public function __construct($userInfo)
    {
        $this -> userInfo = $userInfo;
        $this -> user_id = $userInfo['user_id'];
    }

    //初始页面
    public function index(){
        $edition = 1;
        if( !isExistenceDataWithCondition(self::TB_USER,array('user_id'=>$this -> userInfo['user_id'],'edition'=>$edition))){
            $config = findDataWithCondition( self::TB_CONFIG );
            $id = $config['coupon_id'];
            if( !empty($config) && !empty($id) && isExistenceDataWithCondition("coupon",array("id"=>$id)) ){
                $data = array(
                    'edition'=>$edition,
                    'user_id'=>$this -> userInfo['user_id'],
                    'create_time'=>time()
                );
                addData(self::TB_USER,$data);
                $id = $config['coupon_id'];
                $condition = array(
                    "uid"=>$this -> userInfo['user_id'],
                    "cid"=>$id,
                    "order_id"=>"",
                );
                if( !isExistenceDataWithCondition("coupon_list",$condition)) {
                    addNewCoupon($id, $this -> userInfo['user_id']);
                }
            }
        }
        header("Location: ".U('Mobile/Index/index'));
        exit;
    }
}