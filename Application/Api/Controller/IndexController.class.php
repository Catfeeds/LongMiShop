<?php

namespace Api\Controller;
use Think\Controller;
class IndexController extends BaseController {
    public function index(){
        $adData = M('ad') -> where('pid = 2 AND enabled = 1')->field(array('ad_link','ad_name','ad_code'))->cache(true,MY_CACHE_TIME)->select();
        $adData = changeAddress($adData,'ad_code');
        $newGoods = M("goods") -> where(array("is_new"=>1)) -> order("sort" ) -> limit('10') -> select();
        $newGoods = changeAddress($newGoods,'original_img');
        $hotGoods = M("goods") -> where(array("is_hot"=>1)) -> order("sort" ) -> limit('6') -> select();
        $hotGoods = changeAddress($hotGoods,'original_img');
        exit(json_encode(callback(true,'获取成功',array('adv'=>$adData,'new'=>$newGoods,'top'=>$hotGoods))));
    }

 
    /*
     * 获取首页数据
     */
    public function home(){
        //获取轮播图
        $data = M('ad') -> where('pid = 2')->field(array('ad_link','ad_name','ad_code'))->cache(true,MY_CACHE_TIME)->select();
        //广告地址转换
        foreach($data as $k=>$v){
//            exit($this->http_url);
            if(!strstr($v['ad_link'],'http'))
//                exit($this->http_url);
                $data[$k]['ad_link'] = SITE_URL.$v['ad_link'];
            $data[$k]['ad_code'] = SITE_URL.$v['ad_code'];

        }
        //获取大分类
        $category_arr = M('goods_category') -> where('parent_id=0')->field('id,name')->limit(3)->cache(true,MY_CACHE_TIME)->select();
        $result = array();
        foreach($category_arr as $c){
            $cat_arr = getCatGrandson($c['id']);
            //获取商品
            //$sql = "select goods_name,goods_id,original_img,shop_price from __PREFIX__goods where  cat_id in (".implode(',',$cat_arr).") limit 4";
            //$goods = M()->query($sql);
            $goodsList = M('goods') -> where("1=1")->limit(4)->cache(true,MY_CACHE_TIME)->getField("goods_id,goods_name,original_img,shop_price");
            foreach($goodsList as $k => $v){
                $v['original_img'] = SITE_URL.$v['original_img'];
                $c['goods_list'][] = $v;
            }            
            $result[] = $c;
        }

        exit(json_encode(array('status'=>1,'msg'=>'获取成功','result'=>array('goods'=>$result,'ad'=>$data))));
    }
    
    /**
     * 获取服务器配置
     */
    public function getConfig()
    {
        $config_arr = M('config')->select();
        exit(json_encode(array('status'=>1,'msg'=>'获取成功','result'=>$config_arr)));
    }
    /**
     * 获取插件信息
     */
    public function getPluginConfig()
    {
        $data = M('plugin') -> where("type='payment' OR type='login'")->select();
        $arr = array();
        foreach($data as $k=>$v){
            unset( $data[$k]['config']);
            unset( $data[$k]['config']);

            $data[$k]['config_value'] = unserialize($v['config_value']);
            if($data[$k]['type'] == 'payment'){
                $arr['payment'][] =  $data[$k];
            }
            if($data[$k]['type'] == 'login'){
                $arr['login'][] =  $data[$k];
            }
        }
        exit(json_encode(array('status'=>1,'msg'=>'获取成功','result'=>$arr ? $arr : '')));
    }
}