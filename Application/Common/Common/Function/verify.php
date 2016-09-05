<?php


//验证码
function verify(){

    $config=array(
            'imageW'=>110,
            'imageH'=>30,
            'length'=>4,
            'fontSize'=>16,
            'useNoise'=>false,
    );

    $Verify = new \Think\Verify($config);
    $Verify->entry();
}