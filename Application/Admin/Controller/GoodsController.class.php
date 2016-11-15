<?php
namespace Admin\Controller;
use Admin\Logic\GoodsLogic;
use Think\AjaxPage;
use Think\Page;

class GoodsController extends BaseController {

    /**
     *  商品分类列表
     */
    public function categoryList(){
        $GoodsLogic = new GoodsLogic();
        $cat_list = $GoodsLogic->goods_cat_list();
        $this -> assign('cat_list',$cat_list);
        $this -> display();
    }

    /**
     * 添加修改商品分类
     * 手动拷贝分类正则 ([\u4e00-\u9fa5/\w]+)  ('393','$1'),
     * select * from tp_goods_category where id = 393
    select * from tp_goods_category where parent_id = 393
    update tp_goods_category  set parent_id_path = concat_ws('_','0_76_393',id),`level` = 3 where parent_id = 393
    insert into `tp_goods_category` (`parent_id`,`name`) values
    ('393','时尚饰品'),
     */
    public function addEditCategory(){

        $GoodsLogic = new GoodsLogic();
        if(IS_GET)
        {
            $goods_category_info = D('GoodsCategory') -> where('id='.I('GET.id',0))->find();
            $level_cat = $GoodsLogic->find_parent_cat($goods_category_info['id']); // 获取分类默认选中的下拉框

            $cat_list = M('goods_category') -> where("parent_id = 0")->select(); // 已经改成联动菜单
            $this -> assign('level_cat',$level_cat);
            $this -> assign('cat_list',$cat_list);
            $this -> assign('goods_category_info',$goods_category_info);
            $this -> display('_category');
            exit;
        }

        $GoodsCategory = D('GoodsCategory'); //

        $type = $_POST['id'] > 0 ? 2 : 1; // 标识自动验证时的 场景 1 表示插入 2 表示更新
        //ajax提交验证
        if($_GET['is_ajax'] == 1)
        {
            C('TOKEN_ON',false);

            if(!$GoodsCategory->create(NULL,$type))// 根据表单提交的POST数据创建数据对象
            {
                //  编辑
                $return_arr = array(
                    'status' => -1,
                    'msg'   => '操作失败!',
                    'data'  => $GoodsCategory->getError(),
                );
                $this->ajaxReturn(json_encode($return_arr));
            }else {
                //  form表单提交
                C('TOKEN_ON',true);

                $GoodsCategory->parent_id = $_POST['parent_id_1'];
                $_POST['parent_id_2'] && ($GoodsCategory->parent_id = $_POST['parent_id_2']);

                if($GoodsCategory->id > 0 && $GoodsCategory->parent_id == $GoodsCategory->id)
                {
                    //  编辑
                    $return_arr = array(
                        'status' => -1,
                        'msg'   => '上级分类不能为自己',
                        'data'  => '',
                    );
                    $this->ajaxReturn(json_encode($return_arr));
                }
                if($GoodsCategory->commission_rate > 100)
                {
                    //  编辑
                    $return_arr = array(
                        'status' => -1,
                        'msg'   => '分佣比例不得超过100%',
                        'data'  => '',
                    );
                    $this->ajaxReturn(json_encode($return_arr));
                }
                if ($type == 2)
                {
                    $GoodsCategory->save(); // 写入数据到数据库
                    $GoodsLogic->refresh_cat($_POST['id']);
                }
                else
                {
                    $insert_id = $GoodsCategory->add(); // 写入数据到数据库
                    $GoodsLogic->refresh_cat($insert_id);
                }
                $return_arr = array(
                    'status' => 1,
                    'msg'   => '操作成功',
                    'data'  => array('url'=>U('Admin/Goods/categoryList')),
                );
                $this->ajaxReturn(json_encode($return_arr));

            }
        }

    }

    /**
     * 获取商品分类 的帅选规格 复选框
     */
    public function ajaxGetSpecList(){
        $GoodsLogic = new GoodsLogic();
        $_REQUEST['category_id'] = $_REQUEST['category_id'] ? $_REQUEST['category_id'] : 0;
        $filter_spec = M('GoodsCategory') -> where("id = ".$_REQUEST['category_id'])->getField('filter_spec');
        $filter_spec_arr = explode(',',$filter_spec);
        $str = $GoodsLogic->GetSpecCheckboxList($_REQUEST['type_id'],$filter_spec_arr);
        $str = $str ? $str : '没有可筛选的商品规格';
        exit($str);
    }

    /**
     * 获取商品分类 的帅选属性 复选框
     */
    public function ajaxGetAttrList(){
        $GoodsLogic = new GoodsLogic();
        $_REQUEST['category_id'] = $_REQUEST['category_id'] ? $_REQUEST['category_id'] : 0;
        $filter_attr = M('GoodsCategory') -> where("id = ".$_REQUEST['category_id'])->getField('filter_attr');
        $filter_attr_arr = explode(',',$filter_attr);
        $str = $GoodsLogic->GetAttrCheckboxList($_REQUEST['type_id'],$filter_attr_arr);
        $str = $str ? $str : '没有可筛选的商品属性';
        exit($str);
    }

    /**
     * 删除分类
     */
    public function delGoodsCategory(){
        // 判断子分类
        $GoodsCategory = M("GoodsCategory");
        $count = $GoodsCategory->where("parent_id = {$_GET['id']}")->count("id");
        $count > 0 && $this->error('该分类下还有分类不得删除!',U('Admin/Goods/categoryList'));
        // 判断是否存在商品
        $goods_count = M('Goods') -> where("cat_id = {$_GET['id']}")->count('1');
        $goods_count > 0 && $this->error('该分类下有商品不得删除!',U('Admin/Goods/categoryList'));
        // 删除分类
        $GoodsCategory->where("id = {$_GET['id']}")->delete();
        $this->success("操作成功!!!",U('Admin/Goods/categoryList'));
    }


    /**
     *  商品列表
     */
    public function goodsList(){
        $GoodsLogic = new GoodsLogic();
        $brandList = $GoodsLogic->getSortBrands();
        $categoryList = $GoodsLogic->getSortCategory();
        $this -> assign('categoryList',$categoryList);
        $this -> assign('brandList',$brandList);
        $this -> display();
    }

