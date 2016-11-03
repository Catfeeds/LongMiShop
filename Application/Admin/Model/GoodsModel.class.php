<?php
namespace Admin\Model;
use Think\Model;
class GoodsModel extends Model
{
    protected $patchValidate = true; // 系统支持数据的批量验证功能，
    /**
     *
     * self::EXISTS_VALIDATE 或者0 存在字段就验证（默认）
     * self::MUST_VALIDATE 或者1 必须验证
     * self::VALUE_VALIDATE或者2 值不为空的时候验证
     *
     *
     * self::MODEL_INSERT或者1新增数据时候验证
     * self::MODEL_UPDATE或者2编辑数据时候验证
     * self::MODEL_BOTH或者3全部情况下验证（默认）
     */
    protected $_validate = array(
        array('goods_name', 'require', '商品名称必须填写！', 1, '', 3),

        array('goods_name', '0,30', '商品名字请小于30个字符！', 3, 'length'),
        //array('cat_id','require','商品分类必须填写！',1 ,'',3),
        array('cat_id', '0', '商品分类必须填写。', 1, 'notequal', 3),
        array('goods_sn', '', '商品货号重复！', 2, 'unique', 1),
        array('shop_price', '/\d{1,10}(\.\d{1,2})?$/', '本店售价格式不对。', 2, 'regex'),
        array('member_price', '/\d{1,10}(\.\d{1,2})?$/', '会员价格式不对。', 2, 'regex'),
        array('market_price', '/\d{1,10}(\.\d{1,2})?$/', '市场价格式不对。', 2, 'regex'), // currency
        array('weight', '/\d{1,10}(\.\d{1,2})?$/', '重量格式不对。', 2, 'regex'),
    );


    /**
     * 供应商设置
     * @param int $goods_id 商品id
     */
    public function adminSave($goods_id)
    {
        $admin_id = session('admin_id');
        if (is_supplier()) {
            M('goods')->where("goods_id = $goods_id ")->save(array("admin_id" => $admin_id)); // 根据条件更新记录
            $data = array(
               'goods_id'=> $goods_id,
               'admin_id'=>$admin_id,
               'check'=>0,
                'create_time'=>time(),
            );
            M('goods_check')->add($data);
        }
    }

