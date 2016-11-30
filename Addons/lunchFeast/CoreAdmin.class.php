<?php
@include 'Addons/lunchFeast/Function/base.php';
class lunchFeastAdminController
{

    public $assignData = array();

    public function __construct()
    {
        define("TB_SHOP", "addons_lunchfeast_shop");
        define("TB_MEAL", "addons_lunchfeast_meal_list");
        define("TB_GOODS", "addons_lunchfeast_shop_goods");
        define("TB_CONFIG", "addons_lunchfeast_config");
        define("TB_ADMIN", "addons_lunchfeast_admin");
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
            ),

        );

        $this->assignData['statistical'] = array(
            array(
                "title" => "概况",
                "act"   => "statistical"
            ),
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
            $data['title'] = I('title');
            $data['desc'] = I('desc');
            $data['shareimg'] = I('shareimg');
            $data['invite'] = I('invite');
            $data['invited_to'] = I('invited_to');
            $data['invited_value'] = $data['invite'] == 1 ?   I('invite_value_select') :  I('invite_value_input');
            $data['invited_to_value'] =  $data['invited_to'] == 1 ?  I('invited_to_value_select') : I('invited_to_value_input');
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
        $coupon_list = M('coupon')->select();
        $this->assignData["coupon_list"] = $coupon_list;
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
    public function adminList(){
        $shopId = I('id');
        $isAdmin = 0;
        $condition = array("shop_id" => $shopId,"level" => "0");
        $shopInfo = findDataWithCondition(TB_SHOP, array("id" => $shopId)) ;
        if( empty( $shopInfo ) ){
            $condition = array("level" => "1");
            $isAdmin = 1;
        }
        $condition["is_delete"] = 0 ;
        $count = getCountWithCondition( TB_ADMIN , $condition );
        $Page  = new Think\AjaxPage($count,10);
        $list = M( TB_ADMIN ) -> where($condition) ->limit($Page->firstRow.','.$Page->listRows) -> order('create_time DESC')->select();

        $this -> assignData['page'] = $Page -> show();
        $this -> assignData['shopId'] = $shopId;
        $this -> assignData['list'] = $list;
        $this -> assignData['isAdmin'] = $isAdmin;
        return $this -> assignData;
    }
    public function addAdmin(){
        $adminId = I('id');
        $shopId = I('shopId');
        $condition = array("id" => $adminId);
        if( !empty( $shopId ) ){
            $condition["shop_id"] = $shopId;
        }
        $adminInfo = findDataWithCondition( TB_ADMIN, $condition ) ;
        if (($_GET['is_ajax'] == 1) && IS_POST) {
            C('TOKEN_ON', false);
            $username = I("username");
            $password = I("password");
            $desc = I("desc");
            if( empty($username) || empty($password) ){
                $return_arr = array(
                    'status' => -1,
                    'msg'    => '参数错误',
                    'data'   => '',
                );
                exit(json_encode($return_arr));
            }
            if( empty( $adminInfo ) ){
                $add = array(
                    "username" => $username,
                    "password" => $password,
                    "create_time" => time(),
                    "desc" => $desc
                );
                if( !empty( $shopId ) ){
                    $add["shop_id"] = $shopId;
                }else{
                    $add["shop_id"] = "0";
                    $add["level"] = "1";
                }
                addData( TB_ADMIN , $add);
            }else{
                $save = array(
                    "username" => $username,
                    "password" => $password,
                    "desc" => $desc
                );
                saveData( TB_ADMIN , $condition , $save );
            }
            $return_arr = array(
                'status' => 1,
                'msg'    => '操作成功',
                'data'   => array('url' => U('Admin/Addons/lunchFeast', array("pluginName" => "adminList" , "id" => $shopId ))),
            );
            exit(json_encode($return_arr));
        }
        $this -> assignData['shopId'] = $shopId;
        $this -> assignData['adminInfo'] = $adminInfo;
        return $this -> assignData;


    }

    public function deleteAdmin(){
        $adminId = I('id');
        $condition = array("id" => $adminId);
        $save = array( "is_delete"=>"1");
        saveData( TB_ADMIN , $condition ,  $save);
        return addonsSuccess( "删除成功" );
    }

    public function statistical(){
        $yesterdayTime = strtotime(date('Y-m-d',strtotime("-1 day")));
        $today = date('Y-m-d ')."00:00:00";
        $todayTime = strtotime($today);
        $where = "  create_time > ".$yesterdayTime." AND create_time < ".$todayTime." AND status != 0";
        $count['yesterdayCount'] = M('addons_lunchfeast_order')->where($where)->count(); //昨日订单
        $count['yesterdayMoney'] = M('addons_lunchfeast_order')->where($where)->sum('pay_amount'); //昨日成交额
        $count['count'] = M('addons_lunchfeast_order')->where("status != 0")->count(); //累计宴午订单
        $count['countMoney'] = M('addons_lunchfeast_order')->where("status != 0")->sum('pay_amount'); //累计宴午成交额

        $end =  date('Y-m-d H:i:s');
        $this->begin = strtotime("$end -1 year");
        $this->end = strtotime($end);

        //统计订单
        $sql = "SELECT COUNT(*) as tnum,sum(pay_amount) as amount, FROM_UNIXTIME(create_time,'%Y-%m') as gap from  __PREFIX__addons_lunchfeast_order ";
        $sql .= " where create_time >= $this->begin and create_time <= $this->end AND status != 0    ";
        $sql .= "group by gap ORDER BY create_time";
        $res = M()->query($sql); //订单数
        $variate = date('Y-m-d',$this->begin);

        for($i=12;$i>=0;$i--){
            $timestamp =  date('Y M', strtotime('midnight first day of -'.$i.' month'));
            $time = date('Y-m',strtotime($timestamp));
            $listArray[$i] = $time;
        }

        foreach($listArray as $key=>$item ){
            $count['amount'][$key] = 0;
            $count['tnum'][$key] = 0;
            foreach($res as $items){
                if($items['gap'] == $item){
                    $count['amount'][$key] = $items['amount'];
                    $count['tnum'][$key] = $items['tnum'];
                }
            }
        }
        $count['year'] = implode('","' , $listArray);
        $count['year'] = '["'.$count['year'].'"]';
        $count['amount'] = implode('","' , $count['amount']);
        $count['amount'] = '["'.$count['amount'].'"]';
        $count['tnum'] = implode('","' , $count['tnum']);
        $count['tnum'] = '["'.$count['tnum'].'"]';



        //人次
        $prefix = C('DB_PREFIX');
        $join = "LEFT JOIN ".$prefix."addons_lunchfeast_order ON ".$prefix."addons_lunchfeast_shop.id = ".$prefix."addons_lunchfeast_order.shop_id";
        $group = $prefix."addons_lunchfeast_shop.id";
        $getField = $prefix."addons_lunchfeast_shop.id,sum(".$prefix."addons_lunchfeast_order.number) as numbers";
        $ranking = M('addons_lunchfeast_shop')->join($join)->group($group)->getField($getField);
        //用户
        $join .= " LEFT JOIN ".$prefix."addons_lunchfeast_order_user ON ".$prefix."addons_lunchfeast_order.id = ".$prefix."addons_lunchfeast_order_user.order_id";
        $group = $prefix."addons_lunchfeast_order_user.diningper_id";
        $getField = $prefix."addons_lunchfeast_shop.id,count(".$prefix."addons_lunchfeast_order.number) as numbers";
        $userList = M('addons_lunchfeast_shop')->join($join)->field($getField)->group($group)->select();
        dd($userList);
        //销售额
        $rankingMoney = M('addons_lunchfeast_order')->group('shop_id')->order("pay_amount desc")->getField("shop_id,sum(pay_amount) as sumMoney,sum(number) as number ", true);
        foreach ($rankingMoney as $shop_id => $item) {
            $Condition = array(
                "id" => $shop_id
            );

            $resShop = findDataWithCondition( 'addons_lunchfeast_shop' , $Condition , "shop_name" );
            if( !empty( $resShop ) ){
                $Ranking[$shop_id] = array(
                    "money"   => $item['summoney'],
                    "name" => $resShop['shop_name'],
                    'number'=> $item['number'],
                );
            }
        }

        dd($ranking);

        $this -> assignData['count'] = $count;
        return $this -> assignData;
    }

}