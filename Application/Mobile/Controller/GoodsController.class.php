<?php
namespace Mobile\Controller;
use Think\AjaxPage;
use Think\Page;
class GoodsController extends MobileBaseController {
    function exceptAuthActions()
    {
        return array(
            "index",
            "categoryList",
            "goodsList",
            "ajaxGoodsList",
            "goodsInfo",
            "detail",
            "comment",
            "ajaxComment",
            "goodsAttr",
            "search",
            "ajaxSearch",
            "setGoodsComment",
            'ajaxCollectGoods',
        );
    }
    public function  _initialize() {
        parent::_initialize();
    }
    public function index(){
       // $this->show('<style type="text/css">*{ padding: 0; margin: 0; } div{ padding: 4px 48px;} body{ background: #fff; font-family: "微软雅黑"; color: #333;font-size:24px} h1{ font-size: 100px; font-weight: normal; margin-bottom: 12px; } p{ line-height: 1.8em; font-size: 36px } a,a:hover,{color:blue;}</style><div style="padding: 24px 48px;"> <h1>:)</h1><p>欢迎使用 <b>ThinkPHP</b>！</p><br/>版本 V{$Think.version}</div><script type="text/javascript" src="http://ad.topthink.com/Public/static/client.js"></script><thinkad id="ad_55e75dfae343f5a1"></thinkad><script type="text/javascript" src="http://tajs.qq.com/stats?sId=9347272" charset="UTF-8"></script>','utf-8');
        $this->display();
    }

    /**
     * 分类列表显示
     */
    public function categoryList(){
        $goods_category_tree = getGoodsCategoryTree();
        $this->assign('goods_category_tree', $goods_category_tree);
        $this->display();
    }

