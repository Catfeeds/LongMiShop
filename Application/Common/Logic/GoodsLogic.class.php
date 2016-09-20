<?php
namespace Common\Logic;

use Common\Logic\Base\BaseLogic;

class GoodsLogic extends BaseLogic
{

    public function __construct()
    {

        parent::_initialize();

    }


    /**
     * 获取 商品列表页帅选品牌
     * @param type $id
     * return array(status)  这里状态一般都为1 result 不是返回数据 就是空
     * $mode 0  返回数组形式  1 直接返回result
     */
    public function get_filter_brand($goods_id_arr,$filter_param,$action,$mode = 0)
    {
        if(!empty($filter_param['brand_id']))
            return array();

        $goods_id_str = implode(',', $goods_id_arr);
        $goods_id_str = $goods_id_str ? $goods_id_str : '0';
        $list_brand = M('brand')->where("id in(select brand_id from ".C('DB_PREFIX')."goods where brand_id > 0 and goods_id in($goods_id_str))")->limit('30')->select();  //where("goods_id in($goods_id_str)")->select();

        foreach($list_brand as $k => $v)
        {
            // 帅选参数
            $filter_param['brand_id'] = $v['id'];
            $list_brand[$k]['href'] = urldecode(U("Goods/$action",$filter_param,''));
        }
        if($mode == 1) return $list_brand;
        return array('status'=>1,'msg'=>'','result'=>$list_brand);
    }


    /**
     * 获取 商品列表页帅选规格
     * @param type $id
     * return array(status)  这里状态一般都为1 result 不是返回数据 就是空
     * $mode 0  返回数组形式  1 直接返回result
     */
    public function get_filter_spec($goods_id_arr,$filter_param,$action,$mode = 0)
    {
        $goods_id_str = implode(',', $goods_id_arr);
        $goods_id_str = $goods_id_str ? $goods_id_str : '0';
        $spec_key = M('spec_goods_price')->query("select group_concat(`key` separator  '_') as `key` from __PREFIX__spec_goods_price where goods_id in($goods_id_str)");  //where("goods_id in($goods_id_str)")->select();
        $spec_key = explode('_', $spec_key[0]['key']);
        $spec_key = array_unique($spec_key);
        $spec_key = array_filter($spec_key);

        if(empty($spec_key))
        {
            if($mode == 1) return  array();
            return array('status'=>1,'msg'=>'','result'=>array());
        }
        $spec = M('spec')->getField('id,name');
        $spec_item = M('spec_item')->getField('id,spec_id,item');

        $list_spec = array();
        $old_spec = $filter_param['spec'];
        foreach($spec_key as $k => $v)
        {
            if(strpos($old_spec, $spec_item[$v]['spec_id'].'_') === 0 || strpos($old_spec, '@'.$spec_item[$v]['spec_id'].'_'))
                continue;
            $list_spec[$spec_item[$v]['spec_id']]['spec_id'] = $spec_item[$v]['spec_id'];
            $list_spec[$spec_item[$v]['spec_id']]['name'] = $spec[$spec_item[$v]['spec_id']];
            //$list_spec[$spec_item[$v]['spec_id']]['item'][$v] = $spec_item[$v]['item'];

            // 帅选参数
            if(!empty($old_spec))
                $filter_param['spec'] = $old_spec.'@'.$spec_item[$v]['spec_id'].'_'.$v;
            else
                $filter_param['spec'] = $spec_item[$v]['spec_id'].'_'.$v;
            $list_spec[$spec_item[$v]['spec_id']]['item'][] = array('key'=>$spec_item[$v]['spec_id'],'val'=>$v,'item'=>$spec_item[$v]['item'],'href'=>urldecode(U("Goods/$action",$filter_param,'')));
        }

        if($mode == 1) return $list_spec;
        return array('status'=>1,'msg'=>'','result'=>$list_spec);
    }

