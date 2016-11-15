<?php

namespace WeChat\Controller;
use Think\Controller;
class ToolController extends Controller {

    public function _initialize(){
    }

    public function index(){
        $weChatUserList = getWeChatUserList();
        if ( !empty( $weChatUserList ) ){
            foreach ( $weChatUserList as $openid) {
                $res = weChatPullingMessage( $openid , false );
                echo $openid . "[" . $res . "]<br>";
            }
        }
        echo "===========<br>";
        echo "拉取完成";
        exit;
    }
}