    /**
     * 商品列表页
     */
    public function goodsList(){

    	$filter_param = array(); // 帅选数组
    	$id = I('get.id',1); // 当前分类id
    	$brand_id = I('brand_id',0);
    	$spec = I('spec',0); // 规格
    	$attr = I('attr',''); // 属性
    	$sort = I('sort','goods_id'); // 排序
    	$sort_asc = I('sort_asc','asc'); // 排序
    	$price = I('price',''); // 价钱
    	$start_price = trim(I('start_price','0')); // 输入框价钱
    	$end_price = trim(I('end_price','0')); // 输入框价钱
    	if($start_price && $end_price) $price = $start_price.'-'.$end_price; // 如果输入框有价钱 则使用输入框的价钱
    	$filter_param['id'] = $id; //加入帅选条件中
    	$brand_id  && ($filter_param['brand_id'] = $brand_id); //加入帅选条件中
    	$spec  && ($filter_param['spec'] = $spec); //加入帅选条件中
    	$attr  && ($filter_param['attr'] = $attr); //加入帅选条件中
    	$price  && ($filter_param['price'] = $price); //加入帅选条件中

    	$goodsLogic = new \Common\Logic\GoodsLogic(); // 前台商品操作逻辑类
    	// 分类菜单显示
    	$goodsCate = M('GoodsCategory')->where("id = $id")->find();// 当前分类
    	//($goodsCate['level'] == 1) && header('Location:'.U('Home/Channel/index',array('cat_id'=>$id))); //一级分类跳转至大分类馆
    	$cateArr = $goodsLogic->get_goods_cate($goodsCate);

    	// 帅选 品牌 规格 属性 价格
    	$cat_id_arr = getCatGrandson ($id);

    	$filter_goods_id = M('goods')->where("is_on_sale=1 and cat_id in(".  implode(',', $cat_id_arr).") ")->cache(true)->getField("goods_id",true);

    	// 过滤帅选的结果集里面找商品
    	if($brand_id || $price)// 品牌或者价格
    	{
    		$goods_id_1 = $goodsLogic->getGoodsIdByBrandPrice($brand_id,$price); // 根据 品牌 或者 价格范围 查找所有商品id
    		$filter_goods_id = array_intersect($filter_goods_id,$goods_id_1); // 获取多个帅选条件的结果 的交集
    	}
    	if($spec)// 规格
    	{
    		$goods_id_2 = $goodsLogic->getGoodsIdBySpec($spec); // 根据 规格 查找当所有商品id
    		$filter_goods_id = array_intersect($filter_goods_id,$goods_id_2); // 获取多个帅选条件的结果 的交集
    	}
    	if($attr)// 属性
    	{
    		$goods_id_3 = $goodsLogic->getGoodsIdByAttr($attr); // 根据 规格 查找当所有商品id
    		$filter_goods_id = array_intersect($filter_goods_id,$goods_id_3); // 获取多个帅选条件的结果 的交集
    	}

    	$filter_menu  = $goodsLogic->get_filter_menu($filter_param,'goodsList'); // 获取显示的帅选菜单
    	$filter_price = $goodsLogic->get_filter_price($filter_goods_id,$filter_param,'goodsList'); // 帅选的价格期间
    	$filter_brand = $goodsLogic->get_filter_brand($filter_goods_id,$filter_param,'goodsList',1); // 获取指定分类下的帅选品牌
    	$filter_spec  = $goodsLogic->get_filter_spec($filter_goods_id,$filter_param,'goodsList',1); // 获取指定分类下的帅选规格
    	$filter_attr  = $goodsLogic->get_filter_attr($filter_goods_id,$filter_param,'goodsList',1); // 获取指定分类下的帅选属性

    	$count = count($filter_goods_id);
        $limit = 12;
    	$page = new Page($count,$limit);
    	if($count > 0)
    	{
    		$goods_list = M('goods')->where("goods_id in (".  implode(',', $filter_goods_id).")")->order("$sort $sort_asc")->limit($page->firstRow.','.$page->listRows) ->select();

            $filter_goods_id2 = get_arr_column($goods_list, 'goods_id');
    		if($filter_goods_id2)
    			$goods_images = M('goods_images')->where("goods_id in (".  implode(',', $filter_goods_id2).")")->cache(true)->select();
    	}
    	$goods_category = M('goods_category')->where('is_show=1')->cache(true)->getField('id,name,parent_id,level'); // 键值分类数组
    	$this->assign('goods_list',$goods_list);
    	$this->assign('goods_category',$goods_category);
    	$this->assign('goods_images',$goods_images);  // 相册图片
    	$this->assign('filter_menu',$filter_menu);  // 帅选菜单
    	$this->assign('filter_spec',$filter_spec);  // 帅选规格
    	$this->assign('filter_attr',$filter_attr);  // 帅选属性
    	$this->assign('filter_brand',$filter_brand);// 列表页帅选属性 - 商品品牌
    	$this->assign('filter_price',$filter_price);// 帅选的价格期间
    	$this->assign('goodsCate',$goodsCate);
    	$this->assign('cateArr',$cateArr);
    	$this->assign('filter_param',$filter_param); // 帅选条件
    	$this->assign('cat_id',$id);
    	$this->assign('page',$page);// 赋值分页输出
        $this->assign('p',I('p'));
        $this->assign('number',I('number'));
        $this->assign('count',$count);
        $this->assign('limit',$limit);
    	$this->assign('sort_asc', $sort_asc == 'asc' ? 'desc' : 'asc');
    	C('TOKEN_ON',false);

        if($_GET['is_ajax'])
            $this->display('ajaxGoodsList');
        else
            $this->display();
    }