    /**
     * 获取商品列表页帅选属性
     * @param type $id
     * $mode 0  返回数组形式  1 直接返回result
     */
    public function get_filter_attr($goods_id_arr = array(),$filter_param,$action, $mode = 0)
    {
        $goods_id_str = implode(',', $goods_id_arr);
        $goods_id_str = $goods_id_str ? $goods_id_str : '0';
        $goods_attr = M('goods_attr')->where("goods_id in($goods_id_str) and attr_value != ''")->select();
        // $goods_attr = M('goods_attr')->where("attr_value != ''")->select();
        $goods_attribute = M('goods_attribute')->getField('attr_id,attr_name,attr_index');
        if(empty($goods_attr))
        {
            if($mode == 1) return  array();
            return array('status'=>1,'msg'=>'','result'=>array());
        }
        $list_attr = $attr_value_arr = array();
        $old_attr = $filter_param['attr'];
        foreach($goods_attr as $k => $v)
        {
            // 存在的帅选不再显示
            if(strpos($old_attr, $v['attr_id'].'_') === 0 || strpos($old_attr, '@'. $v['attr_id'].'_'))
                continue;
            if($goods_attribute[$v['attr_id']]['attr_index'] == 0)
                continue;
            $v['attr_value'] = trim($v['attr_value']);
            // 如果同一个属性id 的属性值存储过了 就不再存贮
            if(in_array($v['attr_id'].'_'.$v['attr_value'],$attr_value_arr[$v['attr_id']]))
                continue;
            $attr_value_arr[$v['attr_id']][] = $v['attr_id'].'_'.$v['attr_value'];

            $list_attr[$v['attr_id']]['attr_id'] = $v['attr_id'];
            $list_attr[$v['attr_id']]['attr_name'] = $goods_attribute[$v['attr_id']]['attr_name'];

            // 帅选参数
            if(!empty($old_attr))
                $filter_param['attr'] = $old_attr.'@'.$v['attr_id'].'_'.$v['attr_value'];
            else
                $filter_param['attr'] = $v['attr_id'].'_'.$v['attr_value'];

            $list_attr[$v['attr_id']]['attr_value'][] = array('key'=>$v['attr_id'],'val'=>$v['attr_value'],'attr_value'=>$v['attr_value'],'href'=>urldecode(U("Goods/$action",$filter_param,'')));
            //unset($filter_param['attr_id_'.$v['attr_id']]);
        }
        if($mode == 1) return  $list_attr;
        return array('status'=>1,'msg'=>'','result'=>$list_attr);
    }

