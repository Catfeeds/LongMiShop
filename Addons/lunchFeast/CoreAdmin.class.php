<?php
@include '/Addons/lunchFeast/Function/base.php';
class lunchFeastAdminController
{

    public $assignData = array();

    public function __construct()
    {
        define("TB_SHOP", "addons_lunchfeast_shop");
        define("TB_MEAL", "addons_lunchfeast_meal_list");
        define("TB_GOODS", "addons_lunchfeast_shop_goods");
        define("TB_CONFIG", "addons_lunchfeast_config");
    }

    //初始页面
    public function index()
    {
        $this->assignData["list"] = array(
            array(
                "title" => "店铺列表",
                "act"   => "shopList"
            ),
            array(
                "title" => "订单列表",
                "act"   => "orderList"
            ),
            array(
                "title" => "基础设置",
                "act"   => "config"
            )
        );
        return $this->assignData;
    }

    /**
     * 系统设置
     * @return array
     */
    public function config(){
        if (($_GET['is_ajax'] == 1) && IS_POST) {
            C('TOKEN_ON', false);
            $data["main"] = I("main");
            if( isExistenceDataWithCondition( TB_CONFIG ) ){
                saveData( TB_CONFIG , array() , $data);
            }else{
                addData( TB_CONFIG , $data);
            }
            $return_arr = array(
                'status' => 1,
                'msg'    => '操作成功',
                'data'   => array('url' => U('Admin/Addons/lunchFeast', array("pluginName" => "config"))),
            );
            exit(json_encode($return_arr));
        }
        $config = findDataWithCondition( TB_CONFIG );
        $this->assignData['URL_upload'] = U('Admin/Ueditor/imageUp', array('savepath' => 'goods'));
        $this->assignData['URL_imageUp'] = U('Admin/Ueditor/imageUp', array('savepath' => 'article'));
        $this->assignData['URL_fileUp'] = U('Admin/Ueditor/fileUp', array('savepath' => 'article'));
        $this->assignData['URL_scrawlUp'] = U('Admin/Ueditor/scrawlUp', array('savepath' => 'article'));
        $this->assignData['URL_getRemoteImage'] = U('Admin/Ueditor/getRemoteImage', array('savepath' => 'article'));
        $this->assignData['URL_imageManager'] = U('Admin/Ueditor/imageManager', array('savepath' => 'article'));
        $this->assignData['URL_getMovie'] = U('Admin/Ueditor/getMovie', array('savepath' => 'article'));
        $this->assignData['URL_Home'] = "";
        $this->assignData["config"] = $config;
        return $this->assignData;
    }

    public function mealList(){

    }

    /**
     * 店铺列表
     * @return array
     */
    public function shopList()
    {
        $this->assignData['regionList'] = get_region_list();
        $count = getCountWithCondition(TB_SHOP);
        $Page  = new \Think\Page( $count , 10 );
        $show = $Page -> show();
        $this->assignData['list'] = M(TB_SHOP)->limit($Page->firstRow,$Page->listRows) -> select();
        $this->assignData['page'] = $show;
        return $this->assignData;
    }


    /**
     * 店铺详情
     * @return array
     */
    public function shopDetail()
    {
        $shopId = I("id", 0);
        $type = intval($shopId) > 0 ? 2 : 1; // 标识自动验证时的 场景 1 表示插入 2 表示更新
        if (($_GET['is_ajax'] == 1) && IS_POST) {
            C('TOKEN_ON', false);
            $shopModel = M(TB_SHOP);
            if (!$shopModel->create(NULL, $type))// 根据表单提交的POST数据创建数据对象
            {
                $return_arr = array(
                    'status' => -1,
                    'msg'    => '操作失败',
                    'data'   => $shopModel->getError(),
                );
                exit(json_encode($return_arr));
            } else {
                $shopModel->create_time = time(); // 上架时间
                if ($type == 2) {
                    $shopModel->save();
                } else {
                    $insert_id = $shopModel->add(); // 写入数据到数据库
                }
                $return_arr = array(
                    'status' => 1,
                    'msg'    => '操作成功',
                    'data'   => array('url' => U('Admin/Addons/lunchFeast', array("pluginName" => "shopList"))),
                );
                exit(json_encode($return_arr));
            }
        }
        $shopInfo = findDataWithCondition(TB_SHOP, array("id" => $shopId)) ;
        $province = selectDataWithCondition('region', array('parent_id' => 0, 'level' => 1));
        $city = selectDataWithCondition('region', array('parent_id' => $shopInfo['province'], 'level' => 2));
        $area = selectDataWithCondition('region', array('parent_id' => $shopInfo['city'], 'level' => 3));
        $this->assignData['URL_upload'] = U('Admin/Ueditor/imageUp', array('savepath' => 'goods'));
        $this->assignData['URL_imageUp'] = U('Admin/Ueditor/imageUp', array('savepath' => 'article'));
        $this->assignData['URL_fileUp'] = U('Admin/Ueditor/fileUp', array('savepath' => 'article'));
        $this->assignData['URL_scrawlUp'] = U('Admin/Ueditor/scrawlUp', array('savepath' => 'article'));
        $this->assignData['URL_getRemoteImage'] = U('Admin/Ueditor/getRemoteImage', array('savepath' => 'article'));
        $this->assignData['URL_imageManager'] = U('Admin/Ueditor/imageManager', array('savepath' => 'article'));
        $this->assignData['URL_getMovie'] = U('Admin/Ueditor/getMovie', array('savepath' => 'article'));
        $this->assignData['URL_Home'] = "";
        $this->assignData['province'] = $province;
        $this->assignData['city'] = $city;
        $this->assignData['area'] = $area;
        $this->assignData['shop'] = $shopInfo;
        return $this->assignData;
    }