    /**
     * 商品列表页 ajax 翻页请求 搜索
     */
    public function ajaxGoodsList() {
        $where ='';

        $cat_id  = I("id",0); // 所选择的商品分类id
        if($cat_id > 0)
        {
            $grandson_ids = getCatGrandson($cat_id);
            $where .= " WHERE cat_id in(".  implode(',', $grandson_ids).") "; // 初始化搜索条件
        }

        $Model  = new \Think\Model();
        $result = $Model->query("select count(1) as count from __PREFIX__goods $where ");
        $count = $result[0]['count'];
        $page = new AjaxPage($count,10);

        $order = " order by goods_id desc"; // 排序
        $limit = " limit ".$page->firstRow.','.$page->listRows;
        $list = $Model->query("select *  from __PREFIX__goods $where $order $limit");

        $this->assign('lists',$list);
        $html = $this->fetch('ajaxGoodsList'); //$this->display('ajax_goods_list');
       exit($html);
    }

    /**
     * 商品详情页
     */
    public function goodsInfo(){
        C('TOKEN_ON',true);
        $goodsLogic = new \Common\Logic\GoodsLogic();
        $goods_id = I("get.id");
        $goods = M('Goods')->where("goods_id = $goods_id")->find();
        if(empty($goods)){
        	$this->error('此商品不存在或者已下架');
        }
        if($goods['brand_id']){
            $brnad = M('brand')->where("id =".$goods['brand_id'])->find();
            $goods['brand_name'] = $brnad['name'];
        }
        $goods_images_list = M('GoodsImages')->where("goods_id = $goods_id")->select(); // 商品 图册
        $goods_attribute = M('GoodsAttribute')->getField('attr_id,attr_name'); // 查询属性
        $goods_attr_list = M('GoodsAttr')->where("goods_id = $goods_id")->select(); // 查询商品属性表
		$filter_spec = $goodsLogic->getSpec($goods_id);

        $spec_goods_price  = M('spec_goods_price')->where("goods_id = $goods_id")->getField("key,price,store_count"); // 规格 对应 价格 库存表
        //M('Goods')->where("goods_id=$goods_id")->save(array('click_count'=>$goods['click_count']+1 )); //统计点击数
        $commentStatistics = $goodsLogic->commentStatistics($goods_id);// 获取某个商品的评论统计
        $this->assign('spec_goods_price', json_encode($spec_goods_price,true)); // 规格 对应 价格 库存表
      	$goods['sale_num'] = M('order_goods')->where("goods_id=$goods_id and is_send=1")->count();

        //商品促销
        if($goods['prom_type'] == 3)
        {
            $prom_goods = M('prom_goods')->where("id = {$goods['prom_id']}  AND is_close=0")->find();
            $this->assign('prom_goods',$prom_goods);// 商品促销
        }
        if(!empty($this->user_id)){
            $where['user_id'] = $this->user_id;
            $where['goods_id'] = $goods_id;
            $collectRes = M('goods_collect')->where($where)->count();
            $collectRes == 1 ? $this->assign('collectRes',$collectRes) : '';
        }

        $goods['discount'] = round($goods['shop_price']/$goods['market_price'],2)*10;

        $goods_res = M('goods')->field('weight,delivery_way')->where("goods_id = '".$goods['goods_id']."'")->find();
        $count_data = array(
            0=>array(
                'goods_id'=>$goods['goods_id'], //商品id
                'goods_name'=>$goods['goods_name'], //商品名称
                'shop_price'=> $goods['shop_price'], //商品价格
                'weight'=>$goods_res['weight'], //商品重量
                'shipping_code'=>$goods_res['delivery_way'], //配送方式
                'goods_num'=> 1,   //件数  重量
            ),
        );
        $logAdd = M('logistics')->field('log_province,log_city')->where("log_id = '".$goods_res['delivery_way']."'")->find();
        $logAdd = $logAdd['log_province'].' '.$logAdd['log_city'];
        $count_postage = count_postage($count_data);

        $condition3 = array('user_id' => $this -> user_id , 'goods_id' => $goods_id, 'is_delete' => 0 , 'is_buyer' => 0);
        if( isExistenceDataWithCondition("goods_comment",$condition3) ){
            $isComment = true;
        }else{
            $isComment = false;
        }
        $condition2 = 'og.order_id=o.order_id and og.goods_id="'.$goods_id.'" and o.pay_status = 1 and o.user_id = "'.$this -> user_id.'"';
        if( M()->table(array('lm_order_goods'=>'og','lm_order'=>'o'))->where($condition2)->count() > 0 ){
            $this->assign('isBought',true);
            $condition4 = array('user_id' => $this -> user_id , 'goods_id' => $goods_id, 'is_delete' => 0 , 'is_buyer' => 1);
            if( isExistenceDataWithCondition("goods_comment",$condition4) ){
                $isComment = true;
            }else{
                $isComment = false;
            }
        }else{
            $this->assign('isBought',false);
        }
        $goods['my_parameter'] = unserialize(base64_decode($goods['my_parameter']));
//        dd($goods);

        $this->assign('isComment',$isComment);
        $this->assign('count_postage',sprintf(" %1\$.2f",$count_postage['data']['count']));
        $this->assign('logAdd',$logAdd);
        $this->assign('commentStatistics',$commentStatistics);//评论概览
        $this->assign('goods_attribute',$goods_attribute);//属性值
        $this->assign('goods_attr_list',$goods_attr_list);//属性列表
        $this->assign('filter_spec',$filter_spec);//规格参数
        $this->assign('goods_images_list',$goods_images_list);//商品缩略图
        $this->assign('goods',$goods);
        $this->display();
    }