    /**
     * $spec_item_id 参数类似于以下
     * Array
    5 => Array( // 网络
    [0] => 11  // 4G
    [1] => 12 // 3G
    )
    6 => Array( // 内存
    [0] => 14 // 8G
    ) 其查询类似于  where 网络 in (4G,3G) and 内存 in(G)
     * @param type $spec_item_id 前台列表搜索页面 提交过来的  规格id 和规格项id
     * $mode 0  返回数组形式  1 直接返回result
     */
    public function get_spec_item_goods_id($spec_item_id,$mode = 0)
    {
        /** 最后组装的 sql语句
        select * from (
        select a.*,concat('_',`key`,'_') as key2 from `tp_spec_goods_price` as a
        ) b
        where (key2 like '%_7_%' or key2  like '%_8_%') and (key2  like '%_18_%')and (key2  like '%_24_%')
         */
        $where = " where ( 1 = 1 )";
        foreach ($spec_item_id as $k => $v)
        {
            foreach($v as $k2 => $v2)
            {
                $like[] = " key2 like '%\_{$v2}\_%' ";
            }
            $where .= " and (".  implode('or', $like).")";
            $like = array();
        }

        $sql = "select * from (
                  select *,concat('_',`key`,'_') as key2 from __PREFIX__spec_goods_price as a
              ) b  $where";
        $Model  = new \Think\Model();
        $result = $Model->query($sql);
        $goods_id_arr = get_arr_column($result, 'goods_id');  // 只获取商品id 那一列
        if($mode == 1) return array_unique($goods_id_arr);
        return array('status'=>1,'msg'=>'','result'=>array_unique($goods_id_arr));
    }

    /**
     * 提交过了的值类似于
    Array
    (
    [59] => Array 外观样式
    [0] => 翻盖
    [1] => 滑盖
    [75] => Array 天线位置:
    [0] => 内置
    )
     * 根据提交过了的商品属性 找出对应的商品id
     * @param type $attr_id
     * $mode 0  返回数组形式  1 直接返回result
     */
    public function get_attr_goods_id($attr_id, $mode = 0)
    {
        // select * from `tp_goods_attr` where attr_id = 185 and (attr_value in('白色','黑色'))
        $Model  = new \Think\Model();
        foreach($attr_id as $key => $val)
        {
            $sql = "select goods_id from __PREFIX__goods_attr  where attr_id = $key and attr_value in ('".implode("','",$val)."')";
            $result = $Model->query($sql);
            $tmp_attr[] = get_arr_column($result, 'goods_id');  // 只获取商品id 那一列
        }

        $goods_id_attr = $tmp_attr[0];
        foreach($tmp_attr as  $key => $val)
        {
            $goods_id_attr = array_intersect($goods_id_attr,$val);
        }
        if($mode == 1) return $goods_id_attr;
        return array('status'=>1,'msg'=>'','result'=>$goods_id_attr);
    }

    /**
     * 获取某个商品的评论统计
     * 全部评论数  好评数 中评数  差评数
     */
    public function commentStatistics($goods_id)
    {
        $c1 = M('Comment')->where("is_show = 1 and  goods_id = $goods_id and parent_id = 0 and  ceil((deliver_rank + goods_rank + service_rank) / 3) in(4,5)")->count(); // 好评
        $c2 = M('Comment')->where("is_show = 1 and  goods_id = $goods_id and parent_id = 0 and  ceil((deliver_rank + goods_rank + service_rank) / 3) in(3)")->count(); // 中评
        $c3 = M('Comment')->where("is_show = 1 and  goods_id = $goods_id and parent_id = 0 and  ceil((deliver_rank + goods_rank + service_rank) / 3) in(1,2)")->count(); // 差评

        $c0 = $c1 + $c2 + $c3; // 所有评论
        $rate1 = ceil($c1 / ($c1+$c2+$c3) * 100); // 好评率
        $rate2 = ceil($c2 / ($c1+$c2+$c3) * 100); // 中评率
        //$rate3 = 100 - ($rate1 + $rate2); // 差评率
        $rate3 = ceil($c3 / ($c1+$c2+$c3) * 100); // 差评率

        return array('c0'=>$c0, 'c1' =>$c1,'c2' =>$c2,'c3' =>$c3,'rate1'=>$rate1,'rate2'=>$rate2,'rate3'=>$rate3);
    }

    /**
     * 商品收藏
     * @param type $user_id 用户id
     * @param type $goods_id 商品id
     * @return type
     */
    public function collect_goods($user_id,$goods_id)
    {
        if(!is_numeric($user_id) || $user_id <= 0) return array('status'=>-1,'msg'=>'必须登录后才能收藏','result'=>array());
        //$count = M('Goods')->where("goods_id = $goods_id")->count();
        //if($count == 0)  return array('status'=>-2,'msg'=>'收藏的商品不存在','result'=>array());
        $count = M('GoodsCollect')->where("user_id = $user_id and goods_id = $goods_id")->count();
        if($count > 0)  return array('status'=>-3,'msg'=>'商品已收藏','result'=>array());
        M('GoodsCollect')->add(array('goods_id'=>$goods_id,'user_id'=>$user_id, 'add_time'=>time()));
        return array('status'=>1,'msg'=>'收藏成功!请到个人中心查看','result'=>array());
    }

    /**
     * 获取商品规格
     */
    public function get_spec($goods_id){
        //商品规格 价钱 库存表 找出 所有 规格项id
        $keys = M('SpecGoodsPrice')->where("goods_id = $goods_id")->getField("GROUP_CONCAT(`key` SEPARATOR '_') ");
        $filter_spec = array();
        if($keys)
        {
            $specImage =  M('SpecImage')->where("goods_id = $goods_id and src != '' ")->getField("spec_image_id,src");// 规格对应的 图片表， 例如颜色
            $keys = str_replace('_',',',$keys);
            $sql  = "SELECT a.name,a.order,b.* FROM __PREFIX__spec AS a INNER JOIN __PREFIX__spec_item AS b ON a.id = b.spec_id WHERE b.id IN($keys) ORDER BY a.order";
            $filter_spec2 = M()->query($sql);
            foreach($filter_spec2 as $key => $val)
            {
                $filter_spec[$val['name']][] = array(
                    'item_id'=> $val['id'],
                    'item'=> $val['item'],
                    'src'=>$specImage[$val['id']],
                );
            }
        }
        return $filter_spec;
    }

    /**
     * 获取商品规格
     */
    public function getSpec($goods_id){
        //商品规格 价钱 库存表 找出 所有 规格项id
        $keys = M('SpecGoodsPrice')->where("goods_id = '$goods_id'")->getField("GROUP_CONCAT(`key` SEPARATOR '_') ");
        $filter_spec = array();
        if($keys)
        {
            $specImage =  M('SpecImage')->where("goods_id = $goods_id and src != '' ")->getField("spec_image_id,src");// 规格对应的 图片表， 例如颜色
            $keys = str_replace('_',',',$keys);
            $sql  = "SELECT a.name,a.order,b.* FROM __PREFIX__spec AS a INNER JOIN __PREFIX__spec_item AS b ON a.id = b.spec_id WHERE b.id IN($keys) ORDER BY b.id";
            $filter_spec2 = M()->query($sql);
            foreach($filter_spec2 as $key => $val)
            {
                $filter_spec[$val['name']][] = array(
                    'item_id'=> $val['id'],
                    'item'=> $val['item'],
                    'src'=>$specImage[$val['id']],
                );
            }
        }
        return $filter_spec;
    }


    /**
     * 获取相关分类
     */
    public function get_siblings_cate($cat_id){
        if(empty($cat_id))
            return array();
        $cate_info = M('goods_category')->where("id=$cat_id")->find();
        $siblings_cate = M('goods_category')->where("id!=$cat_id and parent_id=".$cate_info['parent_id'])->select();
        return empty($siblings_cate) ? array() : $siblings_cate;
    }

    /**
     * 看了又看
     */
    public function get_look_see($goods){
        return M('goods')->where("goods_id !=".$goods['goods_id']." and cat_id!=".$goods['cat_id']." and is_on_sale = 1")->limit(12)->select();
    }


    /**
     * 帅选的价格期间
     * @param type $goods_id_str 帅选的分类id
     * @param type $c   分几段 默认分5 段
     */
    function get_filter_price($goods_id_arr,$filter_param,$action,$c=5)
    {

        if(!empty($filter_param['price']))
            return array();

        $goods_id_str = implode(',', $goods_id_arr);
        $goods_id_str = $goods_id_str ? $goods_id_str : '0';
        $priceList = M('goods')->where("goods_id in ($goods_id_str)")->getField('shop_price',true);  //where("goods_id in($goods_id_str)")->select();

        rsort($priceList);
        $max_price = (int)$priceList[0];

        $psize = ceil($max_price / $c); // 每一段累积的价钱
        $parr = array();
        for($i = 0; $i < $c; $i++)
        {
            $start = $i * $psize;
            $end = $start + $psize;

            // 如果没有这个价格范围的商品则不列出来
            $in = false;
            foreach($priceList as $k => $v)
            {
                if($v > $start && $v < $end)
                    $in = true;
            }
            if($in == false)
                continue;

            $filter_param['price'] = "{$start}-{$end}";
            if($i == 0)
                $parr[] = array('value'=>"{$end}以下",'href'=>urldecode(U("Goods/$action",$filter_param,'')));
            elseif($i == ($c-1))
                $parr[] = array('value'=>"{$end}以上",'href'=>urldecode(U("Goods/$action",$filter_param,'')));
            else
                $parr[] = array('value'=>"{$start}-{$end}",'href'=>urldecode(U("Goods/$action",$filter_param,'')));
        }
        return $parr;
    }
    /**
     * 帅选条件菜单
     */
    function get_filter_menu($filter_param,$action)
    {
        $menu_list = array();
        // 品牌
        if(!empty($filter_param['brand_id']))
        {
            $brand_list = M('brand')->getField('id,name');
            $brand_id = explode('_', $filter_param['brand_id']);
            $brand['text'] = "品牌:";
            foreach ($brand_id as $k => $v)
            {
                $brand['text'] .= $brand_list[$v].',';
            }
            $brand['text'] = substr($brand['text'], 0, -1);
            $tmp = $filter_param;
            unset($tmp['brand_id']); // 当前的参数不再带入
            $brand['href'] = urldecode(U("Goods/$action",$tmp,''));
            $menu_list[] = $brand;
        }
        // 规格
        if(!empty($filter_param['spec']))
        {
            $spec = M('spec')->getField('id,name');
            $spec_item = M('spec_item')->getField('id,item');
            $spec_group = explode('@',$filter_param['spec']);
            foreach ($spec_group as $k => $v)
            {
                $spec_group2 = explode('_',$v);
                $spec_menu['text'] = $spec[$spec_group2[0]].':';
                array_shift($spec_group2); // 弹出第一个规格名称
                foreach($spec_group2 as $k2 => $v2)
                {
                    $spec_menu['text'] .= $spec_item[$v2].',';
                }
                $spec_menu['text'] = substr($spec_menu['text'], 0, -1);

                $tmp = $spec_group;
                $tmp2 = $filter_param;
                unset($tmp[$k]);
                $tmp2['spec'] = implode('@', $tmp); // 当前的参数不再带入
                $spec_menu['href'] = urldecode(U("Goods/$action",$tmp2,''));
                $menu_list[] = $spec_menu;
            }
        }
        // 属性
        if(!empty($filter_param['attr']))
        {
            $goods_attribute = M('goods_attribute')->getField('attr_id,attr_name');
            $attr_group = explode('@',$filter_param['attr']);
            foreach ($attr_group as $k => $v)
            {
                $attr_group2 = explode('_',$v);
                $attr_menu['text'] = $goods_attribute[$attr_group2[0]].':';
                array_shift($attr_group2); // 弹出第一个规格名称
                foreach($attr_group2 as $k2 => $v2)
                {
                    $attr_menu['text'] .= $v2.',';
                }
                $attr_menu['text'] = substr($attr_menu['text'], 0, -1);

                $tmp = $attr_group;
                $tmp2 = $filter_param;
                unset($tmp[$k]);
                $tmp2['attr'] = implode('@', $tmp); // 当前的参数不再带入
                $attr_menu['href'] = urldecode(U("Goods/$action",$tmp2,''));
                $menu_list[] = $attr_menu;
            }
        }
        // 价格
        if(!empty($filter_param['price']))
        {
            $price_menu['text'] = "价格:".$filter_param['price'];
            unset($filter_param['price']);
            $price_menu['href'] = urldecode(U("Goods/$action",$filter_param,''));
            $menu_list[] = $price_menu;
        }

        return $menu_list;
    }
    /**
     * 传入当前分类 如果当前是 2级 找一级
     * 如果当前是 3级 找2 级 和 一级
     * @param type $goodsCate
     */
    function get_goods_cate(&$goodsCate)
    {
        if(empty($goodsCate)) return array();
        $cateAll = get_goods_category_tree();
        if($goodsCate['level']==1)
        {
            $cateArr = $cateAll[$goodsCate['id']]['tmenu'];
            $goodsCate['parent_name'] = $goodsCate['name'];
            $goodsCate['select_id'] = 0;
        }elseif($goodsCate['level'] == 2)
        {
            $cateArr = $cateAll[$goodsCate['parent_id']]['tmenu'];
            $goodsCate['parent_name'] = $cateAll[$goodsCate['parent_id']]['name'];//顶级分类名称
            $goodsCate['open_id'] = $goodsCate['id'];//默认展开分类
            $goodsCate['select_id'] = 0;
        }else{
            $parent = M('GoodsCategory')->where("id =".$goodsCate['parent_id'])->order('`sort_order` desc')->find();//父类
            $cateArr = $cateAll[$parent['parent_id']]['tmenu'];
            $goodsCate['parent_name'] = $cateAll[$parent['parent_id']]['name'];//顶级分类名称
            $goodsCate['open_id'] = $parent['id'];
            $goodsCate['select_id'] = $goodsCate['id'];//默认选中分类
        }
        return $cateArr;
    }

    /**
     *  * 根据品牌或者价格条件帅选出 商品id
     * @param type $brand_id 帅选品牌id
     * @param type $price 帅选价格
     */
    function getGoodsIdByBrandPrice($brand_id,$price)
    {
        if(empty($brand_id) && empty($price))
            return array();

        $where = " 1 = 1 ";
        if($brand_id) // 品牌查询
        {
            $brand_id_arr = explode('_',$brand_id);
            $where .= " and brand_id in(".  implode(',', $brand_id_arr).")";
        }
        if($price)// 价格查询
        {
            $price = explode('-',$price);
            $where .= " and shop_price >= {$price[0]} and  shop_price <= {$price[1]} ";
        }
        $arr = M('goods')->where($where)->getField('goods_id',true);
        return $arr ? $arr : array();
    }
    /**
     * 根据规格 查找 商品id
     * @param type $spec 规格
     */
    function getGoodsIdBySpec($spec)
    {
        if(empty($spec))
            return array();

        $spec_group = explode('@',$spec);
        $where = " where 1=1 ";
        foreach ($spec_group as $k => $v)
        {
            $spec_group2 = explode('_',$v);
            array_shift($spec_group2);
            $like = array();
            foreach ($spec_group2 as $k2 => $v2)
            {
                $like[] = " key2  like '%\_$v2\_%' ";
            }
            $where .=  " and (".  implode('or', $like).") ";
        }
        //    $arr = M('spec_goods_price')->where($where)->getField('goods_id',true);
        $sql = "select * from (
                  select *,concat('_',`key`,'_') as key2 from __PREFIX__spec_goods_price as a
              ) b  $where";
        $Model  = new \Think\Model();
        $result = $Model->query($sql);
        $arr = get_arr_column($result, 'goods_id');  // 只获取商品id 那一列
        return ($arr ? $arr : array_unique($arr));
    }

    /**
     * 根据属性 查找 商品id
     * @param type $attr 属性
     * attr=
     * 59_直板_翻盖
     * 80_BT4.0_BT4.1
     */
    function getGoodsIdByAttr($attr)
    {
        if(empty($attr))
            return array();

        $attr_group = explode('@',$attr);
        $attr_id = $attr_value = array();
        foreach ($attr_group as $k => $v)
        {
            $attr_group2 = explode('_',$v);
            $attr_id[] = array_shift($attr_group2);
            $attr_value =array_merge($attr_value,$attr_group2);
        }
        $where = "attr_id in(".  implode(',',$attr_id).") and attr_value in('".  implode("','", $attr_value)."')";
        $c = count($attr_id) - 1;
        if($c > 0)
            $arr = M('goods_attr')->where($where)->group('goods_id')->having("count(goods_id) > $c")->getField("goods_id",true);  //select * from   `tp_goods_attr` where attr_id in(59,80) and attr_value in('直板','翻盖','蓝牙4.0') group by goods_id having count(goods_id) > 1
        else
            $arr = M('goods_attr')->where($where)->getField("goods_id",true); // 如果只有一个条件不再进行分组查询

        return ($arr ? $arr : array_unique($arr));
    }


    /**
     * 获得指定分类下的子分类的数组
     * @access  public
     * @param   int     $cat_id     分类的ID
     * @param   int     $selected   当前选中分类的ID
     * @param   boolean $re_type    返回的类型: 值为真时返回下拉列表,否则返回数组
     * @param   int     $level      限定返回的级数。为0时返回所有级数
     * @return  mix
     */
    public function goods_cat_list($cat_id = 0, $selected = 0, $re_type = true, $level = 0)
    {
        global $goods_category, $goods_category2;
        $sql = "SELECT * FROM  __PREFIX__goods_category ORDER BY parent_id , sort_order ASC";
        $goods_category = D('goods_category')->query($sql);
        $goods_category = convert_arr_key($goods_category, 'id');

        foreach ($goods_category AS $key => $value)
        {
            if($value['level'] == 1)
                $this->get_cat_tree($value['id']);
        }
        /*
        foreach ($goods_category2 AS $key => $value)
        {
                $strpad_count = $value['level']*10;
                echo str_pad('',$strpad_count,"-",STR_PAD_LEFT);
                echo $value['name'];
                echo "<br/>";
        }*/
        return $goods_category2;
    }

    /**
     * 获取指定id下的 所有分类
     * @global type $goods_category 所有商品分类
     * @param type $id 当前显示的 菜单id
     * @return 返回数组 Description
     */
    public function get_cat_tree($id)
    {
        global $goods_category, $goods_category2;
        $goods_category2[$id] = $goods_category[$id];
        foreach ($goods_category AS $key => $value){
            if($value['parent_id'] == $id)
            {
                $this->get_cat_tree($value['id']);
                $goods_category2[$id]['have_son'] = 1; // 还有下级
            }
        }
    }


    /**
     * 移除指定$parent_id_path 分类以及下的所有分类
     * @global type $cat_list 所有商品分类
     * @param type $parent_id_path 指定的id
     * @return 返回数组 Description
     */
    public function remove_cat($cat_list,$parent_id_path)
    {
        foreach ($cat_list AS $key => $value){
            if(strstr($value['parent_id_path'],$parent_id_path))
            {
                unset($cat_list[$value['id']]);
            }
        }
        return $cat_list;
    }

    /**
     * 改变或者添加分类时 需要修改他下面的 parent_id_path  和 level
     * @global type $cat_list 所有商品分类
     * @param type $parent_id_path 指定的id
     * @return 返回数组 Description
     */
    public function refresh_cat($id)
    {
        $GoodsCategory = M("GoodsCategory"); // 实例化User对象
        $cat = $GoodsCategory->where("id = $id")->find(); // 找出他自己
        // 刚新增的分类先把它的值重置一下
        if($cat['parent_id_path'] == '')
        {
            ($cat['parent_id'] == 0) && $GoodsCategory->execute("UPDATE __PREFIX__goods_category set  parent_id_path = '0_$id', level = 1 where id = $id"); // 如果是一级分类
            $GoodsCategory->execute("UPDATE __PREFIX__goods_category AS a ,__PREFIX__goods_category AS b SET a.parent_id_path = CONCAT_WS('_',b.parent_id_path,'$id'),a.level = (b.level+1) WHERE a.parent_id=b.id AND a.id = $id");
            $cat = $GoodsCategory->where("id = $id")->find(); // 从新找出他自己
        }

        if($cat['parent_id'] == 0) //有可能是顶级分类 他没有老爸
        {
            $parent_cat['parent_id_path'] =   '0';
            $parent_cat['level'] = 0;
        }
        else{
            $parent_cat = $GoodsCategory->where("id = {$cat['parent_id']}")->find(); // 找出他老爸的parent_id_path
        }
        $replace_level = $cat['level'] - ($parent_cat['level'] + 1); // 看看他 相比原来的等级 升级了多少  ($parent_cat['level'] + 1) 他老爸等级加一 就是他现在要改的等级
        $replace_str = $parent_cat['parent_id_path'].'_'.$id;
        $GoodsCategory->execute("UPDATE `__PREFIX__goods_category` SET parent_id_path = REPLACE(parent_id_path,'{$cat['parent_id_path']}','$replace_str'), level = (level - $replace_level) WHERE  parent_id_path LIKE '{$cat['parent_id_path']}%'");
    }

    /**
     * 动态获取商品属性输入框 根据不同的数据返回不同的输入框类型
     * @param int $goods_id 商品id
     * @param int $type_id 商品属性类型id
     */
    public function getAttrInput($goods_id,$type_id)
    {
        header("Content-type: text/html; charset=utf-8");
        $GoodsAttribute = D('GoodsAttribute');
        $attributeList = $GoodsAttribute->where("type_id = $type_id")->select();

        foreach($attributeList as $key => $val)
        {

            $curAttrVal = $this->getGoodsAttrVal(NULL,$goods_id, $val['attr_id']);
            //促使他 循环
            if(count($curAttrVal) == 0)
                $curAttrVal[] = array('goods_attr_id' =>'','goods_id' => '','attr_id' => '','attr_value' => '','attr_price' => '');
            foreach($curAttrVal as $k =>$v)
            {
                $str .= "<tr class='attr_{$val['attr_id']}'>";
                $addDelAttr = ''; // 加减符号
                // 单选属性 或者 复选属性
                if($val['attr_type'] == 1 || $val['attr_type'] == 2)
                {
                    if($k == 0)
                        $addDelAttr .= "<a onclick='addAttr(this)' href='javascript:void(0);'>[+]</a>&nbsp&nbsp";
                    else
                        $addDelAttr .= "<a onclick='delAttr(this)' href='javascript:void(0);'>[-]</a>&nbsp&nbsp";
                }

                $str .= "<td>$addDelAttr {$val['attr_name']}</td> <td>";

                // if($v['goods_attr_id'] > 0) //tp_goods_attr 表id
                //     $str .= "<input type='hidden' name='goods_attr_id[]' value='{$v['goods_attr_id']}'/>";

                // 手工录入
                if($val['attr_input_type'] == 0)
                {
                    $str .= "<input type='text' size='40' value='{$v['attr_value']}' name='attr_{$val['attr_id']}[]' />";
                }
                // 从下面的列表中选择（一行代表一个可选值）
                if($val['attr_input_type'] == 1)
                {
                    $str .= "<select name='attr_{$val['attr_id']}[]'>";
                    $tmp_option_val = explode(PHP_EOL, $val['attr_values']);
                    foreach($tmp_option_val as $k2=>$v2)
                    {
                        // 编辑的时候 有选中值
                        if($v['attr_value'] == $v2)
                            $str .= "<option selected='selected' value='{$v2}'>{$v2}</option>";
                        else
                            $str .= "<option value='{$v2}'>{$v2}</option>";
                    }
                    $str .= "</select>";
                    //$str .= "属性价格<input type='text' maxlength='10' size='5' value='{$v['attr_price']}' name='attr_price_{$val['attr_id']}[]'>";
                }
                // 多行文本框
                if($val['attr_input_type'] == 2)
                {
                    $str .= "<textarea cols='40' rows='3' name='attr_{$val['attr_id']}[]'>{$v['attr_value']}</textarea>";
                    //$str .= "属性价格<input type='text' maxlength='10' size='5' value='{$v['attr_price']}' name='attr_price_{$val['attr_id']}[]'>";
                }
                $str .= "</td></tr>";
                //$str .= "<br/>";
            }

        }
        return  $str;
    }

    /**
     * 获取 tp_goods_attr 表中指定 goods_id  指定 attr_id  或者 指定 goods_attr_id 的值 可是字符串 可是数组
     * @param int $goods_attr_id tp_goods_attr表id
     * @param int $goods_id 商品id
     * @param int $attr_id 商品属性id
     * @return array 返回数组
     */
    public function getGoodsAttrVal($goods_attr_id = 0 ,$goods_id = 0, $attr_id = 0)
    {
        $GoodsAttr = D('GoodsAttr');
        if($goods_attr_id > 0)
            return $GoodsAttr->where("goods_attr_id = $goods_attr_id")->select();
        if($goods_id > 0 && $attr_id > 0)
            return $GoodsAttr->where("goods_id = $goods_id and attr_id = $attr_id")->select();
    }

    /**
     *  给指定商品添加属性 或修改属性 更新到 tp_goods_attr
     * @param int $goods_id  商品id
     * @param int $goods_type  商品类型id
     */
    public function saveGoodsAttr($goods_id,$goods_type)
    {
        $GoodsAttr = D('GoodsAttr');
        $Goods = M("Goods");

        $old_goods_type = $Goods->where('goods_id = '.$goods_id)->getField('goods_type');
        // 属性类型被更改了 就先删除以前的属性类型 或者没有属性 则删除
        if(($goods_type == 0) || ($goods_type != $old_goods_type))
        {
            $GoodsAttr->where('goods_id = '.$goods_id)->delete();
            return;
        }


        $GoodsAttrList = $GoodsAttr->where('goods_id = '.$goods_id)->select();

        $old_goods_attr = array(); // 数据库中的的属性  以 attr_id _ 和值的 组合为键名
        foreach($GoodsAttrList as $k => $v)
        {
            $old_goods_attr[$v['attr_id'].'_'.$v['attr_value']] = $v;
        }
        file_put_contents("c.html", print_r($old_goods_attr,true));


        $str = print_r($_POST,true);
        file_put_contents("a.html", $str);
        // post 提交的属性  以 attr_id _ 和值的 组合为键名
        $post_goods_attr = array();
        foreach($_POST as $k => $v)
        {
            $attr_id = str_replace('attr_','',$k);
            if(!strstr($k, 'attr_') || strstr($k, 'attr_price_'))
                continue;
            foreach ($v as $k2 => $v2)
            {
                if(empty($v2))
                    continue;
                //
                $tmp_key = $attr_id."_".$v2;
                $attr_price = $_POST["attr_price_$attr_id"][$k2];
                $attr_price = $attr_price ? $attr_price : 0;
                if(array_key_exists($tmp_key , $old_goods_attr)) // 如果这个属性 原来就存在
                {
                    if($old_goods_attr[$tmp_key]['attr_price'] != $attr_price) // 并且价格不一样 就做更新处理
                    {
                        $goods_attr_id = $old_goods_attr[$tmp_key]['goods_attr_id'];
                        $GoodsAttr->where("goods_attr_id = $goods_attr_id")->save(array('attr_price'=>$attr_price));
                    }
                }
                else // 否则这个属性 数据库中不存在 说明要做删除操作
                {
                    $GoodsAttr->add(array('goods_id'=>$goods_id,'attr_id'=>$attr_id,'attr_value'=>$v2,'attr_price'=>$attr_price));
                }
                unset($old_goods_attr[$tmp_key]);
            }

        }
        //file_put_contents("b.html", print_r($post_goods_attr,true));
        // 没有被 unset($old_goods_attr[$tmp_key]); 掉是 说明 数据库中存在 表单中没有提交过来则要删除操作
        foreach($old_goods_attr as $k => $v)
        {
            $GoodsAttr->where('goods_attr_id = '.$v['goods_attr_id'])->delete(); //
        }

    }

    /**
     * 获取 tp_spec_item表 指定规格id的 规格项
     * @param int $spec_id 规格id
     * @return array 返回数组
     */
    public function getSpecItem($spec_id)
    {
        $model = M('SpecItem');
        $arr = $model->where("spec_id = $spec_id")->select();
        $arr = get_id_val($arr, 'id','item');
        return $arr;
    }

    /**
     * 获取 规格的 笛卡尔积
     * @param $goods_id 商品 id
     * @param $spec_arr 笛卡尔积
     * @return string 返回表格字符串
     */
    public function getSpecInput($goods_id, $spec_arr)
    {
        // <input name="item[2_4_7][price]" value="100" /><input name="item[2_4_7][name]" value="蓝色_S_长袖" />
        /*$spec_arr = array(
            20 => array('7','8','9'),
            10=>array('1','2'),
            1 => array('3','4'),

        );  */
        // 排序
        foreach ($spec_arr as $k => $v)
        {
            $spec_arr_sort[$k] = count($v);
        }
        asort($spec_arr_sort);
        foreach ($spec_arr_sort as $key =>$val)
        {
            $spec_arr2[$key] = $spec_arr[$key];
        }


        $clo_name = array_keys($spec_arr2);
        $spec_arr2 = combineDika($spec_arr2); //  获取 规格的 笛卡尔积

        $spec = M('Spec')->getField('id,name'); // 规格表
        $specItem = M('SpecItem')->getField('id,item');//规格项
        $keySpecGoodsPrice = M('SpecGoodsPrice')->where('goods_id = '.$goods_id)->getField('key,key_name,price,store_count,sku');//规格项

        $str = "<table class='table table-bordered' id='spec_input_tab'>";
        $str .="<tr>";
        // 显示第一行的数据
        foreach ($clo_name as $k => $v)
        {
            $str .=" <td><b>{$spec[$v]}</b></td>";
        }
        $str .="<td><b>价格</b></td>
               <td><b>库存</b></td>
               <td><b>SKU</b></td>
             </tr>";
        // 显示第二行开始
        foreach ($spec_arr2 as $k => $v)
        {
            $str .="<tr>";
            $item_key_name = array();
            foreach($v as $k2 => $v2)
            {
                $str .="<td>$specItem[$v2]</td>";
                $item_key_name[$v2] = $specItem[$v2];
            }
            ksort($item_key_name);
            $item_key = implode('_', array_keys($item_key_name));
            $item_name = implode('_', $item_key_name);

            $str .="<td><input name='item[$item_key][price]' value='{$keySpecGoodsPrice[$item_key][price]}' /></td>";
            $str .="<td><input name='item[$item_key][store_count]' value='{$keySpecGoodsPrice[$item_key][store_count]}' /></td>";
            $str .="<td><input name='item[$item_key][sku]' value='{$keySpecGoodsPrice[$item_key][sku]}' />
                <input type='hidden' name='item[$item_key][key_name]' value='$item_name' /></td>";
            $str .="</tr>";
        }
        $str .= "</table>";
        return $str;
    }

}