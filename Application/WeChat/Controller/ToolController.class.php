<?php

namespace WeChat\Controller;
use Think\Controller;
class ToolController extends Controller {

    public function _initialize(){
    }

    public function index(){
        $weChatUserList = getWeChatUserList();
        setLogResult( $weChatUserList , "微信列表" , "test" );
        if ( !empty( $weChatUserList ) ){
            foreach ( $weChatUserList as $openid) {
                $res = weChatPullingMessage( $openid , false );
                setLogResult( $res , "微信info" , "test" );
            }
        }
    }
}