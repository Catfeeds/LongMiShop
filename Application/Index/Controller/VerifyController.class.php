<?php
namespace Index\Controller;


class VerifyController extends IndexBaseController {

    function exceptAuthActions()
    {
        return array(
            'verify',
            'postage'
        );
    }

    public function _initialize() {
        parent::_initialize();
    }

    //验证码
    public function verify(){

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

  


}