    /**
     * 商品详情页
     */
    public function detail(){
        //  form表单提交
        C('TOKEN_ON',true);
        $goodsLogic = new \Common\Logic\GoodsLogic();
        $goods_id = I("get.id");
        $goods = M('Goods')->where("goods_id = $goods_id")->find();
        $this->assign('goods',$goods);
        $this->display();
    }

    /*
     * 商品评论
     */
    public function comment(){
        $goods_id = I("goods_id",'0');
        $this->assign('goods_id',$goods_id);
        $this->display();
    }

    /*
     * ajax获取商品评论
     */
    public function ajaxComment(){
        $goods_id = I("goods_id",'0');
        $is_buyer = I("is_buyer","0");
        $page_limit = 10;
        $where = "goods_id = '$goods_id' and is_show = 1 and is_delete = 0";
        if(!empty($is_buyer)){
            $where .= " and is_buyer = 1";
        }
        $count = M('goods_comment')->where($where)->count();

        $page = new AjaxPage($count,$page_limit);
        $show = $page->show();
        $list = M('goods_comment')->where($where)->order("create_time desc")->limit($page->firstRow.','.$page->listRows)->select();
        $this->assign('commentList',$list);// 商品评论
        $this->assign('limit',$page_limit);// 赋值分页输出
        $this->assign('count',$count);// 赋值分页输出
        $this->assign('page',$show);// 赋值分页输出
        $this->assign('p',I('p'));// 赋值分页输出page


//        $commentType = I('commentType','1'); // 1 全部 2好评 3 中评 4差评
//        $page_limit = 3;
//        if($commentType==$page_limit){
//        	$where = "goods_id = $goods_id and parent_id = 0 and img !='' ";
//        }else{
//        	$typeArr = array('1'=>'0,1,2,3,4,5','2'=>'4,5','3'=>'3','4'=>'0,1,2');
//        	$where = "goods_id = $goods_id and parent_id = 0 and ceil((deliver_rank + goods_rank + service_rank) / 3) in($typeArr[$commentType])";
//        }
//        $count = M('Comment')->where($where)->count();
//
//        $page = new AjaxPage($count,$page_limit);
//        $show = $page->show();
//        $list = M('Comment')->where($where)->order("add_time desc")->limit($page->firstRow.','.$page->listRows)->select();
//        $replyList = M('Comment')->where("goods_id = $goods_id and parent_id > 0")->order("add_time desc")->select();
//        foreach($list as $k => $v){
//            $list[$k]['img'] = unserialize($v['img']); // 晒单图片
//            $list[$k]['headImg'] = getUserHeadImg($v['user_id']);
//        }
//        $this->assign('commentlist',$list);// 商品评论
//        $this->assign('replyList',$replyList); // 管理员回复
//        $this->assign('page',$show);// 赋值分页输出
        $this->display();
    }

