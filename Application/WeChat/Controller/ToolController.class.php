<?php

namespace WeChat\Controller;
use Think\Controller;
class ToolController extends Controller {

    public function _initialize(){
        set_time_limit(0);
    }

    public function index(){
        $needPic = I( "needPic" , false );
        $thisOpenid = null;
        $weChatUserList = getWeChatUserList();
        if ( !empty( $weChatUserList ) ){
            foreach ( $weChatUserList as $openid ) {
                echo $openid . ":";
                if( ! isExistenceDataWithCondition( "users" , array( "openid" => $openid ) ) ){
                    registerFromOpenid( $openid , array() , "WeChat" , false );
                    echo "[注册]";
                }
                $res = weChatPullingMessage( $openid , $needPic );
                echo "[拉取：" . $res . "]<br>";
            }
        }
        echo "===========<br>";
        echo "拉取完成";
        exit;
    }
}