    /**
     * 设置菜品
     * @return array
     */
    public function setMeal(){
        $shopId = I("id");
        $shopInfo = findDataWithCondition(TB_SHOP, array("id" => $shopId)) ;
        if( empty( $shopInfo ) ){
            return addonsError( "没有此店" );
        }
        $today = strtotime(date('Y-m-d',strtotime("+1 day")));
        $lastDay = strtotime(date("Y-m-d",strtotime("+1 month +1 day")));
        if (($_GET['is_ajax'] == 1) && IS_POST) {
            C('TOKEN_ON', false);
            $money = I("money");
            $content = I("content");
            if( empty($money) || empty($content) ){
                $return_arr = array(
                    'status' => -1,
                    'msg'    => '参数错误',
                    'data'   => '',
                );
                exit(json_encode($return_arr));
            }
            foreach ($money as $key => $moneyItem){
                $keyArray =  explode("_" , $key);
                $condition = array(
                    "shop_id" => $shopId,
                    "date" =>$keyArray[0],
                    "meal_id" =>$keyArray[1],
                );

                $data = array(
                    "shop_id" => $shopId,
                    "date" =>$keyArray[0],
                    "meal_id" =>$keyArray[1],
                    "content" =>$content[$key],
                    "money" =>$moneyItem,
                );
                if( isExistenceDataWithCondition( TB_GOODS , $condition ) ){
                    saveData( TB_GOODS , $condition , $data );
                }else{
                    $data["create_time"] = time();
                    addData( TB_GOODS , $data );
                }
            }
            $return_arr = array(
                'status' => 1,
                'msg'    => '操作成功',
                'data'   => array('url' => U('Admin/Addons/lunchFeast', array("pluginName" => "shopList"))),
            );
            exit(json_encode($return_arr));
        }
        $mealList = selectDataWithCondition( TB_MEAL , array( "is_show" => 1 , "is_delete" => "0" ) );
        $shopGoods = selectDataWithCondition( TB_GOODS , array( "shop_id" => $shopId ) );
        $goodsList = array();
        if( !empty( $shopGoods ) ){
            foreach ( $shopGoods as $shopGoodsItem ) {
                $goodsList[$shopGoodsItem["date"]."_".$shopGoodsItem["meal_id"]] = $shopGoodsItem;
            }
        }
        $this -> assignData['shop'] = $shopInfo;
        $this -> assignData['today'] = $today;
        $this -> assignData['lastDay'] = $lastDay;
        $this -> assignData['mealList'] = $mealList;
        $this -> assignData['goodsList'] = $goodsList;
        return $this -> assignData;
    }

    //订单列表
    public function orderList(){
        $count = M('addons_lunchfeast_order')->count();
        $Page  = new Think\AjaxPage($count,10);
        $List = M('addons_lunchfeast_order')->limit($Page->firstRow.','.$Page->listRows)->order('create_time DESC')->select();
        $mealList = selectMealList();
        $shopList = selectShopList();
        foreach($List as $key=>$item){
            $List[$key]['meal'] = $mealList[$item['meal_id']];
            $List[$key]['shopName'] = $shopList[$item['shop_id']];
        }
        $this -> assignData['page'] = $Page -> show();
        $this -> assignData['List'] = $List;
        return $this -> assignData;
    }
    //订单详情
    public function orderDetails(){
        $id = I('id');
        $mealList = selectMealList();
        $shopList = selectShopList();
        $details = M('addons_lunchfeast_order')->where(array('id'=>$id))->find();
        $details['meal'] = $mealList[$details['meal_id']];
        $details['shopName'] = $shopList[$details['shop_id']];
        $userList = M('addons_lunchfeast_order_user')->where(array('order_id'=>$id))->select();
        $nickname = M('users')->field('nickname')->where(array('user_id'=>$details['user_id']))->find();
        $details['nickname'] = $nickname['nickname'];
        foreach($userList as $key=>$item){
            $details['perList'][$key] = M('addons_lunchfeast_diningper')->where(array('id'=>$item['diningper_id']))->find();
        }
        $this -> assignData['details'] = $details;
        return $this -> assignData;
    }
    public function orderDetail(){
        return $this -> assignData;
    }
}