    /*
     * 获取商品规格
     */
    public function goodsAttr(){
        $goods_id = I("get.goods_id",'0');
        $goods_attribute = M('GoodsAttribute')->getField('attr_id,attr_name'); // 查询属性
        $goods_attr_list = M('GoodsAttr')->where("goods_id = $goods_id")->select(); // 查询商品属性表
        $this->assign('goods_attr_list',$goods_attr_list);
        $this->assign('goods_attribute',$goods_attribute);
        $this->display();
    }
     /**
     * 商品搜索列表页
     */
    public function search(){

    	$filter_param = array(); // 帅选数组
    	$id = I('get.id',0); // 当前分类id
    	$brand_id = I('brand_id',0);
    	$sort = I('sort','goods_id'); // 排序
    	$sort_asc = I('sort_asc','asc'); // 排序
    	$price = I('price',''); // 价钱
    	$start_price = trim(I('start_price','0')); // 输入框价钱
    	$end_price = trim(I('end_price','0')); // 输入框价钱
    	if($start_price && $end_price) $price = $start_price.'-'.$end_price; // 如果输入框有价钱 则使用输入框的价钱
    	$filter_param['id'] = $id; //加入帅选条件中
    	$brand_id  && ($filter_param['brand_id'] = $brand_id); //加入帅选条件中
    	$price  && ($filter_param['price'] = $price); //加入帅选条件中
        $q = urldecode(trim(I('q',''))); // 关键字搜索
        $q  && ($_GET['q'] = $filter_param['q'] = $q); //加入帅选条件中
        if(empty($q))
            $this->error ('请输入搜索关键词');

    	$goodsLogic = new \Common\Logic\GoodsLogic(); // 前台商品操作逻辑类
    	$filter_goods_id = M('goods')->where("is_on_sale=1 and goods_name like '%{$q}%'  ")->cache(true)->getField("goods_id",true);

    	// 过滤帅选的结果集里面找商品
    	if($brand_id || $price)// 品牌或者价格
    	{
    		$goods_id_1 = $goodsLogic->getGoodsIdByBrandPrice($brand_id,$price); // 根据 品牌 或者 价格范围 查找所有商品id
    		$filter_goods_id = array_intersect($filter_goods_id,$goods_id_1); // 获取多个帅选条件的结果 的交集
    	}

    	$filter_menu  = $goodsLogic->get_filter_menu($filter_param,'goodsList'); // 获取显示的帅选菜单
    	$filter_price = $goodsLogic->get_filter_price($filter_goods_id,$filter_param,'goodsList'); // 帅选的价格期间
    	$filter_brand = $goodsLogic->get_filter_brand($filter_goods_id,$filter_param,'goodsList',1); // 获取指定分类下的帅选品牌

    	$count = count($filter_goods_id);
    	$page = new Page($count,4);
    	if($count > 0)
    	{
    		$goods_list = M('goods')->where("goods_id in (".  implode(',', $filter_goods_id).")")->order("$sort $sort_asc")->limit($page->firstRow.','.$page->listRows)->select();
    		$filter_goods_id2 = get_arr_column($goods_list, 'goods_id');
    		if($filter_goods_id2)
    			$goods_images = M('goods_images')->where("goods_id in (".  implode(',', $filter_goods_id2).")")->cache(true)->select();
    	}
    	$goods_category = M('goods_category')->where('is_show=1')->cache(true)->getField('id,name,parent_id,level'); // 键值分类数组
    	$this->assign('goods_list',$goods_list);
    	$this->assign('goods_category',$goods_category);
    	$this->assign('goods_images',$goods_images);  // 相册图片
    	$this->assign('filter_menu',$filter_menu);  // 帅选菜单
    	$this->assign('filter_brand',$filter_brand);// 列表页帅选属性 - 商品品牌
    	$this->assign('filter_price',$filter_price);// 帅选的价格期间
    	$this->assign('goodsCate',$goodsCate);
    	$this->assign('filter_param',$filter_param); // 帅选条件
    	$this->assign('page',$page);// 赋值分页输出
    	$this->assign('sort_asc', $sort_asc == 'asc' ? 'desc' : 'asc');
    	C('TOKEN_ON',false);

        if($_GET['is_ajax'])
            $this->display('ajaxGoodsList');
        else
            $this->display();
    }