    /**
     *  商品列表
     */
    public function ajaxGoodsList(){

        $where = ' 1 = 1 '; // 搜索条件
        I('intro')    && $where = "$where and ".I('intro')." = 1" ;
        I('brand_id') && $where = "$where and brand_id = ".I('brand_id') ;
        (I('is_on_sale') !== '') && $where = "$where and is_on_sale = ".I('is_on_sale') ;
        $cat_id = I('cat_id');
        $cat_id = I('cat_id');
        // 关键词搜索
        $key_word = I('key_word') ? trim(I('key_word')) : '';
        if($key_word)
        {
            $where = "$where and (goods_name like '%$key_word%' or goods_sn like '%$key_word%')" ;
        }

        if($cat_id > 0)
        {
            $grandson_ids = getCatGrandson($cat_id);
            $where .= " and cat_id in(".  implode(',', $grandson_ids).") "; // 初始化搜索条件
        }
        if(is_supplier()){
            $where .= " and admin_id ='".session('admin_id')."'";
        }

        $model = M('Goods');
        $count = $model->where($where)->count();
        $Page  = new \Admin\Common\AjaxPage($count,10);
        /**  搜索条件下 分页赋值
        foreach($condition as $key=>$val) {
        $Page->parameter[$key]   =   urlencode($val);
        }
         */
        $show = $Page -> show();
        $orderby1 = I("orderby1","goods_id");
        $orderby2 = I("orderby2","desc");
        $order_str = "`{$orderby1}` {$orderby2}";
        $goodsList = $model->where($where)->order($order_str)->limit($Page->firstRow.','.$Page->listRows) ->select();
        $catList = D('goods_category')->select();
        $catList = convert_arr_key($catList, 'id');
        $this -> assign('catList',$catList);
        $this -> assign('goodsList',$goodsList);
        $this -> assign('page',$show);// 赋值分页输出
        $this -> display();
    }

    /**
     * 下架商品
     *
     */
    public function soldOutAll(){
        $data = I('post.');
        $res = array();
        foreach($data['data'] as $item){
            if(!empty($item)){
                $res[] = M('goods') -> where(array('admin_id'=>session('admin_id'),'goods_id'=>$item))->save(array('is_on_sale'=>0));
            }
        }
        if(in_array('1',$res)){
            exit(json_encode(callback(true,'下架成功')));
        }else{
            exit(json_encode(callback(true,'操作失败')));
        }

    }


    /**
     * 添加修改商品
     */
    public function addEditGoods(){
        $GoodsLogic = new GoodsLogic();
        $Goods = D('Goods'); //
        $type = $_POST['goods_id'] > 0 ? 2 : 1; // 标识自动验证时的 场景 1 表示插入 2 表示更新
        //ajax提交验证
        if(($_GET['is_ajax'] == 1) && IS_POST)
        {
            C('TOKEN_ON',false);
            $mainPart = $_POST['mainPart'];

            $tempArray = array();
            if( !empty( $mainPart  )){
                foreach($mainPart as $key=>$item){
                    if( !empty($_POST['norms_'.$key] )){
                        foreach ($_POST['norms_'.$key] as $minKey => $minItem){
                            $tempArray[$item][$minItem] = $_POST['parameters_'.$key][$minKey];
                        }
                    }
                }
            }

            $_POST["my_parameter"] = base64_encode(serialize($tempArray));
            if(!$Goods->create(NULL,$type))// 根据表单提交的POST数据创建数据对象
            {
                //  编辑
                $return_arr = array(
                    'status' => -1,
                    'msg'   => '操作失败',
                    'data'  => $Goods->getError(),
                );
                $this->ajaxReturn(json_encode($return_arr));
            }else {


                //  form表单提交
                // C('TOKEN_ON',true);
                $Goods->on_time = time(); // 上架时间
                //$Goods->cat_id = $_POST['cat_id_1'];
                $_POST['cat_id_2'] && ($Goods->cat_id = $_POST['cat_id_2']);
                $_POST['cat_id_3'] && ($Goods->cat_id = $_POST['cat_id_3']);


                $_POST['extend_cat_id_2'] && ($Goods->extend_cat_id = $_POST['extend_cat_id_2']);
                $_POST['extend_cat_id_3'] && ($Goods->extend_cat_id = $_POST['extend_cat_id_3']);


                if ($type == 2)
                {

                    $goods_id = $_POST['goods_id'];

                    if (is_supplier()) {
                        $data = $_POST;
                        $goodsRes = findDataWithCondition( 'goods', "goods_id = '$goods_id' " );
                        $sum = 0;
                        $array = array(
                            'store_count',
                            'market_price',
                            'shop_price',
                            'cost_price',
                            'virtual_sales',
                            'virtual_address',
                            'delivery_way',
                            'weight'
                        );
                        if( !empty($data['cat_id_2']))$data['cat_id'] = $data['cat_id_2'];
                        foreach($data as $key=>$item){
                            //条件
                            if($item != $goodsRes[$key] && !in_array($key,$array) && !empty( $goodsRes[$key] )){
                                $sum++;
                            }
                        }
                        if($sum > 0){
                            $Goods->is_on_sale = "0" ;
                        }
                    }
                    $Goods->save(); // 写入数据到数据库
                    $Goods->afterSave($goods_id);
                }
                else
                {
                    if( is_supplier() ){
                        $adminInfo = findDataWithCondition( "admin" , array( "admin_id" => session("admin_id") ) , "goods_limit" );
                        if( !empty( $adminInfo["goods_limit"] ) && $adminInfo["goods_limit"] > 0 ){
                            $goodsCount = M("goods") -> where( array( "admin_id" => session("admin_id") ) ) -> count();
                            if( $goodsCount >= $adminInfo["goods_limit"] ){
                                $return_arr = array(
                                    'status' => -1,
                                    'msg'   => '商品发布数量达到上限',
                                    'data'  => "",
                                );
                                $this->ajaxReturn(json_encode($return_arr));
                            }
                        }
                    }
                    $goods_id = $insert_id = $Goods->add(); // 写入数据到数据库
                    $Goods->adminSave($goods_id);
                    $Goods->afterSave($goods_id);
                }

                $GoodsLogic->saveGoodsAttr($goods_id, $_POST['goods_type']); // 处理商品 属性
                delFile('./Public/upload/goods/thumb/'.$goods_id);
                $return_arr = array(
                    'status' => 1,
                    'msg'   => '操作成功',
                    'data'  => array('url'=>U('Admin/Goods/goodsList')),
                );
                $this->ajaxReturn(json_encode($return_arr));
            }
        }

        $goodsInfo = D('Goods') -> where('goods_id='.I('GET.id',0))->find();
        $goodsInfo['my_parameter'] = unserialize(base64_decode($goodsInfo['my_parameter']));
        $paRcount = count($goodsInfo['my_parameter']);


        if(is_supplier() && $goodsInfo['admin_id'] != session('admin_id') && $type==2 ){
            $this->error('这不是你的商品');
        }
        //$cat_list = $GoodsLogic->goods_cat_list(); // 已经改成联动菜单
        $level_cat = $GoodsLogic->find_parent_cat($goodsInfo['cat_id']); // 获取分类默认选中的下拉框
        $level_cat2 = $GoodsLogic->find_parent_cat($goodsInfo['extend_cat_id']); // 获取分类默认选中的下拉框
        $cat_list = M('goods_category') -> where("parent_id = 0")->select(); // 已经改成联动菜单

        $logisticsWhere = array();
        if( is_supplier() ) {
            $logisticsWhere['admin_id'] = session("admin_id");
        }
        $logistics_list = M('logistics') -> where($logisticsWhere)->field('log_id,log_template')->select(); //获取所有配送方式
        if( empty($logistics_list) ){
            $this->error("请先添加配送方式!!!",U('Admin/Logistics/add'));
            exit;
        }
        $brandList = $GoodsLogic->getSortBrands();
        $goodsType = M("GoodsType")->select();
        $this -> assign('level_cat',$level_cat);
        $this -> assign('level_cat2',$level_cat2);
        $this -> assign('cat_list',$cat_list);
        $this -> assign('brandList',$brandList);
        $this -> assign('goodsType',$goodsType);
        $this -> assign('goodsInfo',$goodsInfo);  // 商品详情
        $goodsImages = M("GoodsImages") -> where('goods_id ='.I('GET.id',0))->select();
        $this -> assign('goodsImages',$goodsImages);  // 商品相册
        $this -> assign('logistics_list',$logistics_list);
        $this -> assign('paRcount',$paRcount);
        $this -> assign('parI',0);
        $this->initEditor(); // 编辑器
        $this -> display('_goods');
    }



