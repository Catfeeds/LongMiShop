<?php

namespace Mobile\Controller;

class PictureGenerationController extends MobileBaseController {

    function exceptAuthActions()
    {
        return null;
    }

    /**
     * 初始化操作
     */
    public function _initialize() {
        parent::_initialize();
    }

    public function index(){
        $name = I("name");
        $position = I("position");
        $this->assign('name',$name);
        $this->assign('position',$position);
        $this -> display();
    }

    public function run(){
        $name = I("name","米兔");
        $position = I("position","主编");
        $im = imagecreatetruecolor(588, 800);
        $bg = imagecreatefromjpeg('./Template/mobile/longmi/Static/images/toutu.jpg');
        imagecopy($im,$bg,0,0,0,0,588, 800);
        imagedestroy($bg);
        $black = imagecolorallocate($im, 0, 0, 0);
        $font = './Template/mobile/longmi/Static/fonts/gb.ttf';
        imagettftext($im, 24, 4, 100, 360, $black, $font, $name);
        imagettftext($im, 24, 4, 105, 400, $black, $font, $position);
        imagejpeg($im);
        imagedestroy($im);
        header("content-type:image/jpeg");
    }


}