    /**
     * 商品搜索列表页
     */
    public function ajaxSearch()
    {

    }

    /*
     *商品收藏
     *
     */
    public function ajaxCollectGoods(){

        //是否登录
        if(empty($this->user_id)){
            exit(json_encode(callback(false,'请登录收藏该商品')));
        }

        $goodsId = I('goods_id','','int');

        $data['user_id'] = $this->user_id;
        $data['goods_id'] = $goodsId;
        $collectRes = M('goods_collect')->where($data)->count();
        if(empty($collectRes)){ //新增收藏
            $data['add_time'] = time();
            $res = M('goods_collect')->add($data);
            $res ? exit(json_encode(callback(true,'收藏成功',array('status'=>'add')))) : exit(json_encode(callback(false,'收藏失败'))) ;
        }else{
            $res = M('goods_collect')->where($data)->delete();
            $res ? exit(json_encode(callback(true,'取消收藏',array('status'=>'del')))) : exit(json_encode(callback(false,'取消收藏失败')));
        }
    }

    /**
     * 添加新的商品评价
     */
    public function setGoodsComment(){
        if( empty( $this -> user_id ) ){
            exit(json_encode(callback(false,"登陆后才可评价")));
        }
        if( IS_POST ) {
            $goodsCommentContent = I("commentContent");
            $goodsCommentLevel = I("commentLevel");
            $goodsId = I("goodsId");
            $condition1 = array('user_id' => $this -> user_id , 'goods_id' => $goodsId, 'is_delete' => 0 , 'is_buyer' => 1);
            $condition2 = 'og.order_id=o.order_id and og.goods_id="'.$goodsId.'"  and o.pay_status = 1 and o.user_id = "'.$this -> user_id.'"';
            //array('user_id' => $this -> user_id , 'goods_id' => $goodsId);
            $condition3 = array('user_id' => $this -> user_id , 'goods_id' => $goodsId, 'is_delete' => 0 , 'is_buyer' => 0);
            if( !isExistenceDataWithCondition("goods_comment",$condition1) ){
                $newData = array(
                    "user_id"     => $this->user_id,
                    "goods_id"    => $goodsId,
                    "create_time" => time(),
                    "update_time" => time(),
                    "is_show"     => 1,
                    "user_name"   => $this->user['nickname'],
                    "user_img"    => $this->user['head_pic'],
                    "level"       => $goodsCommentLevel,
                    "content"     => $goodsCommentContent,
                    "is_delete"   => 0,
                );
                if( M()->table(array('lm_order_goods'=>'og','lm_order'=>'o'))->where($condition2)->count() > 0 ){
                    $newData['is_buyer'] = 1;
                    M('goods_comment') -> where($condition3) -> save( array('update_time' => time() ,'is_delete' => 1 ));
                    if( isSuccessToAddData("goods_comment" , $newData) ){
                        exit(json_encode(callback(true,"评价成功")));
                    }
                    exit(json_encode(callback(false,"评价失败")));
                }

                if( !isExistenceDataWithCondition("goods_comment",$condition3) ){
                    $newData['is_buyer'] = 0;
                    if( isSuccessToAddData("goods_comment" , $newData) ){
                        exit(json_encode(callback(true,"评价成功")));
                    }
                    exit(json_encode(callback(false,"评价失败")));
                }
            }
        }
        exit(json_encode(callback(false,"您已经评论过了")));
    }
}