    /**
     * 商品类型  用于设置商品的属性
     */
    public function goodsTypeList(){
        $model = M("GoodsType");
        $count = $model->count();
        $Page  = new Page($count,100);
        $show  = $Page -> show();
        $goodsTypeList = $model->order("id desc")->limit($Page->firstRow.','.$Page->listRows)->select();
        $this -> assign('show',$show);
        $this -> assign('goodsTypeList',$goodsTypeList);
        $this -> display('goodsTypeList');
    }


    /**
     * 添加修改编辑  商品属性类型
     */
    public  function addEditGoodsType(){
        $_GET['id'] = $_GET['id'] ? $_GET['id'] : 0;
        $model = M("GoodsType");
        if(IS_POST)
        {
            $model->create();
            if($_GET['id'])
                $model->save();
            else
                $model->add();

            $this->success("操作成功!!!",U('Admin/Goods/goodsTypeList'));
            exit;
        }
        $goodsType = $model->find($_GET['id']);
        $this -> assign('goodsType',$goodsType);
        $this -> display('_goodsType');
    }

    /**
     * 商品属性列表
     */
    public function goodsAttributeList(){
        $goodsTypeList = M("GoodsType")->select();
        $this -> assign('goodsTypeList',$goodsTypeList);
        $this -> display();
    }

    /**
     *  商品属性列表
     */
    public function ajaxGoodsAttributeList(){
        //ob_start('ob_gzhandler'); // 页面压缩输出
        $where = ' 1 = 1 '; // 搜索条件
        I('type_id')   && $where = "$where and type_id = ".I('type_id') ;
        // 关键词搜索
        $model = M('GoodsAttribute');
        $count = $model->where($where)->count();
        $Page       = new AjaxPage($count,13);
        $show = $Page -> show();
        $goodsAttributeList = $model->where($where)->order('`order` desc,attr_id DESC')->limit($Page->firstRow.','.$Page->listRows)->select();
        $goodsTypeList = M("GoodsType")->getField('id,name');
        $attr_input_type = array(0=>'手工录入',1=>' 从列表中选择',2=>' 多行文本框');
        $this -> assign('attr_input_type',$attr_input_type);
        $this -> assign('goodsTypeList',$goodsTypeList);
        $this -> assign('goodsAttributeList',$goodsAttributeList);
        $this -> assign('page',$show);// 赋值分页输出
        $this -> display();
    }

