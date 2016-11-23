<?php

class lunchFeastAdminController
{

    public $assignData = array();

    public function __construct()
    {
        define("TB_SHOP", "addons_lunchfeast_shop");
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
            )
        );
        return $this->assignData;
    }


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
                //  编辑
                $return_arr = array(
                    'status' => -1,
                    'msg'    => '操作失败',
                    'data'   => $shopModel->getError(),
                );
                exit(json_encode($return_arr));
            } else {


                //  form表单提交
                // C('TOKEN_ON',true);
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

    public function orderList(){
        return $this -> assignData;
    }
    public function orderDetail(){
        return $this -> assignData;
    }
}