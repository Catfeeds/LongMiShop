<?php

namespace Mobile\Controller;

class PictureGenerationController extends MobileBaseController {

    public $key = null;
    function exceptAuthActions()
    {
        return null;
    }

    /**
     * 初始化操作
     */
    public function _initialize() {
        parent::_initialize();
        $this -> key = md5("42368");
    }

    public function index(){
        $name = I("name");
        $name = str_replace(' ',$this -> key,$name);
        $position = I("position");
        $position = str_replace(' ',$this -> key,$position);
        $this -> assign('name',$name);
        $this -> assign('position',$position);
        $this -> display();
    }

    public function run(){
        $name = I("name","STEVE Jobs");
        $name = str_replace($this -> key,' ',$name);
        $position = I("position","CEO");
        $position = str_replace($this -> key,' ',$position);
        $im = imagecreatetruecolor(588, 800);
        $bg = imagecreatefromjpeg('./Template/mobile/longmi/Static/images/toutu.jpg');
        imagecopy($im,$bg,0,0,0,0,588, 800);
        imagedestroy($bg);
        $black = imagecolorallocate($im, 115, 115, 115);
        $font = './Template/mobile/longmi/Static/fonts/test.ttf';
        imagettftext($im, 24, 5, 100, 360, $black, $font, $name);
        imagettftext($im, 18, 5, 105, 400, $black, $font, $position);
        imagejpeg($im);
        imagedestroy($im);
        header("content-type:image/jpeg");
    }


}