    /**
     * 添加修改编辑  商品属性
     */
    public  function addEditGoodsAttribute(){

        $model = D("GoodsAttribute");
        $type = $_POST['attr_id'] > 0 ? 2 : 1; // 标识自动验证时的 场景 1 表示插入 2 表示更新
        $_POST['attr_values'] = str_replace('_', '', $_POST['attr_values']); // 替换特殊字符
        $_POST['attr_values'] = str_replace('@', '', $_POST['attr_values']); // 替换特殊字符
        $_POST['attr_values'] = trim($_POST['attr_values']);

        if(($_GET['is_ajax'] == 1) && IS_POST)//ajax提交验证
        {
            C('TOKEN_ON',false);
            if(!$model->create(NULL,$type))// 根据表单提交的POST数据创建数据对象
            {
                //  编辑
                $return_arr = array(
                    'status' => -1,
                    'msg'   => '',
                    'data'  => $model->getError(),
                );
                $this->ajaxReturn(json_encode($return_arr));
            }else {
                // C('TOKEN_ON',true); //  form表单提交
                if ($type == 2)
                {
                    $model->save(); // 写入数据到数据库
                }
                else
                {
                    $insert_id = $model->add(); // 写入数据到数据库
                }
                $return_arr = array(
                    'status' => 1,
                    'msg'   => '操作成功',
                    'data'  => array('url'=>U('Admin/Goods/goodsAttributeList')),
                );
                $this->ajaxReturn(json_encode($return_arr));
            }
        }
        // 点击过来编辑时
        $_GET['attr_id'] = $_GET['attr_id'] ? $_GET['attr_id'] : 0;
        $goodsTypeList = M("GoodsType")->select();
        $goodsAttribute = $model->find($_GET['attr_id']);
        $this -> assign('goodsTypeList',$goodsTypeList);
        $this -> assign('goodsAttribute',$goodsAttribute);
        $this -> display('_goodsAttribute');
    }

    /**
     * 更改指定表的指定字段
     */
    public function updateField(){
        $primary = array(
            'goods' => 'goods_id',
            'goods_category' => 'id',
            'brand' => 'id',
            'goods_attribute' => 'attr_id',
            'ad' =>'ad_id',
        );
        $model = D($_POST['table']);
        $model->$primary[$_POST['table']] = $_POST['id'];
        $model->$_POST['field'] = $_POST['value'];
        $model->save();
        $return_arr = array(
            'status' => 1,
            'msg'   => '操作成功',
            'data'  => array('url'=>U('Admin/Goods/goodsAttributeList')),
        );
        $this->ajaxReturn(json_encode($return_arr));
    }
    /**
     * 动态获取商品属性输入框 根据不同的数据返回不同的输入框类型
     */
    public function ajaxGetAttrInput(){
        $GoodsLogic = new GoodsLogic();
        $str = $GoodsLogic->getAttrInput($_REQUEST['goods_id'],$_REQUEST['type_id']);
        exit($str);
    }

    /**
     * 删除商品
     */
    public function delGoods()
    {
        $goods_id = $_GET['id'];
        $error = '';

        // 判断此商品是否有订单
        $c1 = M('OrderGoods') -> where("goods_id = $goods_id")->count('1');
        $c1 && $error .= '此商品有订单,不得删除! <br/>';


        // 商品团购
        $c1 = M('group_buy') -> where("goods_id = $goods_id")->count('1');
        $c1 && $error .= '此商品有团购,不得删除! <br/>';

        // 商品退货记录
        $c1 = M('return_goods') -> where("goods_id = $goods_id")->count('1');
        $c1 && $error .= '此商品有退货记录,不得删除! <br/>';

        if($error)
        {
            $return_arr = array('status' => -1,'msg' =>$error,'data'  =>'',);   //$return_arr = array('status' => -1,'msg' => '删除失败','data'  =>'',);
            $this->ajaxReturn(json_encode($return_arr));
        }

        // 删除此商品
        M("Goods") -> where('goods_id ='.$goods_id)->delete();  //商品表
        M("cart") -> where('goods_id ='.$goods_id)->delete();  // 购物车
        M("comment") -> where('goods_id ='.$goods_id)->delete();  //商品评论
        M("goods_consult") -> where('goods_id ='.$goods_id)->delete();  //商品咨询
        M("goods_images") -> where('goods_id ='.$goods_id)->delete();  //商品相册
        M("spec_goods_price") -> where('goods_id ='.$goods_id)->delete();  //商品规格
        M("spec_image") -> where('goods_id ='.$goods_id)->delete();  //商品规格图片
        M("goods_attr") -> where('goods_id ='.$goods_id)->delete();  //商品属性
        M("goods_collect") -> where('goods_id ='.$goods_id)->delete();  //商品收藏

        $return_arr = array('status' => 1,'msg' => '操作成功','data'  =>'',);   //$return_arr = array('status' => -1,'msg' => '删除失败','data'  =>'',);
        $this->ajaxReturn(json_encode($return_arr));
    }

    /**
     * 删除商品类型
     */
    public function delGoodsType()
    {
        // 判断 商品规格
        $count = M("Spec") -> where("type_id = {$_GET['id']}")->count("1");
        $count > 0 && $this->error('该类型下有商品规格不得删除!',U('Admin/Goods/goodsTypeList'));
        // 判断 商品属性
        $count = M("GoodsAttribute") -> where("type_id = {$_GET['id']}")->count("1");
        $count > 0 && $this->error('该类型下有商品属性不得删除!',U('Admin/Goods/goodsTypeList'));
        // 删除分类
        M('GoodsType') -> where("id = {$_GET['id']}")->delete();
        $this->success("操作成功!!!",U('Admin/Goods/goodsTypeList'));
    }

    /**
     * 删除商品属性
     */
    public function delGoodsAttribute()
    {
        // 判断 有无商品使用该属性
        $count = M("GoodsAttr") -> where("attr_id = {$_GET['id']}")->count("1");
        $count > 0 && $this->error('有商品使用该属性,不得删除!',U('Admin/Goods/goodsAttributeList'));
        // 删除 属性
        M('GoodsAttribute') -> where("attr_id = {$_GET['id']}")->delete();
        $this->success("操作成功!!!",U('Admin/Goods/goodsAttributeList'));
    }

    /**
     * 删除商品规格
     */
    public function delGoodsSpec()
    {
        // 判断 商品规格项
        $count = M("SpecItem") -> where("spec_id = {$_GET['id']}")->count("1");
        $count > 0 && $this->error('清空规格项后才可以删除!',U('Admin/Goods/specList'));
        // 删除分类
        M('Spec') -> where("id = {$_GET['id']}")->delete();
        $this->success("操作成功!!!",U('Admin/Goods/specList'));
    }