    /**
     * 后置操作方法
     * 自定义的一个函数 用于数据保存后做的相应处理操作, 使用时手动调用
     * @param int $goods_id 商品id
     */
    public function afterSave($goods_id)
    {

        // 商品货号
        $goods_sn = "LM" . str_pad($goods_id, 7, "0", STR_PAD_LEFT);
        // 商品图片相册  图册
        if (count($_POST['goods_images']) > 1) {
//            array_unshift($_POST['goods_images'],$_POST['original_img']); // 商品原始图 默认为 相册第一张图片
//            array_pop($_POST['goods_images']); // 弹出最后一个
            $goodsImagesArr = M('GoodsImages')->where("goods_id = $goods_id")->getField('img_id,image_url'); // 查出所有已经存在的图片

            // 删除图片
            foreach ($goodsImagesArr as $key => $val) {
                if (!in_array($val, $_POST['goods_images']))
                    M('GoodsImages')->where("img_id = {$key}")->delete(); // 删除所有状态为0的用户数据
            }
            // 添加图片
            foreach ($_POST['goods_images'] as $key => $val) {
                if ($val == null) continue;
                if (!in_array($val, $goodsImagesArr)) {
                    $data = array(
                        'goods_id'  => $goods_id,
                        'image_url' => $val,
                    );
                    M("GoodsImages")->data($data)->add();; // 实例化User对象
                }
                $where = array(
                    'goods_id'  => $goods_id,
                    'image_url' => $val,
                );
                $data = array(
                    "sort" => $_POST['goods_images_sort'][$key],
                );
                M('GoodsImages')->where($where)->save($data);
            }
        }


        $model = new Model();
        try {
            $model->startTrans();
            if ($_POST['isChangeOption'] == 1) {
                $spec_title = $_POST['spec_title'];
                if (!empty($spec_title)) {
                    $condition = array(
                        'goods_id' => $goods_id,
                        'name'     => array('not in', implode(',', $spec_title)),
                    );
                    $specList = M("spec")->where($condition)->field("id")->select();
                    if (!empty($specList)) {
                        foreach ($specList as $specItem) {
                            M("spec_item")->where(array('spec_id' => $specItem['id']))->delete();
                        }
                        M("spec")->where(array('goods_id' => $goods_id))->delete();
                    }

                    foreach ($spec_title as $specTitleKey => $specTitleItem) {
                        $condition2 = array(
                            'goods_id' => $goods_id,
                            "name"     => $specTitleItem
                        );
                        $specInfo = findDataWithCondition("spec", $condition2, "id");
                        if (empty($specInfo)) {
                            $specId = M('spec')->add($condition2);
                        } else {
                            $specId = $specInfo['id'];
                        }
                        if (!empty($specId)) {
                            $specItemTitle = $_POST['spec_item_title_' . $specTitleKey];
                            if (!empty($specItemTitle)) {
                                $condition4 = array(
                                    'spec_id' => $specId,
                                    'item'    => array('not in', implode(',', $specItemTitle)),
                                );
                                M('spec_item')->where($condition4)->delete();
                                foreach ($specItemTitle as $specItemTitleKey => $specItemTitleItem) {
                                    $condition3 = array(
                                        'spec_id' => $specId,
                                        "item"    => $specItemTitleItem
                                    );
                                    $specItemInfo = findDataWithCondition("spec_item", $condition3, "id");
                                    if (empty($specItemInfo)) {
                                        M('spec_item')->add($condition3);
                                    }
                                }

                            }
                        }
                    }
                } else {
                    $specList = M("spec")->where(array('goods_id' => $goods_id))->field("id")->select();
                    if (!empty($specList)) {
                        foreach ($specList as $specItem) {
                            M("spec_item")->where(array('spec_id' => $specItem['id']))->delete();
                        }
                        M("spec")->where(array('goods_id' => $goods_id))->delete();
                        M("SpecGoodsPrice")->where(array('goods_id' => $goods_id))->delete();
                    }
                }
            }
            if (!empty($_POST['spec_title']) && !empty($_POST['option_ids'])) {
                $specGoodsPrice = M("SpecGoodsPrice"); // 实例化 商品规格 价格对象
                $specGoodsPrice->where('goods_id = ' . $goods_id)->delete(); // 删除原有的价格规格对象
                $spec_title = $_POST['spec_title'];
                $option_ids = $_POST['option_ids'];
                $spec_id = $_POST['spec_id'];
                $dataList = array();
                foreach ($option_ids as $k => $v) {

                    $tempArray = array_unique(explode('_', $v));
                    $key = array();
                    $name = "";
                    $price = $_POST["option_price_" . $v][0];
                    $store_count = $_POST["option_store_count_" . $v][0];
                    $weight = $_POST["option_weight_" . $v][0];
                    foreach ($spec_id as $specKey => $specItem) {
                        $lock = 0;
                        foreach ($tempArray as $tempItem) {
                            if ($lock == 0) {
                                foreach ($_POST["spec_item_id_" . $specItem] as $specItemIdKey => $specItemIdItem) {
                                    if ($specItemIdItem == $tempItem) {
                                        $condition6 = array(
                                            "goods_id" => $goods_id,
                                            "name" =>$spec_title[$specItem]
                                        );
                                        $specInfoId = findDataWithCondition("spec",$condition6 , "id");
                                        $condition5 = array(
                                            "spec_id" => $specInfoId['id'],
                                            "item" =>$_POST['spec_item_title_' . $specItem][$specItemIdKey]
                                        );
                                        $specItemInfoId = findDataWithCondition("spec_item",$condition5 , "id");
                                        $key[] = $specItemInfoId['id'];
                                        $name .= $spec_title[$specItem] . ":" . $_POST['spec_item_title_' . $specItem][$specItemIdKey] . " ";
                                        $lock = 1;
                                    }
                                }
                            }
                        }
                    }
                    sort($key);
                    $key = implode('_', $key);
                    $price = trim($price);
                    $store_count = trim($store_count);
                    $weight = trim($weight);
                    $dataList[] = array(
                        'goods_id'    => $goods_id,
                        'key'         => $key,
                        'key_name'    => $name,
                        'price'       => $price,
                        'store_count' => $store_count,
                        'weight'      => $weight,
                    );
                }
                $specGoodsPrice->addAll($dataList);
            }
            $model->commit();
        } catch (\Exception $e) {
            $model->rollback();
            setLogResult($e->getMessage(), "规格报错", "goods");
        }


        // 商品规格价钱处理
//        if($_POST['item'])
//        {
//            $spec = M('Spec')->getField('id,name'); // 规格表
//            $specItem = M('SpecItem')->getField('id,item');//规格项
//
//            $specGoodsPrice = M("SpecGoodsPrice"); // 实例化 商品规格 价格对象
//            $specGoodsPrice->where('goods_id = '.$goods_id)->delete(); // 删除原有的价格规格对象
//            foreach($_POST['item'] as $k => $v)
//            {
//                // 批量添加数据
//                $v['price'] = trim($v['price']);
//                $store_count = $v['store_count'] = trim($v['store_count']); // 记录商品总库存
//                $v['sku'] = trim($v['sku']);
//                $dataList[] = array('goods_id'=>$goods_id,'key'=>$k,'key_name'=>$v['key_name'],'price'=>$v['price'],'store_count'=>$v['store_count'],'sku'=>$v['sku']);
//            }
//            $specGoodsPrice->addAll($dataList);
//            //M('Goods')->where("goods_id = 1")->save(array('store_count'=>10)); // 修改总库存为各种规格的库存相加
//        }

        // 商品规格图片处理
//        if($_POST['item_img'])
//        {
//            M('SpecImage')->where("goods_id = $goods_id")->delete(); // 把原来是删除再重新插入
//            foreach ($_POST['item_img'] as $key => $val)
//            {
//                M('SpecImage')->data(array('goods_id'=>$goods_id ,'spec_image_id'=>$key,'src'=>$val))->add();
//            }
//        }
        refresh_stock($goods_id); // 刷新商品库存
    }
}
