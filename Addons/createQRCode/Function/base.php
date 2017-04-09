<?php

/**
 * 获取微信token
 * @return \Common\Logic\type
 */
function addons_get_access_token()
{
    $jssdkLogic = new Common\Logic\JsSdkLogic();
    return $jssdkLogic->get_access_token();
}

function addons_create_qr_code($scene_id) {
    $access_token = addons_get_access_token();
    $url ="https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token={$access_token}";
    $post_arr = array(
        "action_name" => "QR_LIMIT_STR_SCENE",
        "action_info" => array(
            "scene" => array(
                "scene_str" => "addons_qe_code_".$scene_id
            )
        )
    );
    $post_str = jsonEncodeEx($post_arr);
    $post_str = str_replace( "\/" , "/" , $post_str );
    $return = httpRequest($url,'POST',$post_str);

    dd($return);
}