    /**
     * 品牌列表
     */
    public function brandList(){
        $model = M("Brand");
        $where = "";
        $keyword = I('keyword');
        $where = $keyword ? " name like '%$keyword%' " : "";
        $count = $model->where($where)->count();
        $Page  = new Page($count,10);
        $brandList = $model->where($where)->order("`sort` asc")->limit($Page->firstRow.','.$Page->listRows)->select();
        $show  = $Page -> show();
        $cat_list = M('goods_category') -> where("parent_id = 0")->getField('id,name'); // 已经改成联动菜单
        $this -> assign('cat_list',$cat_list);
        $this -> assign('show',$show);
        $this -> assign('brandList',$brandList);
        $this -> display('brandList');
    }

    /**
     * 添加修改编辑  商品品牌
     */
    public  function addEditBrand(){
        $id = I('id');
        $model = M("Brand");
        if(IS_POST)
        {
            $model->create();
            if($id)
                $model->save();
            else
                $id = $model->add();

            $this->success("操作成功!!!",U('Admin/Goods/brandList',array('p'=>$_GET['p'])));
            exit;
        }
        $cat_list = M('goods_category') -> where("parent_id = 0")->select(); // 已经改成联动菜单
        $this -> assign('cat_list',$cat_list);
        $brand = $model->find($id);
        $this -> assign('brand',$brand);
        $this -> display('_brand');
    }

    /**
     * 删除品牌
     */
    public function delBrand()
    {
        // 判断此品牌是否有商品在使用
        $goods_count = M('Goods') -> where("brand_id = {$_GET['id']}")->count('1');
        if($goods_count)
        {
            $return_arr = array('status' => -1,'msg' => '此品牌有商品在用不得删除!','data'  =>'',);   //$return_arr = array('status' => -1,'msg' => '删除失败','data'  =>'',);
            $this->ajaxReturn(json_encode($return_arr));
        }

        $model = M("Brand");
        $model->where('id ='.$_GET['id'])->delete();
        $return_arr = array('status' => 1,'msg' => '操作成功','data'  =>'',);   //$return_arr = array('status' => -1,'msg' => '删除失败','data'  =>'',);
        $this->ajaxReturn(json_encode($return_arr));
    }

    /**
     * 初始化编辑器链接
     * 本编辑器参考 地址 http://fex.baidu.com/ueditor/
     */
    private function initEditor()
    {
        $this -> assign("URL_upload", U('Admin/Ueditor/imageUp',array('savepath'=>'goods'))); // 图片上传目录
        $this -> assign("URL_imageUp", U('Admin/Ueditor/imageUp',array('savepath'=>'article'))); //  不知道啥图片
        $this -> assign("URL_fileUp", U('Admin/Ueditor/fileUp',array('savepath'=>'article'))); // 文件上传s
        $this -> assign("URL_scrawlUp", U('Admin/Ueditor/scrawlUp',array('savepath'=>'article')));  //  图片流
        $this -> assign("URL_getRemoteImage", U('Admin/Ueditor/getRemoteImage',array('savepath'=>'article'))); // 远程图片管理
        $this -> assign("URL_imageManager", U('Admin/Ueditor/imageManager',array('savepath'=>'article'))); // 图片管理
        $this -> assign("URL_getMovie", U('Admin/Ueditor/getMovie',array('savepath'=>'article'))); // 视频上传
        $this -> assign("URL_Home", "");
    }



    /**
     * 商品规格列表
     */
    public function specList(){
        $goodsTypeList = M("GoodsType")->select();
        $this -> assign('goodsTypeList',$goodsTypeList);
        $this -> display();
    }


    /**
     *  商品规格列表
     */
    public function ajaxSpecList(){
        //ob_start('ob_gzhandler'); // 页面压缩输出
        $where = ' 1 = 1 '; // 搜索条件
        I('type_id')   && $where = "$where and type_id = ".I('type_id') ;
        // 关键词搜索
        $model = D('spec');
        $count = $model->where($where)->count();
        $Page       = new AjaxPage($count,13);
        $show = $Page -> show();
        $specList = $model->where($where)->order('`type_id` desc')->limit($Page->firstRow.','.$Page->listRows)->select();
        $GoodsLogic = new GoodsLogic();
        foreach($specList as $k => $v)
        {       // 获取规格项
            $arr = $GoodsLogic->getSpecItem($v['id']);
            $specList[$k]['spec_item'] = implode(' , ', $arr);
        }

        $this -> assign('specList',$specList);
        $this -> assign('page',$show);// 赋值分页输出
        $goodsTypeList = M("GoodsType")->select(); // 规格分类
        $goodsTypeList = convert_arr_key($goodsTypeList, 'id');
        $this -> assign('goodsTypeList',$goodsTypeList);
        $this -> display();
    }
    /**
     * 添加修改编辑  商品规格
     */
    public  function addEditSpec(){

        $model = D("spec");
        $type = $_POST['id'] > 0 ? 2 : 1; // 标识自动验证时的 场景 1 表示插入 2 表示更新
        if(($_GET['is_ajax'] == 1) && IS_POST)//ajax提交验证
        {
            C('TOKEN_ON',false);
            if(!$model->create(NULL,$type))// 根据表单提交的POST数据创建数据对象
            {
                //  编辑
                $return_arr = array(
                    'status' => -1,
                    'msg'   => '',
                    'data'  => $model->getError(),
                );
                $this->ajaxReturn(json_encode($return_arr));
            }else {
                // C('TOKEN_ON',true); //  form表单提交
                if ($type == 2)
                {
                    $model->save(); // 写入数据到数据库
                    $model->afterSave($_POST['id']);
                }
                else
                {
                    $insert_id = $model->add(); // 写入数据到数据库
                    $model->afterSave($insert_id);
                }
                $return_arr = array(
                    'status' => 1,
                    'msg'   => '操作成功',
                    'data'  => array('url'=>U('Admin/Goods/specList')),
                );
                $this->ajaxReturn(json_encode($return_arr));
            }
        }
        // 点击过来编辑时
        $id = $_GET['id'] ? $_GET['id'] : 0;
        $spec = $model->find($id);
        $GoodsLogic = new GoodsLogic();
        $items = $GoodsLogic->getSpecItem($id);
        $spec[items] = implode(PHP_EOL, $items);
        $this -> assign('spec',$spec);

        $goodsTypeList = M("GoodsType")->select();
        $this -> assign('goodsTypeList',$goodsTypeList);
        $this -> display('_spec');
    }


