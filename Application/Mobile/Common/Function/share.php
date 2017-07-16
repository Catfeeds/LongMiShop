<?php


/**
 * 获取分享气泡图片
 */
function getShareImages( $web_config ,$goodes_id = null ,$goodsCate  = null ,$user = null ){


    $default = $web_config['wechat_default'];// 系统默认
    $details = $web_config['wechat_details'];// 商品详细页/单品页分享设置
    $classify = $web_config['wechat_classify'];// 商品分类页分享设置
    $article = $web_config['wechat_article'];// 文章页分享设置
    $dift_img = $web_config['wechat_dift_img']; //分享有礼
//	$user = session('user');
    //logo
    $logo = "http://".$_SERVER['HTTP_HOST'].$web_config['shop_info_store_logo']."";
    //默认图片
    // $imgurl = $default==1 ? $logo : "http://" . $_SERVER['HTTP_HOST'] . $user['head_pic'] ;
    if($default==1){
       $imgurl =  $logo;
    }else{
        //是否微信头像
        if(strstr($user['head_pic'],'wx.qlogo.cn')){
            $imgurl =  $user['head_pic'];
        }else{
            $imgurl = "http://" . $_SERVER['HTTP_HOST'] . $user['head_pic'] ;
        }
    }

    //默认连接
    $link = "http://".$_SERVER['HTTP_HOST']."/index.php?m=Mobile&c=Index&a=index";

    if( CONTROLLER_NAME =="Recommend" ){
//        if( CONTROLLER_NAME =="Recommend" && ACTION_NAME  == "index"){
        $link = "http://".$_SERVER['HTTP_HOST'].U('Mobile/Recommend/share',array('inviteUserId' => $user['user_id']));
        if($dift_img == 1){
            //是否微信头像
            if(strstr($user['head_pic'],'wx.qlogo.cn')){
                $imgurl =  $user['head_pic'];
            }else{
                $imgurl = "http://" . $_SERVER['HTTP_HOST'] . $user['head_pic'] ;
            }
        }else{
            $imgurl = "http://" . $_SERVER['HTTP_HOST'] .$web_config['wechat_dift_file'];
        }
    }
    if( !is_null($goodes_id) ){ //详细页
        $like = "http://".$_SERVER['HTTP_HOST']."/index.php?m=Mobile&c=Goods&a=goodsInfo&id=".$goodes_id."";
        $imgurl = $details==1 ? $logo : "http://" . $_SERVER['HTTP_HOST'] . goods_thum_images($goodes_id,400,400)."";
        return json_encode(array('link'=>$like,'imgurl'=>$imgurl));
    }

    if( !is_null($goodsCate)){ //列表页
        $like = "http://".$_SERVER['HTTP_HOST']."/index.php?m=Mobile&c=Goods&a=goodsList&id=".$goodsCate['id']."";
        $imgurl = $classify==1 ? $logo : "http://" . $_SERVER['HTTP_HOST'] . $goodsCate['image']."";
        return json_encode(array('link'=>$like,'imgurl'=>$imgurl));
    }
    if( $_SERVER['REQUEST_URI']== U('Mobile/Index/recommendPolite')  ){
        $link = "http://".$_SERVER['HTTP_HOST'].U('Mobile/Index/recommendPolite');
    }

    return json_encode(array('imgurl'=>$imgurl,'link'=>$link));

}