    /**
     * 动态获取商品规格选择框 根据不同的数据返回不同的选择框
     */
    public function ajaxGetSpecSelect(){
        $goods_id = $_GET['goods_id'] ? $_GET['goods_id'] : 0;
        $GoodsLogic = new GoodsLogic();
        //$_GET['spec_type'] =  13;
        $specList = D('Spec') -> where("type_id = ".$_GET['spec_type'])->order('`order` desc')->select();
        foreach($specList as $k => $v)
            $specList[$k]['spec_item'] = D('SpecItem') -> where("spec_id = ".$v['id'])->getField('id,item'); // 获取规格项

        $items_id = M('SpecGoodsPrice') -> where('goods_id = '.$goods_id)->getField("GROUP_CONCAT(`key` SEPARATOR '_') AS items_id");
        $items_ids = explode('_', $items_id);

        // 获取商品规格图片
        if($goods_id)
        {
            $specImageList = M('SpecImage') -> where("goods_id = $goods_id")->getField('spec_image_id,src');
        }
        $this -> assign('specImageList',$specImageList);

        $this -> assign('items_ids',$items_ids);
        $this -> assign('specList',$specList);
        $this -> display('ajax_spec_select');
    }

    /**
     * 动态获取商品规格输入框 根据不同的数据返回不同的输入框
     */
    public function ajaxGetSpecInput(){
        $GoodsLogic = new GoodsLogic();
        $goods_id = $_REQUEST['goods_id'] ? $_REQUEST['goods_id'] : 0;
        $str = $GoodsLogic->getSpecInput($goods_id ,$_POST['spec_arr']);
        exit($str);
    }



    public function ajaxGetOptionHtml(){
        $goods_id = $_GET['goods_id'] ? $_GET['goods_id'] : 0;
//        $temp = M()->query("SELECT GROUP_CONCAT(`key` SEPARATOR '_' ) AS goods_spec_item FROM __PREFIX__spec_goods_price WHERE goods_id = '".$goods_id."'");
//
//        $goods_spec_item = $temp[0]['goods_spec_item'];
//        $goods_spec_item = array_unique(explode('_',$goods_spec_item));
//        if($goods_spec_item[0] != ''){
            $spec_item = M() ->fetchSql()->query("SELECT i.*,s.name FROM __PREFIX__spec_item i LEFT JOIN __PREFIX__spec s ON s.id = i.spec_id WHERE s.goods_id ='".$goods_id."' ");//and i.id IN (".implode(',',$goods_spec_item).")

            $new_arr = array();
            foreach($spec_item as $k=>$v){
                $new_arr[$v['name']]["title"] = $v['name'];
                $new_arr[$v['name']]["id"] = $v['id'];
                $v['title'] = $v['item'];
                $new_arr[$v['name']]["items"][] = $v;
            }
            $this -> assign('specList',$new_arr);
//        }

        $specGoodsPriceList = M( "spec_goods_price" ) -> where( array("goods_id" => $goods_id) ) -> select();

        $html = '';
        $html .= '<table class="table table-bordered table-condensed">';
        $html .= '<thead>';
        $html .= '<tr class="active">';
        $specs = array();
        if( !empty($new_arr) ){
            foreach($new_arr as $k=>$v){
                $specs[] = $v;
            }
        }

        $len = count($specs);
        $newlen = 1; //多少种组合
        $h = array(); //显示表格二维数组
        $rowspans = array(); //每个列的rowspan
        for ($i = 0; $i < $len; $i++) {
            //表头
            $html .= "<th style='width:80px;'>" . $specs[$i]['title'] . "</th>";


            //计算多种组合
            $itemlen = count($specs[$i]['items']);
            if ($itemlen <= 0) {
                $itemlen = 1;
            }
            $newlen *= $itemlen;
            //初始化 二维数组
            $h = array();
            for ($j = 0; $j < $newlen; $j++) {
                $h[$i][$j] = array();
            }
            //计算rowspan
            $l = count($specs[$i]['items']);
            $rowspans[$i] = 1;
            for ($j = $i + 1; $j < $len; $j++) {
                $rowspans[$i]*= count($specs[$j]['items']);
            }
        }
        $html .= '<th class="info" style="width:120px;"><div class=""><div style="padding-bottom:10px;text-align:center;font-size:16px;">库存</div><div class="input-group"><input type="text" class="form-control option_store_count_all"  VALUE=""/><span class="input-group-addon"><a href="javascript:;" class="fa fa-hand-o-down" title="批量设置" onclick="setCol(\'option_store_count\');"></a></span></div></div></th>';
        $html .= '<th class="success" style="width:120px;"><div class=""><div style="padding-bottom:10px;text-align:center;font-size:16px;">价格</div><div class="input-group"><input type="text" class="form-control option_price_all"  VALUE=""/><span class="input-group-addon"><a href="javascript:;" class="fa fa-hand-o-down" title="批量设置" onclick="setCol(\'option_price\');"></a></span></div></div></th>';
        $html .= '<th class="danger" style="width:120px;" ><div class=""><div style="padding-bottom:10px;text-align:center;font-size:16px;">重量（克）</div><div class="input-group"><input type="text" class="form-control option_weight_all"  VALUE=""/><span class="input-group-addon"><a href="javascript:;" class="fa fa-hand-o-down" title="批量设置" onclick="setCol(\'option_weight\');"></a></span></div></div></th>';
        $html .= '</tr></thead>';
        for($m=0;$m<$len;$m++){
            $k = 0;$kid = 0;$n=0;
            for($j=0;$j<$newlen;$j++){
                $rowspan = $rowspans[$m]; //9
                if( $j % $rowspan==0){
                    $h[$m][$j]=array("html"=> "<td rowspan='".$rowspan."'>".$specs[$m]['items'][$kid]['title']."</td>","id"=>$specs[$m]['items'][$kid]['id']);
                    // $k++; if($k>count($specs[$m]['items'])-1) { $k=0; }
                }
                else{
                    $h[$m][$j]=array("html"=> "","id"=>$specs[$m]['items'][$kid]['id']);
                }
                $n++;
                if($n==$rowspan){
                    $kid++; if($kid>count($specs[$m]['items'])-1) { $kid=0; }
                    $n=0;
                }
            }
        }

        $hh = "";
        for ($i = 0; $i < $newlen; $i++) {
            $hh.="<tr>";
            $ids = array();
            for ($j = 0; $j < $len; $j++) {
                $hh.=$h[$j][$i]['html'];
                $ids[] = $h[$j][$i]['id'];
            }
            sort($ids);
            $ids = implode("_", $ids);
            $val = array("id" => "","title"=>"", "stock" => "", "pv" => "","costprice" => "", "productprice" => "", "marketprice" => "", "weight" => "");
            foreach ($specGoodsPriceList as $o) {
                if ($ids === $o['key']) {
                    $val = array(
                        "goods_id" => $o['goods_id'],
                        "key" => $o['key'],
                        "title" =>$o['key_name'],
                        "store_count" => $o['store_count'],
                        "price" => $o['price'],
                        "weight" => $o['weight']
                    );
                    break;
                }
            }
            $hh .= '<td class="info">';
            $hh .= '<input name="option_store_count_' . $ids . '[]"  type="text" class="form-control option_store_count option_store_count_' . $ids . '" value="' . $val['store_count'] . '"/></td>';
            $hh .= '<input name="option_goods_id_' . $ids . '[]"  type="hidden" class="form-control option_goods_id option_goods_id_' . $ids . '" value="' . $val['goods_id'] . '"/>';
            $hh .= '<input name="option_key_' . $ids . '[]"  type="hidden" class="form-control option_key option_key_' . $ids . '" value="' . $val['key'] . '"/>';
            $hh .= '<input name="option_ids[]"  type="hidden" class="form-control option_ids option_ids_' . $ids . '" value="' . $ids . '"/>';
            $hh .= '<input name="option_key_name_' . $ids . '[]"  type="hidden" class="form-control option_key_name option_key_name_' . $ids . '" value="' . $val['key_name'] . '"/>';
            $hh .= '</td>';
            $hh .= '<td class="success"><input name="option_price_' . $ids . '[]" type="text" class="form-control option_price option_price_' . $ids . '" value="' . $val['price'] . '"/></td>';
            $hh .= '<td class="danger"><input name="option_weight_' . $ids . '[]" type="text" class="form-control option_weight  option_weight_' . $ids . '" " value="' . $val['weight'] . '"/></td>';
            $hh .= '</tr>';
        }
        $html .= $hh;
        $html .= "</table>";

        if( empty($specGoodsPriceList) ){
            $html = '';
        }
        $this -> assign('html',$html);
        $this -> assign('goods_id',$goods_id);
        $this -> display();
    }

    public function goodsCheck(){
        if(IS_POST){
            $id = I('goods_id');
            $type = I('type');
            $uptatetime = time();
            if($type == 'getOn'){
                $is_on_sale = 1;
                $check = 1;
                $mes = '上';
            }else{
                $is_on_sale = 0;
                $check = 2;
                $mes = '下';
            }
            $goodData = array(

            );
            $goodsRes = M('goods')->save(array('goods_id'=>$id,'is_on_sale'=>$is_on_sale));
            $checkRes = M('goods_check') -> where(array('goods_id'=>$id))->save(array('check'=>$check,'uptatetime'=>$uptatetime));
            if($goodsRes && $checkRes){
                $mes = $mes."架成功";
                exit(json_encode(callback(true,$mes)));
//                $this->success($mes.'架成功',U('Admin/Goods/goodsCheck'));
            }else{
                $mes = $mes."架失败";
                exit(json_encode(callback(false,$mes)));
            }
            exit;

        }
        $this -> display();
    }

    public function ajaxGoodsCheck(){
//        $where = ' 1 = 1 '; // 搜索条件
//        I('intro')    && $where = "$where and ".I('intro')." = 1" ;
//        I('brand_id') && $where = "$where and brand_id = ".I('brand_id') ;
//        (I('is_on_sale') !== '') && $where = "$where and is_on_sale = ".I('is_on_sale') ;
//        $cat_id = I('cat_id');
//        // 关键词搜索
//        $key_word = I('key_word') ? trim(I('key_word')) : '';
//        if($key_word)
//        {
//            $where = "$where and (goods_name like '%$key_word%' or goods_sn like '%$key_word%')" ;
//        }
//
//        if($cat_id > 0)
//        {
//            $grandson_ids = getCatGrandson($cat_id);
//            $where .= " and cat_id in(".  implode(',', $grandson_ids).") "; // 初始化搜索条件
//        }
//        if(is_supplier()){
//            $where .= " and admin_id ='".session('admin_id')."'";
//        }



        $model = M('goods_check');
        $count = $model->where($where)->count();
        $Page  = new AjaxPage($count,10);
        /**  搜索条件下 分页赋值
        foreach($condition as $key=>$val) {
        $Page->parameter[$key]   =   urlencode($val);
        }
         */
        $show = $Page -> show();
        $prefix = C('DB_PREFIX');
//        $order_str = "`{$_POST['orderby1']}` {$_POST['orderby2']}";
        $goodsList = $model->join("LEFT JOIN `".$prefix."goods`   ON ".$prefix."goods_check.goods_id = ".$prefix."goods.goods_id  ") -> where($where)->order($order_str)->limit($Page->firstRow.','.$Page->listRows)->select();
//        dd($goodsList);
//        $catList = D('goods_category')->select();
//        $catList = convert_arr_key($catList, 'id');
//        $this -> assign('catList',$catList);
        $this -> assign('goodsList',$goodsList);
        $this -> assign('page',$show);// 赋值分页输出
        $this -> display();
    }


    /**
     * 供应商交易记录
     *
     **/
    public function tradingRecord(){
        $userId = I('user_id');
        $thirtyDays= date('Y/m/d',(time()-30*60*60*24));//30天前
        $end = date('Y/m/d',strtotime('+1 days'));
        $sevenDays = date('Y/m/d',(time()-7*60*60*24));//7天前
        $this -> assign('thirtyDays',$thirtyDays);
        $this -> assign('end',$end);
        $this -> assign('sevenDays',$sevenDays);
        $this -> assign('userId',$userId);
        $this -> display();
    }

    public function ajaxtradingRecord(){
        $userId = I('userId'); //用户id
        $begin = strtotime(I('begin')); //开始时间
        $end = strtotime(I('end')); // 结束时间
        $type =   I('type'); //订单类型
        $prefix = C('DB_PREFIX');
        $order = $prefix.'order.add_time DESC';
        // 搜索条件
        $condition = array();
        if($begin && $end){
            $condition[$prefix.'order.add_time'] = array('between',"$begin,$end");
        }
        $condition[$prefix.'order_goods.admin_id'] = is_supplier() ? session('admin_id') : '0';
        !empty($userId) ? $condition[$prefix.'order.user_id'] = $userId   : '' ;
        switch ($type) {
            case 'dealing': //进行中
                $condition[$prefix.'order.order_status'] = array('in','0,1');
                break;
            case 'refund': //退款
                $condition[$prefix.'order_goods.is_send'] = "3";
                break;
            case 'succeed': // 已完成
                $condition[$prefix.'order.order_status'] = array('in','2,4');
                break;
            case 'cancel':  // 已取消
                $condition[$prefix.'order.order_status'] = '3';
                break;
        }
        $count = M('order_goods')->join("LEFT JOIN ".$prefix."order ON ".$prefix."order_goods.order_id = ".$prefix."order.order_id ") -> where($condition)->count();
        $limit = 10;
        $Page  = new \Admin\Common\AjaxPage($count,$limit);
        //  搜索条件下 分页赋值
        foreach($condition as $key=>$val) {
            $Page->parameter[$key]   =  urlencode($val);
        }
        $show = $Page -> show();
        // ->field("".$prefix."order_goods.*,".$prefix."order.order_sn,".$prefix."order.add_time,".$prefix."order.mobile")
        $orderList = M('order_goods')->join("LEFT JOIN ".$prefix."order ON ".$prefix."order_goods.order_id = ".$prefix."order.order_id ")->limit($Page->firstRow.','.$Page->listRows) -> where($condition)->order($order)->select();
        // $orderList[$keys] = setBtnOrderStatus( $items );
        foreach($orderList as $key=>$item){
            $orderList[$key] = setBtnOrderStatus( $item );
            $orderList[$key]['total_amount'] = ($item['goods_num'] * $item['member_goods_price']) + $item['goods_postage'];
        }
//        dd($orderList);

        $this -> assign('orderList',$orderList);
        $this -> assign('page',$show);// 赋值分页输出
        $this -> display();
    }

    /**
     * 供应商数据复制
     *
     **/
    public function copyGoodsData(){
        $goodId = I('goodId');
        $where['goods_id'] = $goodId;
        $data = M('goods')->where($where)->find();
        $goodsPriceList  = M('spec_goods_price')->where($where)->select();
        $goodImages = M('goods_images')->where($where)->select();
        
        if(!empty($data['goods_content'])){
            $str = htmlspecialchars_decode($data['goods_content']);
            $pattern="/<[img|IMG].*?src=[\'|\"](.*?(?:[\.gif|\.jpg]))[\'|\"].*?[\/]?>/";
            preg_match_all($pattern,$str,$match);
            foreach($match[1] as $goodsContItem){
                if(strstr($goodsContItem,'http')){
                    continue;
                }else{
                    $pathInfo = pathinfo($goodsContItem);
                    $ContSrand = mt_rand().'.'.$pathInfo['extension'];
                    $ContOldImg = $_SERVER['DOCUMENT_ROOT'].$goodsContItem;
                    $ContImg = $pathInfo['dirname'].'/'.$ContSrand;
                    $ContNewImg = $_SERVER['DOCUMENT_ROOT'].$ContImg;
                    $res = copy($ContOldImg,$ContNewImg);
                    if($res){
                        $str = str_replace($goodsContItem,$ContImg,$str);
                    }
                }

            }
            $data['goods_content'] = htmlspecialchars($str);
        }

        unset($data['goods_id']);
        $data['on_time'] = '';
        $data['is_on_sale'] = 0;
        $data['is_hot'] = 0;
        $data['is_recommend'] = 0;
        $data['is_new'] = 0;
        if(!empty($data['original_img'])){
            $file =  pathinfo($data['original_img']);
            $srand = mt_rand().'.'.$file['extension'];
            $oldImg = $_SERVER['DOCUMENT_ROOT'].$data['original_img'];
            $urlImg = $file['dirname']."/".$srand;
            $newImg = $_SERVER['DOCUMENT_ROOT'].$urlImg;
            $ImgRes  = copy($oldImg,$newImg);
            if($ImgRes){
                $data['original_img'] = $urlImg;
            }
        }
        $info = M('goods')->add($data);
        if(!empty($goodsPriceList) && $info){
            foreach($goodsPriceList as $item){
                $item['goods_id'] = $info;
                M('spec_goods_price')->add($item);
            }
        }

        if(!empty($goodImages)){
            foreach($goodImages as $imgItem){
                unset($imgItem['img_id']);
                $fileImages = pathinfo($imgItem['image_url']);
                $srandImage = mt_rand().'.'.$fileImages['extension'];
                $oldImages = $_SERVER['DOCUMENT_ROOT'].$imgItem['image_url'];
                $urlImages = $fileImages['dirname'].'/'.$srandImage;
                $newImages = $_SERVER['DOCUMENT_ROOT'].$urlImages;
                $ImagesRes  = copy($oldImages,$newImages);
                if($ImagesRes){
                    $imgItem['image_url'] = $urlImages;
                    $imgItem['goods_id'] = $info;
                    M('goods_images')->add($imgItem);
                }
            }
        }
        if($info){
            exit(json_encode(callback(true,'复制成功')));
        }
        exit(json_encode(callback(false,'复制失败')));

    }

  



}